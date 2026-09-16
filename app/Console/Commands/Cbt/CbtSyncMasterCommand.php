<?php

namespace App\Console\Commands\Cbt;

use App\Services\Cbt\CbtServiceInterface;
use Illuminate\Console\Command;

class CbtSyncMasterCommand extends Command
{
    protected $signature = 'cbt:sync-master';
    protected $description = 'Sinkronisasi data master (Tahun Ajaran, Tingkat, Kelas, Mapel) ke ZenCBT';

    public function handle(CbtServiceInterface $cbtService): int
    {
        $this->info('Memulai sinkronisasi master data ke ZenCBT...');

        try {
            $result = $cbtService->syncMasterData();
            $this->info('[SUCCESS] Master data berhasil disinkronkan ke ZenCBT.');
            $this->table(['Entitas', 'Jumlah Terproses'], [
                ['Programs (Tahun Ajaran)', $result['programs_synced'] ?? 0],
                ['Levels (Tingkat)', $result['levels_synced'] ?? 0],
                ['Groups (Rombel/Kelas)', $result['groups_synced'] ?? 0],
                ['Subjects (Mapel)', $result['subjects_synced'] ?? 0],
            ]);
            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('[ERROR] Sinkronisasi master data gagal: ' . $e->getMessage());
            return self::FAILURE;
        }
    }
}
