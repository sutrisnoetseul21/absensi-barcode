<?php

namespace App\Listeners\Presensi;

use App\Events\Student\StudentGraduationCancelled;
use App\Events\Student\StudentReactivated;

/**
 * Listener milik Modul Presensi.
 *
 * Mendengarkan dua Event sekaligus (PHP 8 Union Types) karena
 * keduanya memiliki efek samping yang identik di sisi Presensi:
 * mengaktifkan kembali barcode siswa.
 *
 * Event yang ditangani:
 *  - StudentReactivated          → siswa kembali aktif dari mutasi
 *  - StudentGraduationCancelled  → kelulusan dibatalkan, siswa kembali aktif
 */
class HandleStudentReactivatedForPresensi
{
    public function handle(StudentReactivated|StudentGraduationCancelled $event): void
    {
        $event->siswa
            ->presensiProfile()
            ?->update(['barcode_active' => true]);
    }
}
