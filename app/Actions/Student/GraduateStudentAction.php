<?php

namespace App\Actions\Student;

use App\Events\Student\StudentGraduated;
use App\Models\Siswa;
use Illuminate\Support\Facades\DB;

class GraduateStudentAction
{
    /**
     * Menandai siswa sebagai lulus.
     *
     * $activeYearId diteruskan ke Event agar Listener Enrollment
     * dapat menargetkan enrollment di tahun ajaran yang tepat.
     *
     * Dibungkus dalam DB::transaction untuk menjamin integritas data:
     * jika Listener manapun gagal, seluruh operasi akan di-rollback.
     */
    public function execute(Siswa $siswa, ?string $activeYearId = null): void
    {
        DB::transaction(function () use ($siswa, $activeYearId) {
            $siswa->update(['status' => 'lulus']);

            event(new StudentGraduated($siswa, $activeYearId));
        });
    }
}
