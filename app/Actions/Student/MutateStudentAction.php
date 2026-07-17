<?php

namespace App\Actions\Student;

use App\Events\Student\StudentMutated;
use App\Models\Siswa;
use Illuminate\Support\Facades\DB;

class MutateStudentAction
{
    /**
     * Menandai siswa sebagai mutasi (pindah/keluar sekolah).
     *
     * Perubahan status dan dispatch Event dibungkus dalam DB::transaction
     * untuk menjamin integritas data: jika Listener manapun melempar
     * exception, update status siswa akan ikut di-rollback secara otomatis.
     */
    public function execute(Siswa $siswa): void
    {
        DB::transaction(function () use ($siswa) {
            $siswa->update(['status' => 'mutasi']);

            event(new StudentMutated($siswa));
        });
    }
}
