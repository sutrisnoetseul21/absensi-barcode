<?php

namespace App\Events\Student;

use App\Models\Siswa;

/**
 * Event ini di-dispatch ketika seorang siswa dinyatakan Lulus
 * oleh GraduateStudentAction.
 *
 * Membawa $activeYearId opsional agar Listener Enrollment bisa
 * menargetkan enrollment di tahun ajaran yang tepat.
 */
class StudentGraduated
{
    public function __construct(
        public readonly Siswa   $siswa,
        public readonly ?string $activeYearId = null
    ) {}
}
