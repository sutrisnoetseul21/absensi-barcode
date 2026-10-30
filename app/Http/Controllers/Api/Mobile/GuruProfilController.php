<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Guru;
use App\Models\TahunAjaran;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class GuruProfilController extends Controller
{
    /**
     * Resolusi user yang terautentikasi (Sanctum Token / Fallback Base64 Token / Header X-User-Identifier / Query).
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
     * GET /api/v1/guru/profil
     * Ambil data lengkap profil guru: User, Biodata, Penugasan Akademik, dan Foto.
     */
    public function getProfile(Request $request): JsonResponse
    {
        $user = $this->resolveUser($request);
        if (!$user) {
            return response()->json([
                'success' => false,
                'status' => 'error',
                'message' => 'Sesi tidak valid atau identitas guru tidak ditemukan.',
            ], 401);
        }

        $teacher = $this->resolveTeacher($request);
        $activeYear = $this->getActiveAcademicYear();

        $kelasWaliList = [];
        $kelasPantauList = [];
        $mapelList = [];
        $jabatanList = [];

        if ($teacher && $activeYear) {
            // Wali Kelas
            $kelasWaliList = $teacher->kelasAjarans()
                ->where('academic_year_id', $activeYear->id)
                ->with('kelas')
                ->get()
                ->map(fn($ka) => $ka->kelas->name ?? '-')
                ->filter()
                ->values()
                ->toArray();

            // Kelas Pantau BK
            $kelasPantauList = $teacher->kelasPantau()
                ->where('academic_year_id', $activeYear->id)
                ->with('kelas')
                ->get()
                ->map(fn($kp) => $kp->kelas->name ?? '-')
                ->filter()
                ->values()
                ->toArray();

            // Mapel yang diampu
            $mapelList = $teacher->pengajarans()
                ->whereHas('kelasAjaran', function ($q) use ($activeYear) {
                    $q->where('academic_year_id', $activeYear->id);
                })
                ->with(['mataPelajaran', 'kelasAjaran.kelas'])
                ->get()
                ->map(fn($p) => ($p->mataPelajaran->nama_mapel ?? 'Mapel') . ' (' . ($p->kelasAjaran->kelas->name ?? '') . ')')
                ->filter()
                ->values()
                ->toArray();
        }

        if ($teacher) {
            $jabatanList = $teacher->semua_jabatan ?? [];
        }

        $photoPath = $teacher->photo_path ?? null;
        $photoUrl = null;
        if (!empty($photoPath)) {
            $photoUrl = asset('storage/' . ltrim($photoPath, '/'));
        }

        return response()->json([
            'success' => true,
            'status' => 'success',
            'message' => 'Data profil guru berhasil dimuat.',
            'data' => [
                'user' => [
                    'id' => (string) $user->id,
                    'name' => (string) ($teacher->name ?? $user->name),
                    'email' => (string) $user->email,
                    'no_hp' => (string) ($user->no_hp ?? $teacher->no_hp ?? ''),
                ],
                'teacher' => [
                    'id' => $teacher ? (string) $teacher->id : null,
                    'name' => $teacher ? (string) $teacher->name : (string) $user->name,
                    'nip' => $teacher && $teacher->nip ? (string) $teacher->nip : '-',
                    'no_hp' => $teacher && $teacher->no_hp ? (string) $teacher->no_hp : (string) ($user->no_hp ?? ''),
                    'photo_path' => $photoPath,
                    'photo_url' => $photoUrl,
                    'microsite_url' => $teacher->microsite_url ?? null,
                ],
                'akademik' => [
                    'is_wali_kelas' => !empty($kelasWaliList),
                    'kelas_wali' => $kelasWaliList,
                    'kelas_pantau' => $kelasPantauList,
                    'mapel_diampu' => $mapelList,
                    'jabatan_aktif' => $jabatanList,
                    'tahun_ajaran' => $activeYear ? $activeYear->nama : null,
                ],
            ],
        ]);
    }

    /**
     * POST /api/v1/guru/profil/kontak
     * Perbarui informasi kontak (email, nomor WhatsApp, dan tautan microsite).
     */
    public function updateContact(Request $request): JsonResponse
    {
        $user = $this->resolveUser($request);
        if (!$user) {
            return response()->json([
                'success' => false,
                'status' => 'error',
                'message' => 'Sesi tidak valid atau identitas guru tidak ditemukan.',
            ], 401);
        }

        $teacher = $this->resolveTeacher($request);

        $validated = $request->validate([
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'no_hp' => 'nullable|string|max:20',
            'microsite_url' => 'nullable|url|max:500',
        ], [
            'email.required' => 'Alamat email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email ini sudah digunakan oleh akun lain.',
            'no_hp.max' => 'Nomor WhatsApp maksimal 20 karakter.',
            'microsite_url.url' => 'Format tautan microsite tidak valid. Pastikan diawali dengan http:// atau https://',
            'microsite_url.max' => 'Tautan microsite maksimal 500 karakter.',
        ]);

        $user->update([
            'email' => $validated['email'],
            'no_hp' => $validated['no_hp'] ?? null,
        ]);

        if ($teacher) {
            $teacher->update([
                'no_hp' => $validated['no_hp'] ?? null,
                'microsite_url' => $validated['microsite_url'] ?? null,
            ]);
        }

        return response()->json([
            'success' => true,
            'status' => 'success',
            'message' => 'Informasi kontak dan tautan microsite berhasil diperbarui!',
            'data' => [
                'email' => $user->email,
                'no_hp' => $teacher->no_hp ?? $user->no_hp,
                'microsite_url' => $teacher->microsite_url ?? null,
            ],
        ]);
    }

    /**
     * POST /api/v1/guru/profil/foto
     * Unggah foto profil baru (Multipart form-data).
     */
    public function updatePhoto(Request $request): JsonResponse
    {
        $user = $this->resolveUser($request);
        if (!$user) {
            return response()->json([
                'success' => false,
                'status' => 'error',
                'message' => 'Sesi tidak valid atau identitas guru tidak ditemukan.',
            ], 401);
        }

        $teacher = $this->resolveTeacher($request);
        if (!$teacher) {
            return response()->json([
                'success' => false,
                'status' => 'error',
                'message' => 'Data guru tidak ditemukan untuk akun ini.',
            ], 404);
        }

        $request->validate([
            'photo' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
        ], [
            'photo.required' => 'Pilih foto terlebih dahulu.',
            'photo.image' => 'Berkas harus berupa gambar.',
            'photo.mimes' => 'Format gambar harus jpeg, png, jpg, atau webp.',
            'photo.max' => 'Ukuran foto maksimal 2MB.',
        ]);

        // Hapus foto lama jika ada
        if ($teacher->photo_path && Storage::disk('public')->exists($teacher->photo_path)) {
            try {
                Storage::disk('public')->delete($teacher->photo_path);
            } catch (\Throwable $e) {}
        }

        $photo = $request->file('photo');
        $ext = strtolower($photo->guessExtension() ?: $photo->extension() ?: 'jpg');
        if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp'], true)) {
            $ext = 'jpg';
        }
        $filename = 'guru_' . $teacher->id . '_' . time() . '.' . $ext;
        $path = $photo->storeAs('guru-photos', $filename, 'public');

        $teacher->update([
            'photo_path' => $path,
        ]);

        $photoUrl = asset('storage/' . ltrim($path, '/'));

        return response()->json([
            'success' => true,
            'status' => 'success',
            'message' => 'Foto profil berhasil diperbarui!',
            'data' => [
                'photo_path' => $path,
                'photo_url' => $photoUrl,
            ],
        ]);
    }

    /**
     * DELETE /api/v1/guru/profil/foto
     * Hapus foto profil guru.
     */
    public function removePhoto(Request $request): JsonResponse
    {
        $user = $this->resolveUser($request);
        if (!$user) {
            return response()->json([
                'success' => false,
                'status' => 'error',
                'message' => 'Sesi tidak valid atau identitas guru tidak ditemukan.',
            ], 401);
        }

        $teacher = $this->resolveTeacher($request);
        if (!$teacher) {
            return response()->json([
                'success' => false,
                'status' => 'error',
                'message' => 'Data guru tidak ditemukan untuk akun ini.',
            ], 404);
        }

        if ($teacher->photo_path && Storage::disk('public')->exists($teacher->photo_path)) {
            try {
                Storage::disk('public')->delete($teacher->photo_path);
            } catch (\Throwable $e) {}
        }

        $teacher->update([
            'photo_path' => null,
        ]);

        return response()->json([
            'success' => true,
            'status' => 'success',
            'message' => 'Foto profil berhasil dihapus!',
            'data' => [
                'photo_path' => null,
                'photo_url' => null,
            ],
        ]);
    }

    /**
     * POST /api/v1/guru/profil/password
     * Perbarui kata sandi akun guru.
     */
    public function updatePassword(Request $request): JsonResponse
    {
        $user = $this->resolveUser($request);
        if (!$user) {
            return response()->json([
                'success' => false,
                'status' => 'error',
                'message' => 'Sesi tidak valid atau identitas guru tidak ditemukan.',
            ], 401);
        }

        $validated = $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ], [
            'current_password.required' => 'Kata sandi saat ini wajib diisi.',
            'new_password.required' => 'Kata sandi baru wajib diisi.',
            'new_password.min' => 'Kata sandi baru minimal 8 karakter.',
            'new_password.confirmed' => 'Konfirmasi kata sandi baru tidak cocok.',
        ]);

        if (!Hash::check($validated['current_password'], $user->password)) {
            return response()->json([
                'success' => false,
                'status' => 'error',
                'message' => 'Kata sandi saat ini yang Anda masukkan salah.',
                'errors' => [
                    'current_password' => ['Kata sandi saat ini yang Anda masukkan salah.'],
                ],
            ], 422);
        }

        $user->forceFill([
            'password' => Hash::make($validated['new_password']),
            'must_change_password' => false,
        ])->save();

        return response()->json([
            'success' => true,
            'status' => 'success',
            'message' => 'Kata sandi berhasil diperbarui!',
        ]);
    }
}

