<?php

namespace App\Console\Commands\Cbt;

use App\Models\UjianAkademik;
use App\Services\Cbt\CbtServiceInterface;
use Illuminate\Console\Command;

class CbtPullResultsCommand extends Command
{
    protected $signature = 'cbt:pull-results {cbt_exam_id : ID Event Ujian di ZenCBT} {ujian_akademik_id : UUID Agenda Ujian di Laravel}';
    protected $description = 'Menarik hasil nilai ujian dari ZenCBT ke database nilai_ujians Laravel';

    public function handle(CbtServiceInterface $cbtService): int
    {
        $cbtExamId = $this->argument('cbt_exam_id');
        $ujianAkademikId = $this->argument('ujian_akademik_id');

        $ujian = UjianAkademik::find($ujianAkademikId);
        if (!$ujian) {
            $this->error("[ERROR] Agenda ujian dengan ID '{$ujianAkademikId}' tidak ditemukan di Laravel.");
            return self::FAILURE;
        }

        $this->info("Menarik hasil ujian '{$ujian->nama_ujian}' dari ZenCBT (Exam ID: {$cbtExamId})...");

        try {
            $result = $cbtService->pullExamResults($cbtExamId, $ujian);
            $this->info('[SUCCESS] Hasil nilai ujian berhasil ditarik dan disimpan.');
            $this->table(['Metrik', 'Nilai'], [
                ['Total Baris Hasil CBT', $result['total_results'] ?? 0],
                ['Berhasil Disimpan / Diupdate', $result['synced'] ?? 0],
                ['Dilewati (NISN tidak ditemukan)', $result['skipped'] ?? 0],
            ]);
            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('[ERROR] Penarikan nilai gagal: ' . $e->getMessage());
            return self::FAILURE;
        }
    }
}
