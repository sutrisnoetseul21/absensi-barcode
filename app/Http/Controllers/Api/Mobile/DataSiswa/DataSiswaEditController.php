<?php

namespace App\Http\Controllers\Api\Mobile\DataSiswa;

use App\Models\Siswa;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class DataSiswaEditController extends DataSiswaBaseController
{
    /**
     * Mengambil detail profil lengkap siswa untuk form edit biodata.
     */
    public function detail(Request $request, string $id): JsonResponse
    {
        $user = $this->resolveUser($request);
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Identitas profil Guru tidak ditemukan atau belum terautentikasi.',
            ], 401);
        }

        $student = Siswa::with(['presensiProfile', 'user', 'enrollments.kelas'])->find($id);
        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Data siswa tidak ditemukan.',
            ], 404);
        }

        $photoUrl = null;
        if ($student->photo_path && Storage::disk('public')->exists($student->photo_path)) {
            $photoUrl = url(Storage::url($student->photo_path));
        }

        $enrollment = $student->enrollmentAktif;
        $className = $enrollment?->kelas?->name ?? '-';

        return response()->json([
            'success' => true,
            'data' => [
                'id' => (string) $student->id,
                'name' => $student->name,
                'nisn' => $student->nisn ?? '-',
                'nis' => $student->nis ?? '-',
                'class_name' => $className,
                'photo_url' => $photoUrl,
                'gender' => $student->gender ?? 'L',
                'religion' => $student->religion ?? 'Islam',
                'birth_place' => $student->birth_place,
                'birth_date' => $student->birth_date ? $student->birth_date->format('Y-m-d') : null,
                'address' => $student->address,
                'no_hp' => $student->no_hp,
                'no_hp_orang_tua' => $student->no_hp_orang_tua,
                'previous_school' => $student->previous_school,
                'admission_date' => $student->admission_date ? $student->admission_date->format('Y-m-d') : null,
                'admission_class' => $student->admission_class,
                'family_status' => $student->family_status ?? 'Kandung',
                'child_order' => $student->child_order,
                'nama_ayah' => $student->nama_ayah,
                'pekerjaan_ayah' => $student->pekerjaan_ayah,
                'nama_ibu' => $student->nama_ibu,
                'pekerjaan_ibu' => $student->pekerjaan_ibu,
                'nama_wali' => $student->nama_wali,
                'pekerjaan_wali' => $student->pekerjaan_wali,
                'barcode_code' => $student->presensiProfile?->barcode_code,
                'barcode_active' => (bool) ($student->presensiProfile?->barcode_active ?? false),
                'has_user_account' => !empty($student->user_id),
            ],
        ]);
    }

    /**
     * Memperbarui seluruh field biodata lengkap siswa.
     */
    public function updateBiodata(Request $request, string $id): JsonResponse
    {
        $user = $this->resolveUser($request);
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Identitas profil Guru tidak ditemukan atau belum terautentikasi.',
            ], 401);
        }

        $student = Siswa::find($id);
        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Data siswa tidak ditemukan.',
            ], 404);
        }

        $request->validate([
            'birth_place' => 'nullable|string|max:100',
            'birth_date' => 'nullable|date',
            'address' => 'nullable|string|max:500',
            'gender' => 'nullable|in:L,P',
            'religion' => 'nullable|string|max:50',
            'previous_school' => 'nullable|string|max:255',
            'admission_date' => 'nullable|date',
            'admission_class' => 'nullable|string|max:20',
            'family_status' => 'nullable|string|max:50',
            'child_order' => 'nullable|integer|min:1|max:20',
            'nama_ayah' => 'nullable|string|max:255',
            'pekerjaan_ayah' => 'nullable|string|max:255',
            'nama_ibu' => 'nullable|string|max:255',
            'pekerjaan_ibu' => 'nullable|string|max:255',
            'nama_wali' => 'nullable|string|max:255',
            'pekerjaan_wali' => 'nullable|string|max:255',
            'no_hp' => 'nullable|string|max:30',
            'no_hp_orang_tua' => 'nullable|string|max:30',
        ]);

        $student->birth_place = $request->input('birth_place');
        $student->birth_date = $request->input('birth_date') ?: null;
        $student->address = $request->input('address');
        $student->gender = $request->input('gender') ?: $student->gender;
        $student->religion = $request->input('religion') ?: $student->religion;
        $student->previous_school = $request->input('previous_school');
        $student->admission_date = $request->input('admission_date') ?: null;
        $student->admission_class = $request->input('admission_class');
        $student->family_status = $request->input('family_status') ?: null;
        $student->child_order = $request->input('child_order') ?: null;

        $student->nama_ayah = $request->input('nama_ayah');
        $student->pekerjaan_ayah = $request->input('pekerjaan_ayah');
        $student->nama_ibu = $request->input('nama_ibu');
        $student->pekerjaan_ibu = $request->input('pekerjaan_ibu');
        $student->nama_wali = $request->input('nama_wali');
        $student->pekerjaan_wali = $request->input('pekerjaan_wali');

        $student->no_hp = $this->sanitizePhoneNumber($request->input('no_hp'));
        $student->no_hp_orang_tua = $this->sanitizePhoneNumber($request->input('no_hp_orang_tua'));

        $student->save();

        return response()->json([
            'success' => true,
            'message' => 'Biodata siswa berhasil diperbarui.',
            'data' => [
                'id' => (string) $student->id,
                'name' => $student->name,
                'no_hp' => $student->no_hp,
                'no_hp_orang_tua' => $student->no_hp_orang_tua,
            ],
        ]);
    }

    /**
     * Memperbarui kontak cepat No. HP Siswa & Orang Tua.
     */
    public function updateNoHp(Request $request, string $id): JsonResponse
    {
        $user = $this->resolveUser($request);
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Identitas profil Guru tidak ditemukan atau belum terautentikasi.',
            ], 401);
        }

        $student = Siswa::find($id);
        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Data siswa tidak ditemukan.',
            ], 404);
        }

        $request->validate([
            'no_hp' => 'nullable|string|max:30',
            'no_hp_orang_tua' => 'nullable|string|max:30',
        ]);

        $student->no_hp = $this->sanitizePhoneNumber($request->input('no_hp'));
        $student->no_hp_orang_tua = $this->sanitizePhoneNumber($request->input('no_hp_orang_tua'));
        $student->save();

        return response()->json([
            'success' => true,
            'message' => 'Nomor HP Siswa & Orang Tua berhasil diperbarui.',
            'data' => [
                'id' => (string) $student->id,
                'name' => $student->name,
                'no_hp' => $student->no_hp,
                'no_hp_orang_tua' => $student->no_hp_orang_tua,
            ],
        ]);
    }

    /**
     * Mengubah kata sandi akun login siswa (minimal 6 karakter).
     */
    public function gantiPassword(Request $request, string $id): JsonResponse
    {
        $user = $this->resolveUser($request);
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Identitas profil Guru tidak ditemukan atau belum terautentikasi.',
            ], 401);
        }

        $student = Siswa::find($id);
        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Data siswa tidak ditemukan.',
            ], 404);
        }

        if (!$student->user_id) {
            return response()->json([
                'success' => false,
                'message' => 'Siswa ini belum memiliki akun login yang terhubung.',
            ], 422);
        }

        $request->validate([
            'new_password' => 'required|string|min:6|same:new_password_confirmation',
            'new_password_confirmation' => 'required|string',
        ], [
            'new_password.min' => 'Password minimal terdiri dari 6 karakter.',
            'new_password.same' => 'Konfirmasi password tidak cocok dengan password baru.',
        ]);

        $studentUser = User::find($student->user_id);
        if (!$studentUser) {
            return response()->json([
                'success' => false,
                'message' => 'Akun pengguna siswa tidak ditemukan di sistem.',
            ], 404);
        }

        $studentUser->password = Hash::make($request->input('new_password'));
        $studentUser->save();

        return response()->json([
            'success' => true,
            'message' => "Password akun siswa {$student->name} berhasil diperbarui.",
        ]);
    }

    /**
     * Mengunggah foto profil baru siswa langsung dari HP (Kamera/Galeri).
     */
    public function uploadFoto(Request $request, string $id): JsonResponse
    {
        $user = $this->resolveUser($request);
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Identitas profil Guru tidak ditemukan atau belum terautentikasi.',
            ], 401);
        }

        $student = Siswa::find($id);
        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Data siswa tidak ditemukan.',
            ], 404);
        }

        $request->validate([
            'photo' => 'required|image|mimes:jpg,jpeg,png,webp|max:5120',
        ], [
            'photo.required' => 'Silakan pilih berkas foto yang akan diunggah.',
            'photo.image' => 'Berkas harus berupa gambar valid.',
            'photo.max' => 'Ukuran foto maksimal 5 MB.',
        ]);

        $file = $request->file('photo');
        $ext = strtolower($file->guessExtension() ?: $file->getClientOriginalExtension() ?: 'jpg');
        if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp'], true)) {
            $ext = 'jpg';
        }

        $newFilename = Str::random(24) . '.' . $ext;
        $destDir = storage_path('app/public/siswa-photos');
        if (!file_exists($destDir)) {
            mkdir($destDir, 0755, true);
        }
        $targetPath = $destDir . '/' . $newFilename;

        try {
            $imageManager = new ImageManager(new Driver());
            $image = $imageManager->decode($file->getRealPath());
            $image->scaleDown(width: 500, height: 500);

            if ($ext === 'png') {
                $image->save($targetPath);
            } else {
                $image->save($targetPath, 85);
            }
        } catch (\Throwable $e) {
            // Fallback copy standar jika GD driver gagal memproses
            $file->move($destDir, $newFilename);
        }

        // Hapus foto lama jika ada
        if ($student->photo_path && Storage::disk('public')->exists($student->photo_path)) {
            Storage::disk('public')->delete($student->photo_path);
        }

        $student->photo_path = 'siswa-photos/' . $newFilename;
        $student->save();

        $photoUrl = url(Storage::url($student->photo_path));

        return response()->json([
            'success' => true,
            'message' => 'Foto profil siswa berhasil diperbarui.',
            'data' => [
                'id' => (string) $student->id,
                'name' => $student->name,
                'photo_url' => $photoUrl,
            ],
        ]);
    }
}
