<?php

namespace App\Imports;

use App\Helpers\UsernameHelper;
use App\Models\Guru;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToCollection;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class GuruImport implements ToCollection
{
    public function collection(Collection $rows)
    {
        // ─── Tweak Performa: Hindari Timeout Hashing Bcrypt ───────────────────
        \Illuminate\Support\Facades\Config::set('hashing.bcrypt.rounds', 4);
        set_time_limit(300); // 5 menit

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
        Role::firstOrCreate(['name' => 'wali_kelas', 'guard_name' => 'web']);

        if ($rows->isEmpty()) {
            return;
        }

        // Deteksi mapping kolom dari header
        $headerRow = $rows[0] ?? [];
        $headerMap = [];
        foreach ($headerRow as $colIdx => $th) {
            $h = strtolower(trim((string) $th));
            if (str_contains($h, 'nama') && !isset($headerMap['name'])) {
                $headerMap['name'] = $colIdx;
            } elseif ((str_contains($h, 'kelamin') || str_contains($h, 'jk')) && !isset($headerMap['jenis_kelamin'])) {
                $headerMap['jenis_kelamin'] = $colIdx;
            } elseif (str_contains($h, 'nip') && !isset($headerMap['nip'])) {
                $headerMap['nip'] = $colIdx;
            } elseif ((str_contains($h, 'hp') || str_contains($h, 'wa') || str_contains($h, 'telepon')) && !isset($headerMap['no_hp'])) {
                $headerMap['no_hp'] = $colIdx;
            } elseif (str_contains($h, 'email') && !isset($headerMap['email'])) {
                $headerMap['email'] = $colIdx;
            } elseif (str_contains($h, 'pass') && !isset($headerMap['password'])) {
                $headerMap['password'] = $colIdx;
            } elseif ((str_contains($h, 'facebook') || str_contains($h, 'fb')) && !isset($headerMap['facebook_url'])) {
                $headerMap['facebook_url'] = $colIdx;
            } elseif ((str_contains($h, 'instagram') || str_contains($h, 'ig')) && !isset($headerMap['instagram_url'])) {
                $headerMap['instagram_url'] = $colIdx;
            } elseif (str_contains($h, 'microsite') && !isset($headerMap['microsite_url'])) {
                $headerMap['microsite_url'] = $colIdx;
            }
        }

        $hasCustomMap = isset($headerMap['name']);

        $cleanUrl = function (?string $url): ?string {
            $url = trim((string) $url);
            if ($url === '' || $url === '-') {
                return null;
            }
            if (!preg_match('/^https?:\/\//i', $url)) {
                $url = 'https://' . ltrim($url, '@');
            }
            return filter_var($url, FILTER_VALIDATE_URL) ? $url : null;
        };

        foreach ($rows as $index => $row) {
            if ($index === 0) {
                continue; // skip header
            }

            if ($hasCustomMap) {
                $name        = trim((string) ($row[$headerMap['name'] ?? 0] ?? ''), " '\"\t\n\r\0\x0B");
                $rawJk       = isset($headerMap['jenis_kelamin']) ? trim((string) ($row[$headerMap['jenis_kelamin']] ?? '')) : '';
                $rawNip      = isset($headerMap['nip']) ? trim((string) ($row[$headerMap['nip']] ?? ''), " '\"\t\n\r\0\x0B") : '';
                $no_hp       = isset($headerMap['no_hp']) ? trim((string) ($row[$headerMap['no_hp']] ?? '')) : '';
                $emailVal    = isset($headerMap['email']) ? trim((string) ($row[$headerMap['email']] ?? '')) : '';
                $passwordVal = isset($headerMap['password']) ? trim((string) ($row[$headerMap['password']] ?? '')) : '';
                $rawFb       = isset($headerMap['facebook_url']) ? trim((string) ($row[$headerMap['facebook_url']] ?? '')) : '';
                $rawIg       = isset($headerMap['instagram_url']) ? trim((string) ($row[$headerMap['instagram_url']] ?? '')) : '';
                $rawMicro    = isset($headerMap['microsite_url']) ? trim((string) ($row[$headerMap['microsite_url']] ?? '')) : '';
            } else {
                // Fallback positional legacy: 0: Nama, 1: NIP, 2: No HP, 3: Password
                $name        = trim((string) ($row[0] ?? ''), " '\"\t\n\r\0\x0B");
                $rawJk       = '';
                $rawNip      = trim((string) ($row[1] ?? ''), " '\"\t\n\r\0\x0B");
                $no_hp       = trim((string) ($row[2] ?? ''));
                $emailVal    = '';
                $passwordVal = trim((string) ($row[3] ?? ''));
                $rawFb       = '';
                $rawIg       = '';
                $rawMicro    = '';
            }

            if ($name === '') {
                continue; // skip empty rows
            }

            // Normalisasi Jenis Kelamin
            $jenisKelamin = null;
            if (preg_match('/^p/i', $rawJk)) {
                $jenisKelamin = 'P';
            } elseif (preg_match('/^l/i', $rawJk)) {
                $jenisKelamin = 'L';
            }

            $nip = preg_replace('/\D/', '', $rawNip); // Hanya ambil angka
            $nip = ($nip === '' || $nip === '-') ? null : $nip;
            $password = $passwordVal === '' ? 'password' : $passwordVal;

            $fbUrl        = $cleanUrl($rawFb);
            $igUrl        = $cleanUrl($rawIg);
            $micrositeUrl = $cleanUrl($rawMicro);

            $existingGuru = null;
            if ($nip) {
                $existingGuru = Guru::where('nip', $nip)->first();
            }
            if (!$existingGuru) {
                $existingGuru = Guru::whereRaw('LOWER(name) = ?', [strtolower($name)])->first();
            }

            $excludeId = $existingGuru ? $existingGuru->id : null;
            $defaultDomain = config('school.email_domain', 'sekolah.sch.id');

            if (!empty($emailVal) && filter_var($emailVal, FILTER_VALIDATE_EMAIL)) {
                $email = $emailVal;
            } else {
                $username = UsernameHelper::generateForGuru($name, $nip, $excludeId);
                $email    = $username . '@' . $defaultDomain;
            }

            $dataToSave = [
                'name'  => $name,
                'nip'   => $nip,
                'no_hp' => $no_hp !== '' ? $no_hp : null,
            ];
            if ($jenisKelamin !== null) {
                $dataToSave['jenis_kelamin'] = $jenisKelamin;
            }
            if ($fbUrl !== null) {
                $dataToSave['facebook_url'] = $fbUrl;
            }
            if ($igUrl !== null) {
                $dataToSave['instagram_url'] = $igUrl;
            }
            if ($micrositeUrl !== null) {
                $dataToSave['microsite_url'] = $micrositeUrl;
            }

            if ($existingGuru) {
                $existingGuru->update($dataToSave);

                $user = User::where('teacher_id', $existingGuru->id)
                    ->orWhere('id', $existingGuru->user_id)
                    ->first();

                if (!$user && $email) {
                    $user = User::firstWhere('email', $email);
                }

                if ($user) {
                    $userData = ['name' => $name];
                    if ($passwordVal !== '') {
                        $userData['password'] = $password;
                        $userData['must_change_password'] = false;
                    }
                    if (empty($user->teacher_id)) {
                        $userData['teacher_id'] = $existingGuru->id;
                    }
                    $user->update($userData);

                    if (!$user->hasRole('wali_kelas')) {
                        $user->assignRole('wali_kelas');
                    }
                } else {
                    $user = User::firstOrCreate(
                        ['email' => $email],
                        [
                            'name'                 => $name,
                            'password'             => $password,
                            'must_change_password' => false,
                            'teacher_id'           => $existingGuru->id,
                        ]
                    );
                    $user->assignRole('wali_kelas');
                }

                if (empty($existingGuru->user_id) && $user) {
                    $existingGuru->update(['user_id' => $user->id]);
                }

                $guruRecord = $existingGuru;
            } else {
                // Defensive User Handling: Hindari crash duplicate entry jika akun user sudah ada
                $user = User::firstWhere('email', $email);

                if ($user) {
                    $userData = [
                        'name'                 => $name,
                        'must_change_password' => false,
                    ];
                    if ($passwordVal !== '') {
                        $userData['password'] = $password;
                    }
                    $user->update($userData);
                } else {
                    $user = User::create([
                        'name'                 => $name,
                        'email'                => $email,
                        'password'             => $password,
                        'must_change_password' => false,
                    ]);
                }

                if (!$user->hasRole('wali_kelas')) {
                    $user->assignRole('wali_kelas');
                }

                $dataToSave['user_id'] = $user->id;
                $guruRecord = Guru::create($dataToSave);

                $user->update(['teacher_id' => $guruRecord->id]);
            }

            // Auto-generate barcode presensi jika belum ada
            if ($guruRecord) {
                $profile = $guruRecord->presensiProfile()->firstOrCreate(
                    ['teacher_id' => $guruRecord->id],
                    ['id' => (string) Str::uuid()]
                );
                if (empty($profile->barcode_code)) {
                    $code = $guruRecord->nip;
                    if (empty($code) || $code === '-') {
                        $code = '2' . mt_rand(100000000, 999999999);
                    }
                    $profile->update(['barcode_code' => $code]);
                }
            }
        }

        \App\Models\KelompokGuruWali::syncAllTeachers();
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
}
