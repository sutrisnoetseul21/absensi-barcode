<?php

namespace App\Listeners\Enrollment;

use App\Events\Student\StudentMutated;

/**
 * Listener milik Modul Enrollment.
 *
 * Bereaksi terhadap Event StudentMutated dengan mengubah status
 * semua enrollment aktif siswa tersebut menjadi 'pindah'.
 *
 * Dieksekusi di dalam DB::transaction yang sudah dibuka oleh
 * MutateStudentAction, sehingga jika Listener ini throw exception,
 * seluruh transaksi (termasuk update status siswa) akan di-rollback.
 */
class HandleStudentMutated
{
    public function handle(StudentMutated $event): void
    {
        $event->siswa
            ->enrollments()
            ->where('status', 'aktif')
            ->update(['status' => 'pindah']);
    }
}
