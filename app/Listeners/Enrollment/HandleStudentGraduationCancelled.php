<?php

namespace App\Listeners\Enrollment;

use App\Events\Student\StudentGraduationCancelled;

/**
 * Listener milik Modul Enrollment.
 *
 * Bereaksi terhadap Event StudentGraduationCancelled dengan
 * mengembalikan status enrollment 'lulus' menjadi 'aktif'.
 *
 * Jika $activeYearId tersedia, hanya enrollment di tahun ajaran
 * tersebut yang di-rollback. Jika tidak, semua enrollment 'lulus'
 * dikembalikan (fallback).
 */
class HandleStudentGraduationCancelled
{
    public function handle(StudentGraduationCancelled $event): void
    {
        $query = $event->siswa->enrollments();

        if ($event->activeYearId) {
            $query->where('academic_year_id', $event->activeYearId);
        } else {
            $query->where('status', 'lulus');
        }

        $query->update(['status' => 'aktif']);
    }
}
