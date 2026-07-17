<?php

namespace App\Listeners\Enrollment;

use App\Events\Student\StudentReactivated;

/**
 * Listener milik Modul Enrollment.
 *
 * Bereaksi terhadap Event StudentReactivated dengan mengembalikan
 * status enrollment yang sebelumnya 'pindah' menjadi 'aktif' kembali.
 */
class HandleStudentReactivated
{
    public function handle(StudentReactivated $event): void
    {
        $event->siswa
            ->enrollments()
            ->where('status', 'pindah')
            ->update(['status' => 'aktif']);
    }
}
