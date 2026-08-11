<?php

namespace App\Services;

use App\Models\PresensiDailyReportSetting;
use App\Models\TahunAjaran;
use App\Models\KelasAjaran;
use App\Models\EnrollmentSiswa;
use App\Models\Presensi;
use App\Models\WhatsAppNotificationLog;
use App\Jobs\SendWhatsAppNotificationJob;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class DailyClassReportService
{
    public function __construct(
        protected RecipientResolverService $resolver
    ) {}

    /**
     * Dispatch laporan harian per kelas.
     *
     * @param  bool  $isManual  Jika true, dedup guard tetap dicek tapi cutoff diabaikan
     * @return array{dispatched: int, skipped: int, errors: string[]}
     */
    public function dispatch(bool $isManual = false): array
    {
        $setting = PresensiDailyReportSetting::current();

        if (!$setting->is_active) {
            return ['dispatched' => 0, 'skipped' => 0, 'errors' => ['Laporan harian tidak aktif.']];
        }

        $currentYear = TahunAjaran::aktif()->first();
        if (!$currentYear) {
            return ['dispatched' => 0, 'skipped' => 0, 'errors' => ['Tidak ada tahun ajaran aktif.']];
        }

        $now = Carbon::now();
        $tanggal = $now->format('d-m-Y');

        $kelasAjarans = KelasAjaran::with(['kelas', 'guru'])
            ->where('academic_year_id', $currentYear->id)
            ->get();

        $dispatched = 0;
        $skipped    = 0;
        $errors     = [];

        foreach ($kelasAjarans as $kelasAjaran) {
            $result = $this->processKelas($kelasAjaran, $currentYear, $setting, $tanggal, $now);
            $dispatched += $result['dispatched'];
            $skipped    += $result['skipped'];
            $errors      = array_merge($errors, $result['errors']);
        }

        return compact('dispatched', 'skipped', 'errors');
    }

    private function processKelas($kelasAjaran, $currentYear, $setting, $tanggal, $now): array
    {
        $dispatched = 0;
        $skipped    = 0;
        $errors     = [];

        $enrollments = EnrollmentSiswa::with('siswa')
            ->where('academic_year_id', $currentYear->id)
            ->where('class_id', $kelasAjaran->class_id)
            ->where('status', 'aktif')
            ->get();

        $totalSiswa = $enrollments->count();
        if ($totalSiswa === 0) {
            $skipped++;
            return compact('dispatched', 'skipped', 'errors');
        }

        $studentIds  = $enrollments->pluck('student_id')->toArray();
        $attendances = Presensi::whereIn('student_id', $studentIds)
            ->where('date', $now->toDateString())
            ->get();

        $hadir = $attendances->whereIn('status', ['hadir', 'pulang'])->count();
        $telat = $attendances->where('status', 'telat')->count();
        $alpa  = $attendances->where('status', 'alpa')->count();
        $sakit = $attendances->where('status', 'sakit')->count();
        $izin  = $attendances->where('status', 'izin')->count();

        $attendanceStudentIds = $attendances->pluck('student_id')->toArray();
        $belumPresensiNames   = [];
        foreach ($enrollments as $enrollment) {
            if (!in_array($enrollment->student_id, $attendanceStudentIds)) {
                $belumPresensiNames[] = '- ' . ($enrollment->siswa->name ?? 'Siswa Tanpa Nama');
            }
        }

        $daftarBelumPresensi = empty($belumPresensiNames)
            ? 'Tidak ada (Semua sudah mengisi presensi)'
            : implode("\n", $belumPresensiNames);

        $namaKelas = $kelasAjaran->kelas ? $kelasAjaran->kelas->name : 'Kelas Tidak Diketahui';

        $pesan = str_replace(
            ['{nama_kelas}', '{tanggal}', '{total_siswa}', '{jumlah_hadir}', '{jumlah_terlambat}', '{jumlah_alpa}', '{jumlah_sakit}', '{jumlah_izin}', '{daftar_belum_presensi}'],
            [$namaKelas, $tanggal, $totalSiswa, ($hadir + $telat), $telat, $alpa, $sakit, $izin, $daftarBelumPresensi],
            $setting->template_pesan
        );

        // Dedup Guard
        $relatedType = 'daily_report_kelas';
        $relatedId   = $kelasAjaran->id;
        $todayStr    = $now->toDateString();

        $alreadyDispatched = WhatsAppNotificationLog::where('related_type', $relatedType)
            ->where('related_id', (string) $relatedId)
            ->whereDate('created_at', $todayStr)
            ->whereIn('status', ['sent', 'pending'])
            ->exists();

        if ($alreadyDispatched) {
            $skipped++;
            return compact('dispatched', 'skipped', 'errors');
        }

        // Resolve Penerima
        $resolvedRecipients = [];
        $seenNumbers        = [];
        $recipientKeys      = $setting->recipients ?? [];

        foreach ($recipientKeys as $key) {
            if ($key === 'wali_kelas') {
                $hp = $kelasAjaran->guru ? $kelasAjaran->guru->no_hp : null;
                if ($hp && !isset($seenNumbers[$hp])) {
                    $resolvedRecipients[] = ['number' => $hp, 'type' => 'wali_kelas'];
                    $seenNumbers[$hp]     = true;
                }
            } elseif ($key !== 'ortu') {
                $jabatansHp = $this->resolver->resolveByJabatan($key);
                foreach ($jabatansHp as $hp) {
                    if ($hp && !isset($seenNumbers[$hp])) {
                        $resolvedRecipients[] = ['number' => $hp, 'type' => $key];
                        $seenNumbers[$hp]     = true;
                    }
                }
            }
        }

        if (empty($resolvedRecipients)) {
            $errors[] = "Tidak ada penerima valid untuk $namaKelas.";
            return compact('dispatched', 'skipped', 'errors');
        }

        // Dispatch Jobs
        foreach ($resolvedRecipients as $recipient) {
            $toNumber      = $recipient['number'];
            $recipientType = $recipient['type'];

            $log = WhatsAppNotificationLog::create([
                'module'           => 'presensi',
                'recipient_type'   => $recipientType,
                'recipient_number' => $toNumber,
                'message'          => $pesan,
                'status'           => 'pending',
                'response_payload' => json_encode(['info' => "Daily Report Dispatched for $namaKelas"]),
                'related_type'     => $relatedType,
                'related_id'       => (string) $relatedId,
            ]);

            SendWhatsAppNotificationJob::dispatch($toNumber, $pesan, $relatedType, (string) $relatedId, $recipientType, $log->id);
            $dispatched++;
        }

        return compact('dispatched', 'skipped', 'errors');
    }
}
