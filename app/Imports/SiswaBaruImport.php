<?php

namespace App\Imports;

use App\Models\Siswa;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

/**
 * Import Siswa Baru — hanya mengisi tabel students (Master Data).
 *
 * Kolom Kelas dan logika enrollment telah dihapus sesuai arsitektur
 * pisah total (Refactoring Tahap 3). Pendaftaran kelas dilakukan
 * secara terpisah melalui EnrollmentResource (Modul Akademik).
 */
class SiswaBaruImport implements ToCollection
{
    public function collection(Collection $rows)
    {
        foreach ($rows as $index => $row) {
            if ($index === 0) {
                continue; // skip header
            }

            $nisn = trim((string) ($row[0] ?? ''));
            $nis  = trim((string) ($row[1] ?? ''));
            $name = trim((string) ($row[2] ?? ''));
            $birth_place = trim((string) ($row[3] ?? ''));

            // Tanggal lahir Excel bisa berupa serial number atau string YYYY-MM-DD
            $birth_date_val = trim((string) ($row[4] ?? ''));
            $birth_date     = null;
            if ($birth_date_val !== '') {
                try {
                    $birth_date = \Carbon\Carbon::parse($birth_date_val)->format('Y-m-d');
                } catch (\Exception $e) {
                    // Abaikan jika format tidak valid
                }
            }

            $address      = trim((string) ($row[5] ?? ''));
            $passwordVal  = trim((string) ($row[6] ?? ''));

            if ($nisn === '' || $name === '') {
                continue; // skip baris tidak lengkap
            }

            // Password default adalah NISN jika kolom dikosongkan
            $password = $passwordVal === '' ? $nisn : $passwordVal;

            $dataToSave = [
                'nis'                  => $nis ?: null,
                'name'                 => $name,
                'birth_place'          => $birth_place ?: null,
                'birth_date'           => $birth_date,
                'address'              => $address ?: null,
                'barcode_code'         => $nisn,
                'username'             => $nisn,
                'must_change_password' => false,
            ];

            $existingStudent = Siswa::where('nisn', $nisn)->first();

            if (!$existingStudent || $passwordVal !== '') {
                $dataToSave['password'] = $password;
            }

            if ($existingStudent) {
                $existingStudent->update($dataToSave);
            } else {
                Siswa::create(array_merge(['nisn' => $nisn], $dataToSave));
            }
        }
    }
}
