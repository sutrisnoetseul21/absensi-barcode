<?php

namespace App\Listeners\Enrollment;

use App\Events\Student\StudentGraduated;

/**
 * Listener milik Modul Enrollment.
 *
 * Bereaksi terhadap Event StudentGraduated dengan mengubah status
 * enrollment siswa di tahun ajaran yang relevan menjadi 'lulus'.
 *
 * Jika $activeYearId tersedia, hanya enrollment di tahun ajaran
 * tersebut yang diubah. Jika tidak, semua enrollment aktif diubah.
 */
class HandleStudentGraduated
{
    public function handle(StudentGraduated $event): void
    {
        $query = $event->siswa->enrollments();

        if ($event->activeYearId) {
            $query->where('academic_year_id', $event->activeYearId);
        } else {
            $query->where('status', 'aktif');
        }

        $query->update(['status' => 'lulus']);
    }
}
