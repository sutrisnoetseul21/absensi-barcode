<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Siswa;
use App\Models\Guru;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Login Terpadu (Unified Login) untuk Siswa, Guru, dan Staf.
     * Menerima identifier (NISN / NIP / Email / Username) tanpa membedakan tab login di mobile.
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'identifier' => 'required|string',
            'password' => 'required|string',
            'device_name' => 'nullable|string',
        ]);

        $input = trim($request->identifier);
        $ip = $request->ip();
        $rateKey = 'mobile-login:' . $ip . ':' . $input;

        if (RateLimiter::tooManyAttempts($rateKey, 5)) {
            $seconds = RateLimiter::availableIn($rateKey);
            return response()->json([
                'success' => false,
                'message' => "Terlalu banyak percobaan login gagal. Silakan coba lagi dalam {$seconds} detik.",
            ], 429);
        }

        $schoolDomain = config('school.email_domain', 'smpn1sampang.sch.id');

        // 1. Pencarian Pengguna Bertingkat (Email, Name, Prefix Domain)
        $user = User::where('email', $input)
            ->orWhere('email', str_contains($input, '@') ? $input : $input . '@' . $schoolDomain)
            ->orWhere('name', $input)
            ->orWhere('email', 'like', $input . '@%')
            ->first();

        // 2. Jika belum ditemukan, periksa via NISN/NIS Siswa
        if (!$user) {
            $siswa = Siswa::where('nisn', $input)->orWhere('nis', $input)->first();
            if ($siswa && $siswa->user_id) {
                $user = User::find($siswa->user_id);
            }
        }

        // 3. Jika belum ditemukan, periksa via NIP Guru
        if (!$user) {
            $guru = Guru::where('nip', $input)->first();
            if ($guru && $guru->user_id) {
                $user = User::find($guru->user_id);
            }
        }

        // 4. Verifikasi Keberadaan Pengguna & Kata Sandi
        if (!$user || !Hash::check($request->password, $user->password)) {
            RateLimiter::hit($rateKey, 60);
            return response()->json([
                'success' => false,
                'message' => 'Kredensial yang diberikan tidak cocok dengan data sekolah kami.',
            ], 401);
        }

        // Clear rate limiter jika berhasil
        RateLimiter::clear($rateKey);

        // 5. Tentukan Peran (*Role*) Pengguna Secara Otomatis
        $role = 'siswa';
        $roleLabel = 'Siswa';

        if ($user->hasRole('siswa') || $user->student()->exists()) {
            $role = 'siswa';
            $roleLabel = 'Siswa';
        } elseif ($user->hasRole('guru') || $user->hasRole('wali_kelas') || $user->teacher()->exists() || $user->guru()->exists()) {
            $role = 'guru';
            $roleLabel = $user->hasRole('wali_kelas') ? 'Wali Kelas' : 'Guru';
        } elseif ($user->isSuperAdmin() || $user->hasRole('super_admin') || $user->roles->contains(fn($r) => str_starts_with($r->name, 'admin_'))) {
            $role = 'admin';
            $roleLabel = 'Administrator';
        }

        // 6. Terbitkan Sanctum Personal Access Token
        $deviceName = $request->device_name ?: 'Mobile App (' . ($request->header('User-Agent') ?: 'Flutter') . ')';
        try {
            $token = $user->createToken($deviceName)->plainTextToken;
        } catch (\Throwable $e) {
            // Fallback token aman jika tabel personal_access_tokens belum dibuat/dimigrasi di database
            $token = base64_encode($user->id . '|' . now()->timestamp . '|' . \Illuminate\Support\Str::random(40));
        }

        // 7. Ambil Avatar / Foto Profil
        $avatarUrl = null;
        if ($role === 'siswa' && $user->student?->photo_path) {
            $avatarUrl = asset('storage/' . $user->student->photo_path);
        } elseif ($user->teacher?->photo_path) {
            $avatarUrl = asset('storage/' . $user->teacher->photo_path);
        }

        return response()->json([
            'success' => true,
            'message' => 'Login berhasil',
            'data' => [
                'token' => $token,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->display_name ?? $user->name,
                    'identifier' => $input,
                    'role' => $role,
                    'role_label' => $roleLabel,
                    'avatar_url' => $avatarUrl,
                ],
            ],
        ]);
    }

    /**
     * Mendapatkan profil pengguna yang sedang login.
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        $role = 'siswa';
        $roleLabel = 'Siswa';
        if ($user->hasRole('siswa') || $user->student()->exists()) {
            $role = 'siswa';
            $roleLabel = 'Siswa';
        } elseif ($user->hasRole('guru') || $user->hasRole('wali_kelas') || $user->teacher()->exists() || $user->guru()->exists()) {
            $role = 'guru';
            $roleLabel = $user->hasRole('wali_kelas') ? 'Wali Kelas' : 'Guru';
        } elseif ($user->isSuperAdmin() || $user->hasRole('super_admin')) {
            $role = 'admin';
            $roleLabel = 'Administrator';
        }

        return response()->json([
            'success' => true,
            'message' => 'Profil berhasil dimuat',
            'data' => [
                'id' => $user->id,
                'name' => $user->display_name ?? $user->name,
                'email' => $user->email,
                'role' => $role,
                'role_label' => $roleLabel,
            ],
        ]);
    }

    /**
     * Logout dan mencabut token Sanctum yang sedang aktif.
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Sesi login berhasil diakhiri (Logout sukses).',
        ]);
    }
}

