<?php

namespace App\Services;

use App\Models\EnrollmentSiswa;
use App\Models\PengaturanSekolah;
use App\Models\Presensi;
use Illuminate\Support\Facades\Log;

class AlpaAutomationService
{
    /**
     * Memproses penandaan Alpa secara massal untuk siswa yang belum absen hari ini.
     *
     * @param string $source Sumber pemanggilan (misal: 'cron', 'manual_admin')
     * @return array Array berisi status dan pesan atau jumlah yang diproses.
     */
    public static function process(string $source = 'manual_admin'): array
    {
        $today = now('Asia/Jakarta');
        $kalenderService = app(KalenderSekolahService::class);
        $isHariSekolahGlobal = $kalenderService->isHariSekolah($today);

        if (!$isHariSekolahGlobal) {
            Log::info("AlpaAutomationService [{$source}]: Hari ini bukan hari sekolah, proses dibatalkan.");
            return [
                'status' => 'info',
                'message' => 'Hari ini bukan hari sekolah.',
                'count' => 0
            ];
        }

        $settings = PengaturanSekolah::current();
        $activeYearId = $settings->academic_year_id_active ?? null;

        if (!$activeYearId) {
            Log::error("AlpaAutomationService [{$source}]: Tidak ada Tahun Ajaran aktif.");
            return [
                'status' => 'error',
                'message' => 'Tidak ada Tahun Ajaran aktif.',
                'count' => 0
            ];
        }

        $dateString = $today->toDateString();
        $count = 0;
        $totalProcessed = 0;

        // Proses menggunakan chunk untuk performa
        EnrollmentSiswa::with('kelas')
            ->where('academic_year_id', $activeYearId)
            ->where('status', 'aktif')
            ->chunkById(100, function ($enrollments) use ($today, $kalenderService, $dateString, $activeYearId, $source, &$count, &$totalProcessed) {
                
                // Kumpulkan ID siswa untuk mengecek presensi secara massal jika diperlukan, 
                // atau per iterasi seperti aslinya.
                foreach ($enrollments as $enrollment) {
                    $totalProcessed++;
                    
                    // Cek apakah libur khusus untuk kelas siswa ini
                    if (!$kalenderService->isHariSekolah($today, $enrollment->class_id)) {
                        continue;
                    }

                    // Cek apakah sudah absen
                    $sudahAbsen = Presensi::where('student_id', $enrollment->student_id)
                        ->where('date', $dateString)
                        ->exists();

                    if (!$sudahAbsen) {
                        $note = $source === 'cron' 
                            ? 'Otomatis Alpa oleh Sistem (Cron)' 
                            : 'Otomatis Alpa oleh Sistem (Manual Trigger)';

                        Presensi::create([
                            'student_id' => $enrollment->student_id,
                            'enrollment_id' => $enrollment->id,
                            'class_id' => $enrollment->class_id,
                            'academic_year_id' => $activeYearId,
                            'date' => $dateString,
                            'status' => 'alpa',
                            'scan_time' => null,
                            'note' => $note,
                            'source' => $source,
                        ]);
                        $count++;
                    }
                }
            });

        Log::info("AlpaAutomationService [{$source}]: Selesai memproses {$totalProcessed} siswa. Berhasil menandai {$count} siswa sebagai Alpa.");

        return [
            'status' => 'success',
            'message' => "Berhasil menandai {$count} siswa sebagai Alpa untuk hari ini.",
            'count' => $count,
            'total_processed' => $totalProcessed
        ];
    }
}
