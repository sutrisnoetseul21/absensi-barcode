<?php

namespace App\Console\Commands\Cbt;

use App\Services\Cbt\CbtServiceInterface;
use Illuminate\Console\Command;

class CbtPingCommand extends Command
{
    protected $signature = 'cbt:ping';
    protected $description = 'Uji konektivitas ke ZenCBT Bridge API Gateway';

    public function handle(CbtServiceInterface $cbtService): int
    {
        $this->info('Menguji koneksi ke ZenCBT Bridge...');

        if (!$cbtService->ping()) {
            $this->error('[FAIL] Tidak dapat terhubung ke root ZenCBT Bridge.');
            return self::FAILURE;
        }

        $this->info('[OK] Root endpoint merespons.');
        $this->line('Menguji otentikasi & status database (/api/v1/health)...');

        try {
            $health = $cbtService->healthCheck();
            $this->info('[SUCCESS] ZenCBT Bridge terhubung normal.');
            $this->table(['Key', 'Value'], [
                ['Status', $health['status'] ?? 'unknown'],
                ['Database', $health['database'] ?? 'unknown'],
                ['Latency', ($health['latency_ms'] ?? 0) . ' ms'],
                ['DB Name', $health['db_name'] ?? 'unknown'],
            ]);
            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('[ERROR] Health check gagal: ' . $e->getMessage());
            return self::FAILURE;
        }
    }
}
