<?php

namespace App\Imports;

use App\Helpers\UsernameHelper;
use App\Models\Guru;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class GuruImport implements ToCollection
{
    public function collection(Collection $rows)
    {
        foreach ($rows as $index => $row) {
            if ($index === 0) {
                continue; // skip header
            }

            $name = trim((string) ($row[0] ?? ''));
            $nip = trim((string) ($row[1] ?? ''));
            $passwordVal = trim((string) ($row[2] ?? ''));

            if ($name === '') {
                continue; // skip empty rows
            }

            $nip = ($nip === '' || $nip === '-') ? null : $nip;
            $password = $passwordVal === '' ? 'password' : $passwordVal;

            $existingGuru = null;
            if ($nip) {
                $existingGuru = Guru::where('nip', $nip)->first();
            }
            if (!$existingGuru) {
                $existingGuru = Guru::whereRaw('LOWER(name) = ?', [strtolower($name)])->first();
            }

            // Generate unique username
            $username = $existingGuru ? $existingGuru->username : UsernameHelper::generateForGuru($name, $nip);

            $dataToSave = [
                'name' => $name,
                'nip' => $nip,
                'username' => $username,
                'must_change_password' => false,
            ];

            // Hanya update password jika diisi di Excel, atau jika ini record baru
            if (!$existingGuru || $passwordVal !== '') {
                $dataToSave['password'] = $password; // hashed automatically by cast
            }

            if ($existingGuru) {
                $existingGuru->update($dataToSave);
            } else {
                Guru::create($dataToSave);
            }
        }
    }
}
