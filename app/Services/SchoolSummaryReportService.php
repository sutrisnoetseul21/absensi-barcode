<?php

namespace App\Services;

use App\Models\PresensiSchoolSummarySetting;
use App\Models\TahunAjaran;
use App\Models\KelasAjaran;
use App\Models\EnrollmentSiswa;
use App\Models\Presensi;
use App\Models\WhatsAppNotificationLog;
use App\Models\PengaturanSekolah;
use App\Jobs\SendWhatsAppNotificationJob;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class SchoolSummaryReportService
{
    public function __construct(
        protected RecipientResolverService $resolver
    ) {}

    /**
     * Dispatch rekap presensi seluruh sekolah.
     *
     * @return array{dispatched: int, skipped: int, errors: string[]}
     */
    public function dispatch(): array
    {
        $setting = PresensiSchoolSummarySetting::current();

        if (!$setting->is_active) {
            return ['dispatched' => 0, 'skipped' => 0, 'errors' => ['Laporan rekap sekolah tidak aktif.']];
        }

        $currentYear = TahunAjaran::aktif()->first();
        if (!$currentYear) {
            return ['dispatched' => 0, 'skipped' => 0, 'errors' => ['Tidak ada tahun ajaran aktif.']];
        }

        // Dedup Guard
        $relatedType = 'school_summary_report';
        $relatedId   = $currentYear->id;
        $now         = Carbon::now();
        $todayStr    = $now->toDateString();

        $alreadyDispatched = WhatsAppNotificationLog::where('related_type', $relatedType)
            ->where('related_id', (string) $relatedId)
            ->whereDate('created_at', $todayStr)
            ->whereIn('status', ['sent', 'pending'])
            ->exists();

        if ($alreadyDispatched) {
            return ['dispatched' => 0, 'skipped' => 1, 'errors' => []];
        }

        // Header
        $pengaturan  = PengaturanSekolah::current();
        $namaSekolah = $pengaturan ? $pengaturan->school_name : 'Sekolah';

        $namaHari = [
            'Sunday'    => 'Minggu',
            'Monday'    => 'Senin',
            'Tuesday'   => 'Selasa',
            'Wednesday' => 'Rabu',
            'Thursday'  => 'Kamis',
            'Friday'    => 'Jumat',
            'Saturday'  => 'Sabtu',
        ];
        $hari    = $namaHari[$now->format('l')] ?? $now->format('l');
        $tanggal = $now->format('d-m-Y');

        $pesanHeader = str_replace(
            ['{nama_sekolah}', '{hari}', '{tanggal}'],
            [$namaSekolah, $hari, $tanggal],
            $setting->template_header
        );

        // Loop kelas
        $kelasAjarans = KelasAjaran::with(['kelas'])
            ->where('academic_year_id', $currentYear->id)
            ->get();

        $rowsPesan = [];

        foreach ($kelasAjarans as $kelasAjaran) {
            $enrollments = EnrollmentSiswa::with('siswa')
                ->where('academic_year_id', $currentYear->id)
                ->where('class_id', $kelasAjaran->class_id)
                ->where('status', 'aktif')
                ->get();

            if ($enrollments->isEmpty()) {
                continue;
            }

            $studentIds  = $enrollments->pluck('student_id')->toArray();
            $attendances = Presensi::whereIn('student_id', $studentIds)
                ->where('date', $now->toDateString())
                ->get();

            $hadir = $attendances->whereIn('status', ['hadir', 'pulang'])->count();

            $telatList = $attendances->where('status', 'telat');
            $telat     = $telatList->count();
            $namaTelat = $this->getNamesList($telatList, $enrollments);

            $sakitList = $attendances->where('status', 'sakit');
            $sakit     = $sakitList->count();
            $namaSakit = $this->getNamesList($sakitList, $enrollments);

            $izinList = $attendances->where('status', 'izin');
            $izin     = $izinList->count();
            $namaIzin = $this->getNamesList($izinList, $enrollments);

            $alpaList = $attendances->where('status', 'alpa');
            $alpa     = $alpaList->count();
            $namaAlpa = $this->getNamesList($alpaList, $enrollments);

            $attendanceStudentIds = $attendances->pluck('student_id')->toArray();
            $belumPresensiNames   = [];
            foreach ($enrollments as $enrollment) {
                if (!in_array($enrollment->student_id, $attendanceStudentIds)) {
                    $belumPresensiNames[] = $enrollment->siswa->name ?? '-';
                }
            }
            $belum     = count($belumPresensiNames);
            $namaBelum = empty($belumPresensiNames) ? '' : '(' . implode(', ', $belumPresensiNames) . ')';
            $namaKelas = $kelasAjaran->kelas ? $kelasAjaran->kelas->name : '-';

            $formatNama = fn($str) => $str ? " $str" : "";

            $baris = str_replace(
                ['{nama_kelas}', '{jumlah_hadir}', '{jumlah_terlambat}', '{nama_terlambat}', '{jumlah_sakit}', '{nama_sakit}', '{jumlah_izin}', '{nama_izin}', '{jumlah_alpa}', '{nama_alpa}', '{jumlah_belum_presensi}', '{nama_belum_presensi}'],
                [$namaKelas, $hadir, $telat, $formatNama($namaTelat), $sakit, $formatNama($namaSakit), $izin, $formatNama($namaIzin), $alpa, $formatNama($namaAlpa), $belum, $formatNama($namaBelum)],
                $setting->template_row
            );

            $rowsPesan[] = trim(preg_replace('/\s+/', ' ', $baris));
        }

        $pesanGabungan = $pesanHeader . implode("\n", $rowsPesan) . $setting->template_footer;

        // Resolve Penerima
        $resolvedRecipients = [];
        $seenNumbers        = [];
        $recipientKeys      = $setting->recipients ?? [];

        foreach ($recipientKeys as $key) {
            $jabatansHp = $this->resolver->resolveByJabatan($key);
            foreach ($jabatansHp as $hp) {
                if ($hp && !isset($seenNumbers[$hp])) {
                    $resolvedRecipients[] = ['number' => $hp, 'type' => $key];
                    $seenNumbers[$hp]     = true;
                }
            }
        }

        if (empty($resolvedRecipients)) {
            return ['dispatched' => 0, 'skipped' => 0, 'errors' => ['Tidak ada penerima valid untuk rekap sekolah.']];
        }

        // Dispatch Jobs
        $dispatched = 0;
        foreach ($resolvedRecipients as $recipient) {
            $toNumber      = $recipient['number'];
            $recipientType = $recipient['type'];

            $log = WhatsAppNotificationLog::create([
                'module'           => 'presensi',
                'recipient_type'   => $recipientType,
                'recipient_number' => $toNumber,
                'message'          => $pesanGabungan,
                'status'           => 'pending',
                'response_payload' => json_encode(['info' => 'School Summary Report Dispatched']),
                'related_type'     => $relatedType,
                'related_id'       => (string) $relatedId,
            ]);

            SendWhatsAppNotificationJob::dispatch($toNumber, $pesanGabungan, $relatedType, (string) $relatedId, $recipientType, $log->id);
            $dispatched++;
        }

        return ['dispatched' => $dispatched, 'skipped' => 0, 'errors' => []];
    }

    private function getNamesList($attendances, $enrollments): string
    {
        if ($attendances->isEmpty()) {
            return '';
        }

        $ids   = $attendances->pluck('student_id')->toArray();
        $names = [];
        foreach ($enrollments as $en) {
            if (in_array($en->student_id, $ids)) {
                $names[] = $en->siswa->name ?? '-';
            }
        }
        return '(' . implode(', ', $names) . ')';
    }
}
