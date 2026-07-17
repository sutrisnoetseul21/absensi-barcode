<?php

namespace App\Listeners\Presensi;

use App\Events\Student\StudentGraduated;
use App\Events\Student\StudentMutated;

/**
 * Listener milik Modul Presensi.
 *
 * Mendengarkan dua Event sekaligus (PHP 8 Union Types) karena
 * keduanya memiliki efek samping yang identik di sisi Presensi:
 * menonaktifkan barcode siswa.
 *
 * Event yang ditangani:
 *  - StudentMutated   → siswa pindah, barcode tidak boleh aktif
 *  - StudentGraduated → siswa lulus, barcode tidak boleh aktif
 */
class HandleStudentDeactivatedForPresensi
{
    public function handle(StudentMutated|StudentGraduated $event): void
    {
        $event->siswa
            ->presensiProfile()
            ?->update(['barcode_active' => false]);
    }
}
