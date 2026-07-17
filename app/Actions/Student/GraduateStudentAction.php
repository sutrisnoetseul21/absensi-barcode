<?php

namespace App\Actions\Student;

use App\Models\Siswa;

class GraduateStudentAction
{
    /**
     * Menandai siswa sebagai lulus.
     * 
     * @param Siswa $siswa
     * @param string|null $activeYearId
     * @return void
     */
    public function execute(Siswa $siswa, ?string $activeYearId = null): void
    {
        // 1. Ubah status global siswa menjadi lulus
        $siswa->update(['status' => 'lulus']);
        
        // 2. Ubah status pendaftaran di kelas menjadi lulus
        if ($activeYearId) {
            $siswa->enrollments()->where('academic_year_id', $activeYearId)->update(['status' => 'lulus']);
        } else {
            $siswa->enrollments()->where('status', 'aktif')->update(['status' => 'lulus']);
        }
    }
}
