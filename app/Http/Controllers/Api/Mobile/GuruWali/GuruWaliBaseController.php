<?php

namespace App\Http\Controllers\Api\Mobile\GuruWali;

use App\Http\Controllers\Controller;
use App\Models\Guru;
use App\Models\KelompokGuruWali;
use App\Models\TahunAjaran;
use App\Models\User;
use Illuminate\Http\Request;

abstract class GuruWaliBaseController extends Controller
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
     * Resolusi penugasan Kelompok Guru Wali aktif milik guru.
     */
    protected function resolveKelompok(?Guru $teacher): ?KelompokGuruWali
    {
        if (!$teacher) {
            return null;
        }

        return KelompokGuruWali::where('teacher_id', $teacher->id)
            ->where('status_aktif', true)
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
}

