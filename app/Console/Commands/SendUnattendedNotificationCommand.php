<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PresensiNotificationSetting;
use App\Services\UnattendedAttendanceNotificationService;
use Carbon\Carbon;

class SendUnattendedNotificationCommand extends Command
{
    protected $signature = 'presensi:send-unattended-notification {--force : Abaikan jam cutoff untuk testing}';
    protected $description = 'Kirim notifikasi otomatis ke orang tua siswa yang belum presensi pagi';

    public function handle(UnattendedAttendanceNotificationService $service): void
    {
        $setting = PresensiNotificationSetting::where('status_presensi', 'belum')->first();

        if (!$setting || !$setting->is_active || !$setting->is_auto_send) {
            $this->info('Notifikasi otomatis belum presensi tidak aktif.');
            return;
        }

        $now         = Carbon::now('Asia/Jakarta');
        $currentTime = $now->format('H:i');
        $cutoffTime  = substr($setting->auto_send_time ?? '07:30:00', 0, 5);

        if (!$this->option('force')) {
            $cutoffToday    = Carbon::today('Asia/Jakarta')->setTimeFromTimeString($cutoffTime);
            $windowEndToday = $cutoffToday->copy()->addHour();

            // Cek apakah sudah masuk jam cutoff dan dalam window 1 jam
            if ($now->lt($cutoffToday) || $now->gt($windowEndToday)) {
                $this->info("Bukan waktunya kirim notifikasi belum hadir. Waktu saat ini: $currentTime, Window Cutoff: {$cutoffTime}–{$windowEndToday->format('H:i')}");
                return;
            }

            // Cek apakah hari ini sudah pernah dijalankan oleh cron
            $todayStr = $now->toDateString();
            if ($setting->last_auto_send_date && $setting->last_auto_send_date->toDateString() === $todayStr) {
                $this->info("Notifikasi otomatis belum presensi sudah dijalankan hari ini ({$todayStr}).");
                return;
            }

            // Cek apakah hari ini hari sekolah
            $kalenderService = app(\App\Services\KalenderSekolahService::class);
            if (!$kalenderService->isHariSekolah($now)) {
                $this->info("Hari ini bukan hari sekolah (libur). Notifikasi otomatis dibatalkan.");
                return;
            }
        }

        $this->info("Memulai pengiriman notifikasi otomatis belum presensi (Cutoff: $cutoffTime)");

        $result = $service->dispatch('cron');

        $this->info("Selesai. Dispatched: {$result['dispatched']}, Skipped: {$result['skipped']}");
        foreach ($result['errors'] as $err) {
            $this->warn($err);
        }
    }
}
