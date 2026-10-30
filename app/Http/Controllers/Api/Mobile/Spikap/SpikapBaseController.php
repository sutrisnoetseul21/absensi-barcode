<?php

namespace App\Http\Controllers\Api\Mobile\Spikap;

use App\Http\Controllers\Controller;
use App\Models\Guru;
use App\Models\SpikapLaporan;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

abstract class SpikapBaseController extends Controller
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

        if ($user->email) {
            $guru = Guru::where('email', $user->email)->first();
            if ($guru) {
                return $guru;
            }
        }

        if ($user->name) {
            $guru = Guru::where('name', $user->name)->first();
            if ($guru) {
                return $guru;
            }
        }

        return null;
    }

    /**
     * Pengecekan status hak akses SPIKAP pengguna.
     * Aturan: Hanya Kepala Sekolah, Guru BK, Wali Kelas aktif, dan Admin yang diizinkan.
     */
    protected function checkSpikapAccess(?User $user): array
    {
        if (!$user) {
            return [
                'can_access' => false,
                'is_bk' => false,
                'is_wali' => false,
                'is_ks' => false,
                'is_admin' => false,
                'role_label' => 'Tamu / Belum Login',
            ];
        }

        $isAdmin = $user->hasAnyRole(['super_admin', 'spikap_admin']);
        $isKs = $user->isKepalaSekolah() || $user->hasRole('spikap_kepala_sekolah');
        $isBk = $user->isGuruBk() || $user->hasRole('spikap_guru_bk');
        $isWk = $user->isWaliKelasMurni() || ($user->hasRole('wali_kelas') && $user->teacher !== null);

        $canAccess = false;
        if (method_exists($user, 'canAccessSpikapGuru')) {
            $canAccess = $user->canAccessSpikapGuru();
        } else {
            $canAccess = $isAdmin || $isKs || $isBk || $isWk;
        }

        // Tentukan label peran yang paling representatif untuk modul SPIKAP
        $roleLabel = 'Guru Mata Pelajaran';
        if ($isAdmin) {
            $roleLabel = 'Administrator SPIKAP';
        } elseif ($isKs) {
            $roleLabel = 'Kepala Sekolah';
        } elseif ($isBk) {
            $roleLabel = 'Guru Bimbingan Konseling (BK)';
        } elseif ($isWk) {
            $roleLabel = 'Wali Kelas';
        }

        return [
            'can_access' => $canAccess,
            'is_bk' => $isBk,
            'is_wali' => $isWk,
            'is_ks' => $isKs,
            'is_admin' => $isAdmin,
            'role_label' => $roleLabel,
        ];
    }

    /**
     * Memastikan user memiliki izin akses ke modul SPIKAP.
     * Jika tidak berhak, kembalikan response JSON 403 Forbidden.
     */
    protected function requireSpikapAccess(Request $request): User|JsonResponse
    {
        $user = $this->resolveUser($request);
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Identitas profil pengguna tidak ditemukan atau sesi telah berakhir.',
            ], 401);
        }

        $access = $this->checkSpikapAccess($user);
        if (!$access['can_access']) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak. Modul SPIKAP (Anti-Perundungan) bersifat konfidensial dan hanya dapat diakses oleh Guru BK, Wali Kelas, atau Kepala Sekolah.',
                'data' => [
                    'can_access' => false,
                    'role_label' => $access['role_label'],
                ],
            ], 403);
        }

        return $user;
    }

    /**
     * Scope query SpikapLaporan yang disesuaikan secara ketat dengan kewenangan user.
     */
    protected function getScopedReports(User $user)
    {
        return SpikapLaporan::forUser($user);
    }
}
