<?php

namespace App\Events\Student;

use App\Models\Siswa;

/**
 * Event ini di-dispatch ketika seorang siswa ditandai sebagai Mutasi
 * (pindah / keluar sekolah) oleh MutateStudentAction.
 *
 * Listener yang mendengarkan event ini bertanggung jawab untuk
 * bereaksi sesuai domain masing-masing (Enrollment, Presensi, dst.)
 * tanpa Modul Siswa perlu mengetahui detail implementasinya.
 */
class StudentMutated
{
    public function __construct(
        public readonly Siswa $siswa
    ) {}
}
