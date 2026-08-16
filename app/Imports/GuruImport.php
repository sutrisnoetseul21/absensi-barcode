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
        // ─── Tweak Performa: Hindari Timeout Hashing Bcrypt ───────────────────
        \Illuminate\Support\Facades\Config::set('hashing.bcrypt.rounds', 4);
        set_time_limit(300); // 5 menit

        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'wali_kelas', 'guard_name' => 'web']);

        
        foreach ($rows as $index => $row) {
            if ($index === 0) {
                continue; // skip header
            }

            $name = trim((string) ($row[0] ?? ''), " '\"\t\n\r\0\x0B");
            
            $rawNip = trim((string) ($row[1] ?? ''), " '\"\t\n\r\0\x0B");
            $nip = preg_replace('/\D/', '', $rawNip); // Hanya ambil angka
            $no_hp = trim((string) ($row[2] ?? ''));
            $passwordVal = trim((string) ($row[3] ?? ''));

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

            $excludeId = $existingGuru ? $existingGuru->id : null;
            $username = $existingGuru ? null : UsernameHelper::generateForGuru($name, $nip, $excludeId);
            $email = $username ? $username . '@' . config('school.email_domain', 'sekolah.sch.id') : null;

            $dataToSave = [
                'name' => $name,
                'nip' => $nip,
                'no_hp' => $no_hp !== '' ? $no_hp : null,
            ];

            if ($existingGuru) {
                $existingGuru->update($dataToSave);
                
                $user = \App\Models\User::where('teacher_id', $existingGuru->id)
                            ->orWhere('id', $existingGuru->user_id)
                            ->first();
                
                if ($user) {
                    $userData = [
                        'name' => $name,
                    ];
                    if ($passwordVal !== '') {
                        $userData['password'] = $password;
                    }
                    $user->update($userData);

                    // Pastikan role wali_kelas sudah ada (guru lama mungkin belum punya role)
                    if (!$user->hasRole('wali_kelas')) {
                        $user->assignRole('wali_kelas');
                    }
                } else {
                    $email = $email ?? UsernameHelper::generateForGuru($name, $nip, $excludeId) . '@' . config('school.email_domain', 'sekolah.sch.id');
                    $user = \App\Models\User::firstOrCreate(
                        ['email' => $email],
                        [
                            'name' => $name,
                            'password' => $password,
                            'must_change_password' => false,
                            'teacher_id' => $existingGuru->id,
                        ]
                    );
                    $user->assignRole('wali_kelas');
                    $existingGuru->update(['user_id' => $user->id]);
                }
            } else {
                $user = \App\Models\User::firstOrCreate(
                    ['email' => $email],
                    [
                        'name' => $name,
                        'password' => $password,
                        'must_change_password' => false,
                    ]
                );
                $user->assignRole('wali_kelas');

                $dataToSave['user_id'] = $user->id;
                $guru = Guru::create($dataToSave);
                
                $user->update(['teacher_id' => $guru->id]);
            }
        }
    }
}
