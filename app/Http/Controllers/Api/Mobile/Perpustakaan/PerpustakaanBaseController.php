<?php

namespace App\Http\Controllers\Api\Mobile\Perpustakaan;

use App\Http\Controllers\Controller;
use App\Models\EnrollmentSiswa;
use App\Models\Guru;
use App\Models\Kelas;
use App\Models\TahunAjaran;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

abstract class PerpustakaanBaseController extends Controller
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
     * Mendapatkan daftar kelas yang diampu guru pada tahun ajaran aktif:
     * 1. Wali Kelas (kelasAjarans)
     * 2. Guru Mata Pelajaran (pengajarans)
     * 3. Kelas Pantau BK (kelasPantau)
     * 4. Admin / Akses Semua Kelas
     */
    protected function getAccessibleClasses(?User $user, ?Guru $guru, ?TahunAjaran $activeYear): Collection
    {
        if (!$activeYear) {
            return collect();
        }

        $isAdminMode = false;
        if ($user) {
            $isAdminMode = (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin())
                || $user->hasAnyRole(['super_admin', 'admin_presensi', 'admin_portal_presensi']);
            try {
                if (!$isAdminMode && method_exists($user, 'can') && $user->can('portal_guru:akses_semua_kelas')) {
                    $isAdminMode = true;
                }
            } catch (\Throwable $e) {}
        }

        $classesMap = collect();

        if ($guru) {
            // 1. Wali Kelas
            $kelasWali = $guru->kelasAjarans()
                ->where('academic_year_id', $activeYear->id)
                ->with('kelas')
                ->get();
            foreach ($kelasWali as $kw) {
                if ($kw->kelas) {
                    $classesMap->put($kw->kelas->id, [
                        'id' => (string) $kw->kelas->id,
                        'name' => (string) $kw->kelas->name,
                        'grade_level' => $kw->kelas->grade_level ?? null,
                        'role' => 'Wali Kelas',
                    ]);
                }
            }

            // 2. Guru Mapel
            $pengajarans = $guru->pengajarans()
                ->whereHas('kelasAjaran', function ($q) use ($activeYear) {
                    $q->where('academic_year_id', $activeYear->id);
                })
                ->with('kelasAjaran.kelas')
                ->get();
            foreach ($pengajarans as $p) {
                if ($p->kelasAjaran && $p->kelasAjaran->kelas) {
                    $k = $p->kelasAjaran->kelas;
                    if (!$classesMap->has($k->id)) {
                        $classesMap->put($k->id, [
                            'id' => (string) $k->id,
                            'name' => (string) $k->name,
                            'grade_level' => $k->grade_level ?? null,
                            'role' => 'Guru Mapel',
                        ]);
                    }
                }
            }

            // 3. Kelas Pantau (Guru BK)
            $kelasPantau = $guru->kelasPantau()
                ->where('academic_year_id', $activeYear->id)
                ->with('kelas')
                ->get();
            foreach ($kelasPantau as $kp) {
                if ($kp->kelas) {
                    $k = $kp->kelas;
                    if (!$classesMap->has($k->id)) {
                        $classesMap->put($k->id, [
                            'id' => (string) $k->id,
                            'name' => (string) $k->name,
                            'grade_level' => $k->grade_level ?? null,
                            'role' => 'Guru BK',
                        ]);
                    }
                }
            }
        }

        // Jika mode admin atau belum ada kelas sama sekali namun admin
        if ($isAdminMode && $classesMap->isEmpty()) {
            $allKelas = Kelas::whereHas('kelasAjarans', function ($q) use ($activeYear) {
                $q->where('academic_year_id', $activeYear->id);
            })->orderBy('name', 'asc')->get();

            foreach ($allKelas as $k) {
                $classesMap->put($k->id, [
                    'id' => (string) $k->id,
                    'name' => (string) $k->name,
                    'grade_level' => $k->grade_level ?? null,
                    'role' => 'Semua Kelas',
                ]);
            }
        }

        return $classesMap->sortBy('name')->values();
    }

    /**
     * Daftar alias polymorphic untuk peminjam Guru.
     * Mengingat Guru memiliki alias morph 'guru' dan 'wali_kelas'.
     */
    protected function getTeacherPeminjamTypes(): array
    {
        return ['guru', 'wali_kelas'];
    }

    /**
     * Response JSON sukses terstandar.
     */
    protected function sendSuccess(mixed $data = null, string $message = 'Success', int $code = 200): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    /**
     * Response JSON error terstandar.
     */
    protected function sendError(string $message = 'Error', int $code = 400, mixed $errors = null): JsonResponse
    {
        $response = [
            'status' => 'error',
            'message' => $message,
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $code);
    }
}
