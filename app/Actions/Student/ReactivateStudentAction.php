<?php

namespace App\Actions\Student;

use App\Models\Siswa;

class ReactivateStudentAction
{
    /**
     * Mengaktifkan kembali siswa (dari status mutasi/pindah).
     * 
     * @param Siswa $siswa
     * @return void
     */
    public function execute(Siswa $siswa): void
    {
        // 1. Ubah status global siswa menjadi aktif
        $siswa->update(['status' => 'aktif']);
        
        // 2. Kembalikan status enrollment yang pindah menjadi aktif
        $siswa->enrollments()->where('status', 'pindah')->update(['status' => 'aktif']);
    }
    
    /**
     * Membatalkan kelulusan siswa dan mengembalikan statusnya ke aktif.
     * 
     * @param Siswa $siswa
     * @param string|null $activeYearId
     * @return void
     */
    public function cancelGraduation(Siswa $siswa, ?string $activeYearId = null): void
    {
        $siswa->update(['status' => 'aktif']);
        
        if ($activeYearId) {
            $siswa->enrollments()->where('academic_year_id', $activeYearId)->update(['status' => 'aktif']);
        } else {
            // Fallback jika year ID tidak spesifik
            $siswa->enrollments()->where('status', 'lulus')->update(['status' => 'aktif']);
        }
    }
}
