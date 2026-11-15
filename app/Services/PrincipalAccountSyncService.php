<?php

namespace App\Services;

use App\Helpers\UsernameHelper;
use App\Models\Guru;
use App\Models\Jabatan;
use App\Models\TeacherJabatan;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class PrincipalAccountSyncService
{
    /**
     * Sinkronkan data Kepala Sekolah dari PengaturanSekolah ke tabel teachers,
     * users, teacher_jabatan, dan presensi_profiles tanpa membuat duplikat.
     *
     * @return array{guru: ?Guru, user: ?User, created: bool, email: ?string}|null
     */
    public function syncFromSchoolSettings(?string $principalName, ?string $principalNip): ?array
    {
        $cleanName = trim((string) $principalName);
        if ($cleanName === '' || $cleanName === '-' || $cleanName === 'Drs. Kepala Sekolah, M.Pd') {
            return null;
        }

        $digitsNip = preg_replace('/\D/', '', trim((string) $principalNip));
        $cleanNip = $digitsNip !== '' ? $digitsNip : null;

        return DB::transaction(function () use ($cleanName, $cleanNip): array {
            $jabatanKepsek = Jabatan::firstOrCreate(['nama_jabatan' => 'Kepala Sekolah']);
            $today = now()->toDateString();

            $activeKepsek = $this->findActivePrincipalGuru($jabatanKepsek->id, $today);
            $matchedGuru = $this->findMatchingGuru($cleanName, $cleanNip);

            // Jika sebelumnya ada Kepsek aktif yang jelas orang berbeda (NIP & nama berbeda), tutup masa jabatannya
            if ($activeKepsek && $matchedGuru && $activeKepsek->id !== $matchedGuru->id) {
                $this->closeActivePrincipalTenure($activeKepsek->id, $jabatanKepsek->id, $today);
            } elseif ($activeKepsek && ! $matchedGuru) {
                $oldNipDifferent = $activeKepsek->nip && $cleanNip && $activeKepsek->nip !== $cleanNip;
                if ($oldNipDifferent) {
                    $this->closeActivePrincipalTenure($activeKepsek->id, $jabatanKepsek->id, $today);
                } else {
                    $matchedGuru = $activeKepsek;
                }
            }

            $created = false;
            if ($matchedGuru) {
                $updateData = ['name' => $cleanName];
                if ($cleanNip !== null) {
                    $updateData['nip'] = $cleanNip;
                }
                $matchedGuru->update($updateData);
                $guru = $matchedGuru;
            } else {
                $guru = Guru::create([
                    'name' => $cleanName,
                    'nip' => $cleanNip,
                ]);
                $created = true;
            }

            $user = $this->ensureUserLinkedToGuru($guru, $cleanName, $cleanNip);
            $this->ensurePresensiBarcode($guru);
            $this->ensureActivePrincipalJabatan($guru->id, $jabatanKepsek->id, $today);

            return [
                'guru' => $guru->fresh(),
                'user' => $user?->fresh(),
                'created' => $created,
                'email' => $user?->email,
            ];
        });
    }

    protected function findActivePrincipalGuru(int $jabatanId, string $today): ?Guru
    {
        return Guru::whereHas('teacherJabatans', function ($q) use ($jabatanId, $today) {
            $q->where('jabatan_id', $jabatanId)
                ->where(function ($d) use ($today) {
                    $d->whereNull('tanggal_selesai')
                        ->orWhere('tanggal_selesai', '>=', $today);
                });
        })->first();
    }

    protected function findMatchingGuru(string $cleanName, ?string $cleanNip): ?Guru
    {
        if ($cleanNip !== null) {
            $byNip = Guru::where('nip', $cleanNip)->first();
            if ($byNip) {
                return $byNip;
            }
        }

        return Guru::whereRaw('LOWER(name) = ?', [strtolower($cleanName)])->first();
    }

    protected function closeActivePrincipalTenure(string $guruId, int $jabatanId, string $today): void
    {
        TeacherJabatan::where('teacher_id', $guruId)
            ->where('jabatan_id', $jabatanId)
            ->where(function ($q) use ($today) {
                $q->whereNull('tanggal_selesai')
                    ->orWhere('tanggal_selesai', '>=', $today);
            })
            ->update(['tanggal_selesai' => $today]);
    }

    protected function ensureUserLinkedToGuru(Guru $guru, string $cleanName, ?string $cleanNip): User
    {
        $user = $guru->user ?? User::where('teacher_id', $guru->id)->first();

        if (! $user) {
            $domain = config('school.email_domain', 'sekolah.sch.id');
            $baseUsername = $cleanNip ?: UsernameHelper::nameToUsername($cleanName);
            $email = $baseUsername . '@' . $domain;
            $candidateByEmail = User::firstWhere('email', $email);

            if (
                $candidateByEmail
                && ! $candidateByEmail->is_super_admin
                && (empty($candidateByEmail->teacher_id) || $candidateByEmail->teacher_id === $guru->id)
            ) {
                $user = $candidateByEmail;
            } else {
                $uniqueUsername = UsernameHelper::ensureUnique($baseUsername, $guru->id);
                $email = $uniqueUsername . '@' . $domain;

                $user = User::create([
                    'name' => $cleanName,
                    'email' => $email,
                    'password' => 'password',
                    'must_change_password' => false,
                    'teacher_id' => $guru->id,
                ]);
            }
        }

        if (! $user->is_super_admin) {
            $user->update([
                'name' => $cleanName,
                'teacher_id' => $guru->id,
            ]);
        } elseif (empty($user->teacher_id)) {
            $user->update(['teacher_id' => $guru->id]);
        }

        if ($guru->user_id !== $user->id) {
            $guru->update(['user_id' => $user->id]);
        }

        Role::firstOrCreate(['name' => 'wali_kelas', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'spikap_kepala_sekolah', 'guard_name' => 'web']);

        if (! $user->hasRole('wali_kelas')) {
            $user->assignRole('wali_kelas');
        }
        if (! $user->hasRole('spikap_kepala_sekolah')) {
            $user->assignRole('spikap_kepala_sekolah');
        }

        return $user;
    }

    protected function ensurePresensiBarcode(Guru $guru): void
    {
        $profile = $guru->presensiProfile()->firstOrCreate(
            ['teacher_id' => $guru->id],
            ['id' => (string) Str::uuid()]
        );

        if (empty($profile->barcode_code)) {
            $code = $guru->nip ?: ('2' . mt_rand(100000000, 999999999));
            $profile->update(['barcode_code' => $code]);
        }
    }

    protected function ensureActivePrincipalJabatan(string $guruId, int $jabatanId, string $today): void
    {
        $exists = TeacherJabatan::where('teacher_id', $guruId)
            ->where('jabatan_id', $jabatanId)
            ->where(function ($q) use ($today) {
                $q->whereNull('tanggal_selesai')
                    ->orWhere('tanggal_selesai', '>=', $today);
            })
            ->exists();

        if (! $exists) {
            TeacherJabatan::create([
                'teacher_id' => $guruId,
                'jabatan_id' => $jabatanId,
                'tanggal_mulai' => $today,
                'tanggal_selesai' => null,
            ]);
        }
    }
}
