<?php

namespace App\Filament\Traits;

use App\Models\EnrollmentSiswa;
use App\Models\Presensi;
use Filament\Notifications\Notification;

trait ManagesRombelStudents
{
    public string $searchLeft = '';
    public string $searchRight = '';
    public ?string $manageClassId = null;
    public ?string $manageAcademicYearId = null;

    protected function canModifyEnrollment(): bool
    {
        $user = auth()->user();

        return (bool) ($user?->isSuperAdmin() || $user?->hasRole('admin_akademik_editor') || $user?->hasRole('admin_master_editor'));
    }

    public function enrollStudent($studentId, $classId = null, $academicYearId = null)
    {
        if (!$this->canModifyEnrollment()) {
            Notification::make()->title('Akses Ditolak: Anda hanya memiliki hak akses melihat data.')->danger()->send();

            return false;
        }

        $classId = $classId ?: $this->manageClassId;
        $academicYearId = $academicYearId ?: $this->manageAcademicYearId;

        if (!$classId || !$academicYearId) {
            Notification::make()->title('Gagal: Data Kelas atau Tahun Ajaran tidak valid.')->danger()->send();

            return false;
        }

        $this->manageClassId = $classId;
        $this->manageAcademicYearId = $academicYearId;

        EnrollmentSiswa::updateOrCreate(
            [
                'student_id' => $studentId,
                'academic_year_id' => $academicYearId,
            ],
            [
                'class_id' => $classId,
                'status' => 'aktif',
            ]
        );

        Notification::make()
            ->title('Siswa Berhasil Dimasukkan')
            ->success()
            ->send();

        return true;
    }

    public function unenrollStudent($studentId, $academicYearId = null)
    {
        if (!$this->canModifyEnrollment()) {
            Notification::make()->title('Akses Ditolak: Anda hanya memiliki hak akses melihat data.')->danger()->send();

            return false;
        }

        $academicYearId = $academicYearId ?: $this->manageAcademicYearId;

        if (!$academicYearId) {
            Notification::make()->title('Gagal: Data Tahun Ajaran tidak valid.')->danger()->send();

            return false;
        }

        $this->manageAcademicYearId = $academicYearId;

        $hasPresensi = Presensi::where('student_id', $studentId)
            ->where('academic_year_id', $academicYearId)
            ->exists();

        if ($hasPresensi) {
            Notification::make()
                ->title('Tidak Bisa Dikeluarkan dari Kelas')
                ->body('Siswa ini sudah memiliki data presensi di kelas ini. Hapus data presensinya terlebih dahulu di menu Laporan Detail sebelum mengeluarkan siswa dari kelas.')
                ->danger()
                ->send();

            return false;
        }

        EnrollmentSiswa::where('student_id', $studentId)
            ->where('academic_year_id', $academicYearId)
            ->delete();

        Notification::make()
            ->title('Siswa Berhasil Dikeluarkan')
            ->success()
            ->send();

        return true;
    }
}

