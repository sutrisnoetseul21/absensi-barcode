<?php

namespace App\Http\Controllers\Api\Mobile\Siswa;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use Illuminate\Http\Request;

abstract class BaseSiswaApiController extends Controller
{
    /**
     * Resolusi user yang terautentikasi (Sanctum Token / Fallback Base64 Token / Header / Parameter).
     */
    protected function resolveUser(Request $request): ?User
    {
        // 1. Coba melalui guard Sanctum
        try {
            $sanctumUser = auth('sanctum')->user();
            if ($sanctumUser instanceof User) {
                return $sanctumUser;
            }
        } catch (\Throwable $e) {
            // Abaikan jika personal_access_tokens belum dimigrasi
        }

        // 2. Coba decode Fallback Token (base64: user_id|timestamp|random)
        $bearer = $request->bearerToken();
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

        // 3. Coba melalui header X-User-Identifier atau input identifier
        $identifier = trim((string) ($request->header('X-User-Identifier') ?: $request->input('identifier', '')));
        if ($identifier !== '') {
            // Cek jika identifier adalah NISN/NIS siswa
            $siswa = Siswa::where('nisn', $identifier)
                ->orWhere('nis', $identifier)
                ->first();
            if ($siswa && $siswa->user_id) {
                $user = User::find($siswa->user_id);
                if ($user) {
                    return $user;
                }
            }

            // Cek berdasarkan email atau name
            $schoolDomain = config('school.email_domain', 'smpn1sampang.sch.id');
            $user = User::where('email', $identifier)
                ->orWhere('email', str_contains($identifier, '@') ? $identifier : $identifier . '@' . $schoolDomain)
                ->orWhere('name', $identifier)
                ->first();
            if ($user) {
                return $user;
            }
        }

        return null;
    }

    /**
     * Resolusi objek Siswa yang sedang mengakses API.
     */
    protected function resolveStudent(Request $request): ?Siswa
    {
        $user = $this->resolveUser($request);
        if ($user) {
            // Relasi student langsung
            if ($user->student) {
                return $user->student;
            }
            $siswa = Siswa::where('user_id', $user->id)->first();
            if ($siswa) {
                return $siswa;
            }
        }

        // Fallback jika dikirim identifier NISN langsung
        $identifier = trim((string) ($request->header('X-User-Identifier') ?: $request->input('identifier', '')));
        if ($identifier !== '') {
            $siswa = Siswa::where('nisn', $identifier)
                ->orWhere('nis', $identifier)
                ->orWhere('id', $identifier)
                ->first();
            if ($siswa) {
                return $siswa;
            }
        }

        return null;
    }

    /**
     * Ambil satu-satunya Tahun Ajaran Aktif.
     */
    protected function getActiveAcademicYear(): ?TahunAjaran
    {
        return TahunAjaran::where('status', 'aktif')
            ->orderBy('start_year', 'desc')
            ->first()
            ?: TahunAjaran::orderBy('start_year', 'desc')->first();
    }
}

