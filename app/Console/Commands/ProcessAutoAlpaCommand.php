<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PengaturanSekolah;
use App\Services\AlpaAutomationService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ProcessAutoAlpaCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'presensi:auto-alpa';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Memproses penandaan Alpa secara otomatis untuk siswa yang belum absen berdasarkan waktu yang ditentukan di pengaturan.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $settings = PengaturanSekolah::current();

        if (!$settings) {
            $this->error('Pengaturan sekolah tidak ditemukan.');
            return;
        }

        if (!$settings->auto_alpa_active) {
            // Jika fitur tidak aktif, kita diam saja, tidak perlu error log agar cron tidak berisik
            return;
        }

        $now = now('Asia/Jakarta');
        $todayStr = $now->toDateString();

        // 1. Idempotency guard: cek apakah sudah jalan hari ini
        if ($settings->last_auto_alpa_run_date && $settings->last_auto_alpa_run_date->toDateString() === $todayStr) {
            // Sudah jalan hari ini, batalkan
            return;
        }

        // 2. Threshold check: pastikan waktu saat ini >= waktu setting
        // Format `auto_alpa_time` dari DB kemungkinan '09:00:00' atau '09:00'
        $autoAlpaTimeStr = $settings->auto_alpa_time;
        if (!$autoAlpaTimeStr) {
            return; // Tidak ada waktu di-setting
        }

        $autoAlpaCarbon = Carbon::createFromTimeString($autoAlpaTimeStr, 'Asia/Jakarta');
        
        // Cek jika sekarang sudah lewat atau sama dengan waktu yang diatur
        if ($now->format('H:i:s') >= $autoAlpaCarbon->format('H:i:s')) {
            Log::info("ProcessAutoAlpaCommand: Memulai proses auto alpa (Threshold tercapai: {$now->format('H:i:s')} >= {$autoAlpaCarbon->format('H:i:s')}).");
            
            // Opsional: Set guard di awal sebelum proses berat, mencegah double cron trigger if takes > 1 min
            // Namun karena already withoutOverlapping di routes/console.php, kita set saja sebelum mulai
            $settings->last_auto_alpa_run_date = $todayStr;
            $settings->save();

            // Panggil shared service
            $result = AlpaAutomationService::process('cron');

            $this->info($result['message']);
        }
    }
}
