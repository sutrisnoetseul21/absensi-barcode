<?php

namespace App\Actions\Student;

use App\Models\Siswa;

class MutateStudentAction
{
    /**
     * Menandai siswa sebagai mutasi (pindah/keluar sekolah).
     * 
     * @param Siswa $siswa
     * @return void
     */
    public function execute(Siswa $siswa): void
    {
        // 1. Ubah status global siswa menjadi mutasi
        $siswa->update(['status' => 'mutasi']);
        
        // 2. Ubah status pendaftaran di kelas yang sedang aktif menjadi 'pindah'
        $siswa->enrollments()->where('status', 'aktif')->update(['status' => 'pindah']);
    }
}
