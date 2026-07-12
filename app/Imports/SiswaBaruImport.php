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
            $nis = trim((string) ($row[1] ?? ''));
            $name = trim((string) ($row[2] ?? ''));
            $birth_place = trim((string) ($row[3] ?? ''));
            
            // Tanggal lahir Excel bisa berupa serial number, tapi mari asumsikan string YYYY-MM-DD
            $birth_date_val = trim((string) ($row[4] ?? ''));
            $birth_date = null;
            if ($birth_date_val !== '') {
                try {
                    $birth_date = \Carbon\Carbon::parse($birth_date_val)->format('Y-m-d');
                } catch (\Exception $e) {
                    // Abaikan jika format tidak valid
                }
            }
            
            $address = trim((string) ($row[5] ?? ''));
            $passwordVal = trim((string) ($row[6] ?? ''));
            $className = trim((string) ($row[7] ?? ''));

            if ($nisn === '' || $name === '' || $className === '') {
                continue; // skip incomplete rows
            }

            // Password default adalah NISN
            $password = $passwordVal === '' ? $nisn : $passwordVal;

            // Find or create student
            $existingStudent = Siswa::where('nisn', $nisn)->first();

            $dataToSave = [
                'nis' => $nis ?: null,
                'name' => $name,
                'birth_place' => $birth_place ?: null,
                'birth_date' => $birth_date,
                'address' => $address ?: null,
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
