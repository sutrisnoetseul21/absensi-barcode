<?php

namespace App\Filament\Akademik\Resources\Enrollment\Pages;

use App\Filament\Akademik\Resources\Enrollment\EnrollmentResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListEnrollments extends ListRecords
{
    protected static string $resource = EnrollmentResource::class;

    public string $searchLeft = '';
    public string $searchRight = '';
    public ?string $manageClassId = null;
    public ?string $manageAcademicYearId = null;

    // Fields for inline student registration
    public string $newStudentName = '';
    public string $newStudentNisn = '';
    public string $newStudentGender = 'L'; // L or P

    protected function getHeaderActions(): array
    {
        return [];
    }

    protected function canModifyEnrollment(): bool
    {
        $user = auth()->user();
        return (bool) ($user?->isSuperAdmin() || $user?->hasRole('admin_akademik_editor') || $user?->hasRole('admin_master_editor'));
    }

    public function enrollStudent($studentId, $classId = null, $academicYearId = null)
    {
        if (!$this->canModifyEnrollment()) {
            \Filament\Notifications\Notification::make()->title('Akses Ditolak: Anda hanya memiliki hak akses melihat data.')->danger()->send();
            return false;
        }

        $classId = $classId ?: $this->manageClassId;
        $academicYearId = $academicYearId ?: $this->manageAcademicYearId;

        if (!$classId || !$academicYearId) {
            \Filament\Notifications\Notification::make()->title('Gagal: Data Kelas atau Tahun Ajaran tidak valid.')->danger()->send();
            return false;
        }

        // Set state just in case it's needed for UI updates
        $this->manageClassId = $classId;
        $this->manageAcademicYearId = $academicYearId;

        \App\Models\EnrollmentSiswa::updateOrCreate(
            [
                'student_id' => $studentId,
                'academic_year_id' => $academicYearId,
            ],
            [
                'class_id' => $classId,
                'status' => 'aktif',
            ]
        );

        \Filament\Notifications\Notification::make()
            ->title('Siswa Berhasil Dimasukkan')
            ->success()
            ->send();

        return true;
    }

    public function unenrollStudent($studentId, $academicYearId = null)
    {
        if (!$this->canModifyEnrollment()) {
            \Filament\Notifications\Notification::make()->title('Akses Ditolak: Anda hanya memiliki hak akses melihat data.')->danger()->send();
            return false;
        }

        $academicYearId = $academicYearId ?: $this->manageAcademicYearId;

        if (!$academicYearId) {
            \Filament\Notifications\Notification::make()->title('Gagal: Data Tahun Ajaran tidak valid.')->danger()->send();
            return false;
        }
        
        $this->manageAcademicYearId = $academicYearId;

        // Cek apakah siswa sudah punya data presensi di kelas + tahun ajaran ini
        $hasPresensi = \App\Models\Presensi::where('student_id', $studentId)
            ->where('academic_year_id', $academicYearId)
            ->exists();

        if ($hasPresensi) {
            \Filament\Notifications\Notification::make()
                ->title('Tidak Bisa Dikeluarkan dari Kelas')
                ->body('Siswa ini sudah memiliki data presensi di kelas ini. Hapus data presensinya terlebih dahulu di menu Laporan Detail sebelum mengeluarkan siswa dari kelas.')
                ->danger()
                ->send();
            return false;
        }

        \App\Models\EnrollmentSiswa::where('student_id', $studentId)
            ->where('academic_year_id', $academicYearId)
            ->delete();

        \Filament\Notifications\Notification::make()
            ->title('Siswa Berhasil Dikeluarkan')
            ->success()
            ->send();
            
        return true;
    }

    public function registerNewStudent()
    {
        if (!$this->canModifyEnrollment()) {
            \Filament\Notifications\Notification::make()->title('Akses Ditolak: Anda hanya memiliki hak akses melihat data.')->danger()->send();
            return;
        }

        $this->validate([
            'newStudentName' => 'required|string|max:255',
            'newStudentNisn' => 'required|string|max:20|unique:students,nisn',
            'newStudentGender' => 'required|in:L,P',
        ], [
            'newStudentName.required' => 'Nama lengkap wajib diisi.',
            'newStudentNisn.required' => 'NISN wajib diisi.',
            'newStudentNisn.unique' => 'NISN sudah terdaftar.',
        ]);

        $siswa = \App\Models\Siswa::create([
            'name' => $this->newStudentName,
            'nisn' => $this->newStudentNisn,
            'password' => '12345678', // default password
            'must_change_password' => false,
        ]);

        // Reset fields
        $this->newStudentName = '';
        $this->newStudentNisn = '';
        $this->newStudentGender = 'L';

        \Filament\Notifications\Notification::make()
            ->title('Siswa Baru Terdaftar')
            ->body("Siswa **{$siswa->name}** berhasil didaftarkan ke database (tanpa kelas).")
            ->success()
            ->send();
    }
}
