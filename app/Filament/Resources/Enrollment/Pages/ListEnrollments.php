<?php

namespace App\Filament\Resources\Enrollment\Pages;

use App\Filament\Resources\Enrollment\EnrollmentResource;
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

    public function enrollStudent($studentId)
    {
        if (!$this->manageClassId || !$this->manageAcademicYearId) return;

        \App\Models\EnrollmentSiswa::updateOrCreate(
            [
                'student_id' => $studentId,
                'academic_year_id' => $this->manageAcademicYearId,
            ],
            [
                'class_id' => $this->manageClassId,
                'status' => 'aktif',
            ]
        );

        \Filament\Notifications\Notification::make()
            ->title('Siswa Berhasil Dimasukkan')
            ->success()
            ->send();
    }

    public function unenrollStudent($studentId)
    {
        if (!$this->manageAcademicYearId) return;

        // Cek apakah siswa sudah punya data presensi di kelas + tahun ajaran ini
        $hasPresensi = \App\Models\Presensi::where('student_id', $studentId)
            ->where('academic_year_id', $this->manageAcademicYearId)
            ->exists();

        if ($hasPresensi) {
            \Filament\Notifications\Notification::make()
                ->title('Tidak Bisa Dikeluarkan dari Kelas')
                ->body('Siswa ini sudah memiliki data presensi di kelas ini. Hapus data presensinya terlebih dahulu di menu Laporan Detail sebelum mengeluarkan siswa dari kelas.')
                ->danger()
                ->send();
            return;
        }

        \App\Models\EnrollmentSiswa::where('student_id', $studentId)
            ->where('academic_year_id', $this->manageAcademicYearId)
            ->delete();

        \Filament\Notifications\Notification::make()
            ->title('Siswa Berhasil Dikeluarkan')
            ->success()
            ->send();
    }

    public function registerNewStudent()
    {
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
