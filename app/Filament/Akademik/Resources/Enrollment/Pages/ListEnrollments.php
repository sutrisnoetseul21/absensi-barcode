<?php

namespace App\Filament\Akademik\Resources\Enrollment\Pages;

use App\Filament\Akademik\Resources\Enrollment\EnrollmentResource;
use App\Filament\Traits\ManagesRombelStudents;
use Filament\Resources\Pages\ListRecords;

class ListEnrollments extends ListRecords
{
    use ManagesRombelStudents;

    protected static string $resource = EnrollmentResource::class;

    // Fields for inline student registration
    public string $newStudentName = '';
    public string $newStudentNisn = '';
    public string $newStudentGender = 'L'; // L or P

    protected function getHeaderActions(): array
    {
        return \App\Filament\Akademik\Resources\Enrollment\Tables\EnrollmentTable::getPageHeaderActions();
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
