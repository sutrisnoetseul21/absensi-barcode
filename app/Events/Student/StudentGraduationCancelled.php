<?php

namespace App\Events\Student;

use App\Models\Siswa;

/**
 * Event ini di-dispatch ketika kelulusan seorang siswa dibatalkan
 * oleh ReactivateStudentAction::cancelGraduation().
 *
 * Listener Enrollment akan mengembalikan status enrollment 'lulus'
 * menjadi 'aktif'. Listener Presensi akan mengaktifkan kembali barcode.
 */
class StudentGraduationCancelled
{
    public function __construct(
        public readonly Siswa   $siswa,
        public readonly ?string $activeYearId = null
    ) {}
}
