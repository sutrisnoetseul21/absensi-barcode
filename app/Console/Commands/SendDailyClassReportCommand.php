<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PresensiDailyReportSetting;
use App\Services\DailyClassReportService;
use Carbon\Carbon;

class SendDailyClassReportCommand extends Command
{
    protected $signature = 'presensi:send-daily-class-report {--force : Abaikan jam cutoff untuk testing}';
    protected $description = 'Kirim laporan harian presensi per kelas ke Wali Kelas';

    public function handle(DailyClassReportService $service): void
    {
        $setting = PresensiDailyReportSetting::current();

        if (!$setting->is_active) {
            $this->info('Laporan harian tidak aktif.');
            return;
        }

        $now         = Carbon::now();
        $currentTime = $now->format('H:i');
        $cutoffTime  = substr($setting->cutoff_time, 0, 5);

        // Cek apakah dalam window 1 jam sejak cutoff (kecuali --force)
        if (!$this->option('force')) {
            $cutoff    = Carbon::parse($cutoffTime);
            $windowEnd = $cutoff->copy()->addHour();

            // Bangun Carbon dengan tanggal hari ini + jam cutoff agar perbandingan akurat
            $cutoffToday    = Carbon::today()->setTimeFromTimeString($cutoffTime);
            $windowEndToday = $cutoffToday->copy()->addHour();

            if ($now->lt($cutoffToday) || $now->gt($windowEndToday)) {
                $this->info("Bukan waktunya kirim laporan. Current: $currentTime, Window: {$cutoffTime}–{$windowEndToday->format('H:i')}");
                return;
            }
        }

        $this->info("Memulai pengiriman laporan harian (Cutoff: $cutoffTime)");

        $result = $service->dispatch();

        $this->info("Selesai. Dispatched: {$result['dispatched']}, Skipped: {$result['skipped']}");
        foreach ($result['errors'] as $err) {
            $this->warn($err);
        }
    }
}
