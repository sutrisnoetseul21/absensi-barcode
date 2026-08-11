<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PresensiSchoolSummarySetting;
use App\Services\SchoolSummaryReportService;
use Carbon\Carbon;

class SendSchoolSummaryReportCommand extends Command
{
    protected $signature = 'presensi:send-school-summary {--force : Abaikan jam cutoff untuk testing}';
    protected $description = 'Kirim laporan rekap presensi seluruh kelas ke Manajemen Sekolah';

    public function handle(SchoolSummaryReportService $service): void
    {
        $setting = PresensiSchoolSummarySetting::current();

        if (!$setting->is_active) {
            $this->info('Laporan rekap sekolah tidak aktif.');
            return;
        }

        $now         = Carbon::now();
        $currentTime = $now->format('H:i');
        $cutoffTime  = substr($setting->cutoff_time, 0, 5);

        // Cek apakah dalam window 1 jam sejak cutoff (kecuali --force)
        if (!$this->option('force')) {
            $cutoffToday    = Carbon::today()->setTimeFromTimeString($cutoffTime);
            $windowEndToday = $cutoffToday->copy()->addHour();

            if ($now->lt($cutoffToday) || $now->gt($windowEndToday)) {
                $this->info("Bukan waktunya kirim laporan rekap sekolah. Current: $currentTime, Window: {$cutoffTime}–{$windowEndToday->format('H:i')}");
                return;
            }
        }

        $this->info("Memulai pembuatan laporan rekap sekolah (Cutoff: $cutoffTime)");

        $result = $service->dispatch();

        $this->info("Selesai. Dispatched: {$result['dispatched']}, Skipped: {$result['skipped']}");
        foreach ($result['errors'] as $err) {
            $this->warn($err);
        }
    }
}
