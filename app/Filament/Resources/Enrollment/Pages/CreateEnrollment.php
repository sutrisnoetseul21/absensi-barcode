<?php

namespace App\Filament\Resources\Enrollment\Pages;

use App\Filament\Resources\Enrollment\EnrollmentResource;
use App\Models\EnrollmentSiswa;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateEnrollment extends CreateRecord
{
    protected static string $resource = EnrollmentResource::class;

    /**
     * Validasi duplikat sebelum create:
     * 1 Siswa hanya boleh punya 1 enrollment per Tahun Ajaran.
     */
    protected function beforeCreate(): void
    {
        $data = $this->data;

        $exists = EnrollmentSiswa::where('student_id', $data['student_id'])
            ->where('academic_year_id', $data['academic_year_id'])
            ->exists();

        if ($exists) {
            Notification::make()
                ->title('Siswa sudah terdaftar di tahun ajaran ini')
                ->body('Satu siswa hanya bisa di-assign ke satu kelas per tahun ajaran. Gunakan fitur Edit untuk mengubah kelasnya.')
                ->danger()
                ->send();

            $this->halt(); // Batalkan proses simpan (menghindari error 500 DB)
        }
    }
}
