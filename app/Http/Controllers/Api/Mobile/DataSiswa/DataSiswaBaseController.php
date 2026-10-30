<?php

namespace App\Http\Controllers\Api\Mobile\DataSiswa;

use App\Http\Controllers\Controller;
use App\Models\Guru;
use App\Models\Kelas;
use App\Models\TahunAjaran;
use App\Models\User;
use Illuminate\Http\Request;

abstract class DataSiswaBaseController extends Controller
{
    /**
     * Resolusi user yang terautentikasi (Sanctum Token / Fallback Base64 Token / Identifier Header / Query).
     */
    protected function resolveUser(Request $request): ?User
    {
        try {
            $sanctumUser = auth('sanctum')->user();
            if ($sanctumUser instanceof User) {
                return $sanctumUser;
            }
        } catch (\Throwable $e) {}

        $bearer = $request->bearerToken() ?: (string) $request->input('token', '');
        if (!empty($bearer) && !str_contains($bearer, '|')) {
            $decoded = base64_decode($bearer, true);
            if ($decoded && str_contains($decoded, '|')) {
                $parts = explode('|', $decoded);
                $userId = $parts[0] ?? null;
                if ($userId) {
                    $user = User::find($userId);
                    if ($user) {
                        return $user;
                    }
                }
            }
        }

        $identifier = trim((string) ($request->header('X-User-Identifier') ?: $request->input('identifier', '')));
        if ($identifier !== '') {
            $guru = Guru::where('nip', $identifier)
                ->orWhere('name', $identifier)
                ->first();
            if ($guru) {
                if ($guru->user_id) {
                    $user = User::find($guru->user_id);
                    if ($user) {
                        return $user;
                    }
                }
                $userByTeacher = User::where('teacher_id', $guru->id)->first();
                if ($userByTeacher) {
                    return $userByTeacher;
                }

                $virtualUser = new User();
                $virtualUser->id = $guru->id;
                $virtualUser->name = $guru->name;
                $virtualUser->email = ($guru->nip ?: 'guru') . '@sekolah.id';
                $virtualUser->teacher_id = $guru->id;
                $virtualUser->setRelation('teacher', $guru);
                $virtualUser->setRelation('guru', $guru);
                return $virtualUser;
            }

            $user = User::where('email', $identifier)
                ->orWhere('name', $identifier)
                ->orWhere('email', 'like', $identifier . '@%')
                ->first();
            if ($user) {
                return $user;
            }
        }

        return null;
    }

    /**
     * Resolusi profil guru dari user terautentikasi.
     */
    protected function resolveTeacher(Request $request): ?Guru
    {
        $user = $this->resolveUser($request);
        if (!$user) {
            return null;
        }

        if ($user->relationLoaded('teacher') && $user->teacher) {
            return $user->teacher;
        }

        if ($user->relationLoaded('guru') && $user->guru) {
            return $user->guru;
        }

        if (!empty($user->teacher_id)) {
            $guru = Guru::find($user->teacher_id);
            if ($guru) {
                return $guru;
            }
        }

        $guruByUser = Guru::where('user_id', $user->id)->first();
        if ($guruByUser) {
            return $guruByUser;
        }

        return Guru::where('email', $user->email)
            ->orWhere('name', $user->name)
            ->first();
    }

    /**
     * Ambil Tahun Ajaran Aktif terkunci.
     */
    protected function getActiveAcademicYear(): ?TahunAjaran
    {
        return TahunAjaran::where('status', 'aktif')
            ->orderBy('start_year', 'desc')
            ->first()
            ?: TahunAjaran::orderBy('start_year', 'desc')->first();
    }

    /**
     * Tentukan daftar kelas yang berhak diakses oleh Guru pada menu Data Siswa:
     * 1. Wali Kelas Utama (tabel class_academic_year / relasi kelasAjarans)
     * 2. Kelas Binaan BK (tabel teacher_class_accesses / relasi kelasPantau)
     * 3. Bypass Semua Kelas (permission portal_guru:akses_semua_kelas atau Super Admin / Admin Presensi)
     */
    protected function getAccessibleClasses(?User $user, ?string $academicYearId): array
    {
        if (!$user || !$academicYearId) {
            return [
                'classes' => collect(),
                'is_bk' => false,
                'is_bypass' => false,
                'role_summary' => 'Guru',
            ];
        }

        $isAdminMode = (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin())
            || $user->hasAnyRole(['super_admin', 'admin_presensi', 'admin_portal_presensi']);
        $hasBypass = false;
        try {
            $hasBypass = method_exists($user, 'can') && $user->can('portal_guru:akses_semua_kelas');
        } catch (\Throwable $e) {}

        $isGuruBk = false;
        try {
            $isGuruBk = method_exists($user, 'isGuruBk') && $user->isGuruBk();
        } catch (\Throwable $e) {}

        $actor = $user->teacher ?: Guru::where('user_id', $user->id)->first();

        if ($isAdminMode || $hasBypass) {
            $rawClasses = Kelas::whereHas('kelasAjarans', function ($query) use ($academicYearId) {
                $query->where('academic_year_id', $academicYearId);
            })->orderBy('name', 'asc')->get();

            $formatted = $rawClasses->map(fn(Kelas $c) => [
                'id' => $c->id,
                'name' => $c->name,
                'grade_level' => $c->grade_level,
                'access_type' => 'bypass',
                'access_label' => 'Semua Kelas',
            ])->values();

            return [
                'classes' => $formatted,
                'is_bk' => $isGuruBk,
                'is_bypass' => true,
                'role_summary' => $isAdminMode ? 'Admin Presensi' : 'Akses Semua Kelas',
            ];
        }

        if (!$actor) {
            return [
                'classes' => collect(),
                'is_bk' => $isGuruBk,
                'is_bypass' => false,
                'role_summary' => 'Guru',
            ];
        }

        // 1. Kelas Wali Kelas Utama
        $waliClasses = Kelas::whereHas('kelasAjarans', function ($query) use ($actor, $academicYearId) {
            $query->where('academic_year_id', $academicYearId)
                ->where('teacher_id', $actor->id);
        })->orderBy('name', 'asc')->get();

        // 2. Kelas Binaan BK / Kelas Pantau
        $pantauClassIds = $actor->kelasPantau()
            ->where('academic_year_id', $academicYearId)
            ->pluck('class_id')
            ->toArray();

        $pantauClasses = Kelas::whereIn('id', $pantauClassIds)
            ->orderBy('name', 'asc')
            ->get();

        $merged = collect();
        foreach ($waliClasses as $c) {
            $merged->push([
                'id' => $c->id,
                'name' => $c->name,
                'grade_level' => $c->grade_level,
                'access_type' => 'wali_kelas',
                'access_label' => 'Wali Kelas ' . $c->name,
            ]);
        }

        foreach ($pantauClasses as $c) {
            if (!$merged->contains('id', $c->id)) {
                $merged->push([
                    'id' => $c->id,
                    'name' => $c->name,
                    'grade_level' => $c->grade_level,
                    'access_type' => 'guru_bk',
                    'access_label' => 'Binaan BK ' . $c->name,
                ]);
            }
        }

        // Jika wali kelas tidak ditemukan (misal akun testing), ambil kelas di mana ada kelasAjarans untuk tahun ini
        if ($merged->isEmpty()) {
            $fallback = Kelas::whereHas('kelasAjarans', function ($q) use ($academicYearId) {
                $q->where('academic_year_id', $academicYearId);
            })->orderBy('name', 'asc')->get();

            $merged = $fallback->map(fn(Kelas $c) => [
                'id' => $c->id,
                'name' => $c->name,
                'grade_level' => $c->grade_level,
                'access_type' => 'penugasan',
                'access_label' => 'Kelas ' . $c->name,
            ]);
        }

        return [
            'classes' => $merged->values(),
            'is_bk' => $isGuruBk,
            'is_bypass' => false,
            'role_summary' => $isGuruBk ? 'Guru BK' : 'Wali Kelas',
        ];
    }

    /**
     * Sanitasi format nomor telepon ke format internasional (misal 08123 -> 628123).
     */
    protected function sanitizePhoneNumber(?string $raw): ?string
    {
        if (empty($raw)) {
            return null;
        }

        $clean = preg_replace('/[^0-9]/', '', $raw);
        if (empty($clean)) {
            return null;
        }

        if (str_starts_with($clean, '0')) {
            $clean = '62' . substr($clean, 1);
        }

        return $clean;
    }
}
