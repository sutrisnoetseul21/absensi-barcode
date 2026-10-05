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
     * @param  bool  $isManual  Jika true, cutoff dan cek hari libur otomatis dilewati
     * @return array{dispatched: int, skipped: int, errors: string[]}
     */
    public function dispatch(bool $isManual = false): array
    {
        $setting = PresensiSchoolSummarySetting::current();

        if (!$setting->is_active) {
            return ['dispatched' => 0, 'skipped' => 0, 'errors' => ['Laporan rekap sekolah tidak aktif.']];
        }

        $currentYear = TahunAjaran::aktif()->first();
        if (!$currentYear) {
            return ['dispatched' => 0, 'skipped' => 0, 'errors' => ['Tidak ada tahun ajaran aktif.']];
        }

        $now         = Carbon::now();
        $todayStr    = $now->toDateString();

        // Cek Hari Sekolah jika pengiriman otomatis
        $kalenderService = app(KalenderSekolahService::class);
        if (!$isManual && !$kalenderService->isHariSekolah($now)) {
            Log::info("SchoolSummaryReportService: Hari ini bukan hari sekolah, laporan rekap sekolah otomatis dilewati.");
            return ['dispatched' => 0, 'skipped' => 0, 'errors' => ['Hari ini bukan hari sekolah (libur). Laporan rekap sekolah otomatis dilewati.']];
        }

        $relatedType = 'school_summary_report';

        // Header Pengaturan
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

        // Resolve Penerima
        $resolvedRecipients = [];
        $seenNumbers        = [];
        $recipientKeys      = $setting->recipients ?? [];

        foreach ($recipientKeys as $key) {
            if (str_starts_with($key, 'GROUP:')) {
                $groupId = substr($key, 6);
                if (!empty($groupId) && str_ends_with($groupId, '@g.us') && !isset($seenNumbers[$groupId])) {
                    $resolvedRecipients[] = ['number' => $groupId, 'type' => 'whatsapp_group'];
                    $seenNumbers[$groupId] = true;
                }
            } else {
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
            return ['dispatched' => 0, 'skipped' => 0, 'errors' => ['Tidak ada penerima valid untuk rekap sekolah.']];
        }

        // Ambil semua KelasAjaran tahun aktif
        $kelasAjarans = KelasAjaran::with(['kelas'])
            ->where('academic_year_id', $currentYear->id)
            ->get();

        // Helper ekstraksi tingkat / angkatan (misal: 7, 8, 9)
        $getGrade = function ($ka) {
            if ($ka->kelas && $ka->kelas->grade_level) {
                return (int) $ka->kelas->grade_level;
            }
            if ($ka->kelas && preg_match('/^(\d+)/', $ka->kelas->name, $matches)) {
                return (int) $matches[1];
            }
            return 0;
        };

        $groupedKelasAjarans = $kelasAjarans->groupBy($getGrade)->sortKeys();

        $dispatched = 0;
        $skipped    = 0;

        foreach ($groupedKelasAjarans as $grade => $kaList) {
            $gradeLabel = $grade > 0 ? "Kelas {$grade}" : "Seluruh Kelas";
            $hash       = md5("school_summary_{$currentYear->id}_grade_{$grade}");
            $relatedId  = vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split($hash, 4));

            // Dedup Guard per angkatan jika bukan pengiriman manual
            if (!$isManual) {
                $alreadyDispatched = WhatsAppNotificationLog::where('related_type', $relatedType)
                    ->where('related_id', (string) $relatedId)
                    ->whereDate('created_at', $todayStr)
                    ->whereIn('status', ['sent', 'pending'])
                    ->exists();

                if ($alreadyDispatched) {
                    $skipped++;
                    continue;
                }
            }

            // Render Header per Angkatan
            $pesanHeader = str_replace(
                ['{nama_sekolah}', '{hari}', '{tanggal}', '{tingkat}', '{angkatan}'],
                [$namaSekolah, $hari, $tanggal, $gradeLabel, $gradeLabel],
                $setting->template_header
            );
            $pesanHeader = str_replace('Seluruh Kelas', $gradeLabel, $pesanHeader);

            // Render Baris per Kelas untuk Angkatan Ini
            $rowsPesan = [];
            foreach ($kaList->sortBy(fn($k) => $k->kelas?->name ?? '') as $kelasAjaran) {
                $enrollments = EnrollmentSiswa::with('siswa')
                    ->where('academic_year_id', $currentYear->id)
                    ->where('class_id', $kelasAjaran->class_id)
                    ->where('status', 'aktif')
                    ->get();

                if ($enrollments->isEmpty()) {
                    continue;
                }

                $totalSiswa  = $enrollments->count();
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

                $baris = str_replace(
                    [
                        '{nama_kelas}',
                        '{total_siswa}',
                        '{jumlah_hadir}',
                        '{jumlah_terlambat}',
                        '{nama_terlambat}',
                        '{jumlah_sakit}',
                        '{nama_sakit}',
                        '{jumlah_izin}',
                        '{nama_izin}',
                        '{jumlah_alpa}',
                        '{nama_alpa}',
                        '{jumlah_alfa}',
                        '{nama_alfa}',
                        '{jumlah_belum_presensi}',
                        '{nama_belum_presensi}',
                    ],
                    [
                        $namaKelas,
                        $totalSiswa,
                        $hadir,
                        $telat,
                        $namaTelat,
                        $sakit,
                        $namaSakit,
                        $izin,
                        $namaIzin,
                        $alpa,
                        $namaAlpa,
                        $alpa,
                        $namaAlpa,
                        $belum,
                        $namaBelum,
                    ],
                    $setting->template_row
                );

                // Trim spasi di ujung setiap baris dengan tetap mempertahankan baris bertingkat (multi-line)
                $barisLines   = explode("\n", $baris);
                $trimmedLines = array_map('rtrim', $barisLines);
                $rowsPesan[]  = implode("\n", $trimmedLines);
            }

            if (empty($rowsPesan)) {
                continue;
            }

            $pesanFooter   = $setting->template_footer ? "\n" . ltrim($setting->template_footer) : "";
            $pesanAngkatan = rtrim($pesanHeader) . "\n\n" . implode("\n", $rowsPesan) . $pesanFooter;

            // Dispatch Jobs Angkatan ini ke Setiap Penerima
            foreach ($resolvedRecipients as $recipient) {
                $toNumber      = $recipient['number'];
                $recipientType = $recipient['type'];

                $log = WhatsAppNotificationLog::create([
                    'module'           => 'presensi',
                    'recipient_type'   => $recipientType,
                    'recipient_number' => $toNumber,
                    'message'          => $pesanAngkatan,
                    'status'           => 'pending',
                    'response_payload' => json_encode(['info' => "School Summary Report Dispatched for {$gradeLabel}"]),
                    'related_type'     => $relatedType,
                    'related_id'       => (string) $relatedId,
                ]);

                SendWhatsAppNotificationJob::dispatch($toNumber, $pesanAngkatan, $relatedType, (string) $relatedId, $recipientType, $log->id);
                $dispatched++;
            }
        }

        return ['dispatched' => $dispatched, 'skipped' => $skipped, 'errors' => []];
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
