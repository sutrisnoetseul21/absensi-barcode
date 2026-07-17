<?php

namespace App\Events\Student;

use App\Models\Siswa;

/**
 * Event ini di-dispatch ketika seorang siswa yang sebelumnya
 * berstatus Mutasi diaktifkan kembali oleh ReactivateStudentAction.
 *
 * Listener Enrollment akan mengembalikan status enrollment 'pindah'
 * menjadi 'aktif'. Listener Presensi akan mengaktifkan kembali barcode.
 */
class StudentReactivated
{
    public function __construct(
        public readonly Siswa $siswa
    ) {}
}
