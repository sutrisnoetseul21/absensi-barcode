<?php

namespace App\Console\Commands\Cbt;

use App\Services\Cbt\CbtServiceInterface;
use Illuminate\Console\Command;

class CbtSyncStudentsCommand extends Command
{
    protected $signature = 'cbt:sync-students {--class=* : ID Rombel / Kelas sasaran} {--all : Sinkronkan seluruh siswa aktif}';
    protected $description = 'Sinkronisasi akun siswa aktif ke ZenCBT';

    public function handle(CbtServiceInterface $cbtService): int
    {
        $classIds = (array) $this->option('class');
        $all = $this->option('all');

        if (empty($classIds) && !$all) {
            $this->warn('Harap tentukan opsi --class=<class_id> atau gunakan --all untuk seluruh siswa.');
            return self::INVALID;
        }

        $this->info('Memulai sinkronisasi siswa ke ZenCBT...');

        try {
            $result = $cbtService->syncStudents(!empty($classIds) ? $classIds : null);
            $this->info('[SUCCESS] Data siswa berhasil disinkronkan ke ZenCBT.');
            $this->table(['Metrik', 'Nilai'], [
                ['Total Siswa Diproses', $result['total_processed'] ?? 0],
                ['Siswa Baru (Inserted)', $result['inserted'] ?? 0],
                ['Siswa Lama (Updated)', $result['updated'] ?? 0],
            ]);
            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('[ERROR] Sinkronisasi siswa gagal: ' . $e->getMessage());
            return self::FAILURE;
        }
    }
}
