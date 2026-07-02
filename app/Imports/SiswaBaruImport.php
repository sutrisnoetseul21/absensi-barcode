<?php

namespace App\Imports;

use App\Models\EnrollmentSiswa;
use App\Models\Kelas;
use App\Models\PengaturanSekolah;
use App\Models\Siswa;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class SiswaBaruImport implements ToCollection
{
    public function collection(Collection $rows)
    {
        $activeYearId = PengaturanSekolah::current()?->academic_year_id_active;

        foreach ($rows as $index => $row) {
            if ($index === 0) {
                continue; // skip header
            }

            $nisn = trim((string) ($row[0] ?? ''));
            $name = trim((string) ($row[1] ?? ''));
            $passwordVal = trim((string) ($row[2] ?? ''));
            $className = trim((string) ($row[3] ?? ''));

            if ($nisn === '' || $name === '' || $className === '') {
                continue; // skip incomplete rows
            }

            $password = $passwordVal === '' ? 'password' : $passwordVal;

            // Find or create student
            $existingStudent = Siswa::where('nisn', $nisn)->first();

            $dataToSave = [
                'name' => $name,
                'barcode_code' => $nisn,
                'username' => $nisn,
                'must_change_password' => false,
            ];

            if (!$existingStudent || $passwordVal !== '') {
                $dataToSave['password'] = $password; // hashed automatically by cast
            }

            if ($existingStudent) {
                $existingStudent->update($dataToSave);
                $student = $existingStudent;
            } else {
                $student = Siswa::create(array_merge(['nisn' => $nisn], $dataToSave));
            }

            // Enroll student to active year
            if ($activeYearId) {
                $kelas = Kelas::where('name', $className)->first();
                if ($kelas) {
                    EnrollmentSiswa::updateOrCreate(
                        [
                            'student_id' => $student->id,
                            'academic_year_id' => $activeYearId,
                        ],
                        [
                            'class_id' => $kelas->id,
                            'status' => 'aktif',
                        ]
                    );
                }
            }
        }
    }
}
