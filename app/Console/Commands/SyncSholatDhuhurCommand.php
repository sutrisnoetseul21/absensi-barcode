<?php

namespace App\Console\Commands;

use App\Models\PengaturanSekolah;
use App\Services\KalenderSekolahService;
use App\Services\PresensiSholatDhuhurService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SyncSholatDhuhurCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'presensi:sync-sholat-dhuhur
                            {--force : Jalankan paksa tanpa mengecek threshold jam 11:00 atau guard cache}
                            {--date= : Tanggal spesifik yang ingin disinkronkan (format Y-m-d)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sinkronisasi otomatis presensi pagi (Alpa, Izin, Sakit) ke presensi sholat dhuhur untuk semua kelas pada jam 11:00 siang hari kerja.';

    /**
     * Execute the console command.
     */
    public function handle(PresensiSholatDhuhurService $sholatService, KalenderSekolahService $kalenderService): int
    {
        $settings = PengaturanSekolah::current();
        if ($settings && !($settings->enable_sholat_dhuhur ?? true)) {
            $this->info('Fitur Sholat Dhuhur sedang dinonaktifkan di pengaturan sekolah.');
            return self::SUCCESS;
        }

        $now = now('Asia/Jakarta');
        $dateParam = $this->option('date');
        $targetDate = $dateParam ? Carbon::parse($dateParam, 'Asia/Jakarta') : $now;
        $todayStr = $targetDate->toDateString();
        $isForce = (bool) $this->option('force');

        // 1. Cek apakah hari ini adalah hari kerja dan hari pelaksanaan sholat dhuhur
        if (!$sholatService->isHariSholatDhuhur($targetDate)) {
            $desc = $sholatService->getHolidayDescription($targetDate) ?? 'Hari Libur';
            $this->info("Tanggal {$todayStr} bukan hari pelaksanaan sholat dhuhur ({$desc}). Proses dibatalkan.");
            return self::SUCCESS;
        }

        // 2. Jika bukan mode force, cek threshold jam 11:00 siang & guard idempotency
        if (!$isForce) {
            $currentTimeStr = $now->format('H:i');
            if ($currentTimeStr < '11:00') {
                // Belum jam 11:00, diam saja agar log scheduler tidak berisik
                return self::SUCCESS;
            }

            $cacheKey = 'sholat_dhuhur_auto_sync_' . $todayStr;
            if (Cache::has($cacheKey)) {
                // Sudah disinkronkan otomatis hari ini
                return self::SUCCESS;
            }
        }

        Log::info("SyncSholatDhuhurCommand: Memulai sinkronisasi presensi pagi ke sholat dhuhur untuk tanggal {$todayStr} (Force: " . ($isForce ? 'YES' : 'NO') . ")");

        $result = $sholatService->syncAllClassesFromMorning($todayStr);

        // Tandai cache agar tidak dijalankan ganda hari ini
        $cacheKey = 'sholat_dhuhur_auto_sync_' . $todayStr;
        Cache::put($cacheKey, true, now('Asia/Jakarta')->endOfDay());

        Log::info("SyncSholatDhuhurCommand: Selesai. " . ($result['message'] ?? ''));

        $this->info($result['message'] ?? 'Sinkronisasi selesai.');
        return self::SUCCESS;
    }
}
