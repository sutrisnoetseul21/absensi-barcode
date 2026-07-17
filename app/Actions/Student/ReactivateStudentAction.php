<?php

namespace App\Actions\Student;

use App\Events\Student\StudentGraduationCancelled;
use App\Events\Student\StudentReactivated;
use App\Models\Siswa;
use Illuminate\Support\Facades\DB;

class ReactivateStudentAction
{
    /**
     * Mengaktifkan kembali siswa dari status mutasi.
     *
     * Men-dispatch StudentReactivated agar Listener Enrollment
     * mengembalikan status 'pindah' → 'aktif', dan Listener Presensi
     * mengaktifkan kembali barcode siswa.
     */
    public function execute(Siswa $siswa): void
    {
        DB::transaction(function () use ($siswa) {
            $siswa->update(['status' => 'aktif']);

            event(new StudentReactivated($siswa));
        });
    }

    /**
     * Membatalkan kelulusan siswa dan mengembalikan statusnya ke aktif.
     *
     * Men-dispatch StudentGraduationCancelled agar Listener Enrollment
     * mengembalikan status 'lulus' → 'aktif', dan Listener Presensi
     * mengaktifkan kembali barcode siswa.
     */
    public function cancelGraduation(Siswa $siswa, ?string $activeYearId = null): void
    {
        DB::transaction(function () use ($siswa, $activeYearId) {
            $siswa->update(['status' => 'aktif']);

            event(new StudentGraduationCancelled($siswa, $activeYearId));
        });
    }
}
