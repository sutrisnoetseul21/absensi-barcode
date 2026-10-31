<?php

namespace App\Http\Controllers\Api\Mobile\Siswa;

use App\Models\PengaturanSekolah;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class SiswaProfilController extends BaseSiswaApiController
{
    /**
     * Mengambil informasi lengkap biodata, kontak, foto, dan status akun siswa.
     */
    public function info(Request $request): JsonResponse
    {
        $student = $this->resolveStudent($request);
        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Data siswa tidak ditemukan atau sesi telah berakhir.',
            ], 404);
        }

        $user = $this->resolveUser($request);
        if (!$user && $student->user_id) {
            $user = User::find($student->user_id);
        }

        $student->loadMissing(['presensiProfile', 'enrollmentAktif.kelas', 'enrollmentAktif.tahunAjaran']);
        $enrollment = $student->enrollmentAktif;

        $className = $enrollment?->kelas?->name ?? '-';
        $academicYear = $enrollment?->tahunAjaran?->name ?? '-';

        // Foto Siswa
        $photoUrl = null;
        if ($student->photo_path && Storage::disk('public')->exists($student->photo_path)) {
            $photoUrl = url(Storage::url($student->photo_path));
        }

        $gender = strtoupper($student->gender ?? 'L');
        $genderLabel = $gender === 'P' ? 'Perempuan' : 'Laki-laki';

        $birthPlace = $student->birth_place ?? '';
        $birthDateFormatted = $student->birth_date ? $student->birth_date->translatedFormat('d F Y') : '';
        $birthInfo = trim(($birthPlace ? $birthPlace . ', ' : '') . $birthDateFormatted);

        $allowPhotoUpload = (bool) config('school.allow_student_photo_upload', true);
        $schoolDomain = config('school.email_domain', 'smpn3kedungreja.sch.id');
        $email = $user?->email ?? ($student->nisn ? $student->nisn . '@' . $schoolDomain : '-');

        $barcodeCode = $student->presensiProfile?->barcode_code ?? $student->barcode_code ?? $student->nisn ?? 'NO-BARCODE';

        return response()->json([
            'success' => true,
            'message' => 'Profil siswa berhasil dimuat.',
            'data' => [
                'student_id' => (string) $student->id,
                'user_id' => $user ? (string) $user->id : null,
                'name' => $student->name,
                'nisn' => $student->nisn ?? '-',
                'nis' => $student->nis ?? '-',
                'email' => $email,
                'class_name' => $className,
                'academic_year' => $academicYear,
                'status' => $student->status ?? 'aktif',
                'gender' => $gender,
                'gender_label' => $genderLabel,
                'religion' => $student->religion ?? '-',
                'birth_place' => $birthPlace,
                'birth_date' => $student->birth_date ? $student->birth_date->format('Y-m-d') : null,
                'birth_info' => $birthInfo ?: '-',
                'address' => $student->address ?? '',
                'no_hp' => $student->no_hp ?? '',
                'photo_url' => $photoUrl,
                'allow_photo_upload' => $allowPhotoUpload,
                'nama_ayah' => $student->nama_ayah ?? '-',
                'pekerjaan_ayah' => $student->pekerjaan_ayah ?? '-',
                'nama_ibu' => $student->nama_ibu ?? '-',
                'pekerjaan_ibu' => $student->pekerjaan_ibu ?? '-',
                'nama_wali' => $student->nama_wali ?? '-',
                'pekerjaan_wali' => $student->pekerjaan_wali ?? '-',
                'no_hp_orang_tua' => $student->no_hp_orang_tua ?? '-',
                'barcode_code' => $barcodeCode,
            ],
        ]);
    }

    /**
     * Memperbarui alamat domisili dan nomor kontak siswa.
     */
    public function updateKontak(Request $request): JsonResponse
    {
        $student = $this->resolveStudent($request);
        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Data siswa tidak ditemukan.',
            ], 404);
        }

        $validated = $request->validate([
            'address' => 'required|string|max:500',
            'no_hp' => 'nullable|string|max:20',
            'no_hp_orang_tua' => 'nullable|string|max:20',
        ], [
            'address.required' => 'Alamat tempat tinggal tidak boleh kosong.',
            'address.max' => 'Alamat maksimal 500 karakter.',
            'no_hp.max' => 'Nomor HP maksimal 20 digit.',
        ]);

        $student->update([
            'address' => $validated['address'],
            'no_hp' => $validated['no_hp'] ?? $student->no_hp,
            'no_hp_orang_tua' => $validated['no_hp_orang_tua'] ?? $student->no_hp_orang_tua,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Alamat domisili dan kontak berhasil diperbarui.',
            'data' => [
                'address' => $student->address,
                'no_hp' => $student->no_hp,
                'no_hp_orang_tua' => $student->no_hp_orang_tua,
            ],
        ]);
    }

    /**
     * Mengunggah atau memperbarui foto profil siswa.
     */
    public function updateFoto(Request $request): JsonResponse
    {
        if (!config('school.allow_student_photo_upload', true)) {
            return response()->json([
                'success' => false,
                'message' => 'Fitur unggah foto siswa dinonaktifkan oleh kebijakan sekolah.',
            ], 403);
        }

        $student = $this->resolveStudent($request);
        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Data siswa tidak ditemukan.',
            ], 404);
        }

        $request->validate([
            'photo' => 'required|image|mimes:jpg,jpeg,png,webp|max:5120',
        ], [
            'photo.required' => 'Pilih berkas foto terlebih dahulu.',
            'photo.image' => 'Berkas harus berupa gambar valid (jpg, jpeg, png, webp).',
            'photo.max' => 'Ukuran foto maksimal 5 MB.',
        ]);

        // Hapus foto lama jika ada
        if ($student->photo_path && Storage::disk('public')->exists($student->photo_path)) {
            Storage::disk('public')->delete($student->photo_path);
        }

        $file = $request->file('photo');
        $ext = strtolower($file->guessExtension() ?: $file->getClientOriginalExtension() ?: 'jpg');
        if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp'], true)) {
            $ext = 'jpg';
        }

        $filename = 'siswa_' . $student->id . '_' . time() . '.' . $ext;
        $path = $file->storeAs('siswa-photos', $filename, 'public');

        $student->update([
            'photo_path' => $path,
        ]);

        $photoUrl = url(Storage::url($path));

        return response()->json([
            'success' => true,
            'message' => 'Foto profil berhasil diperbarui.',
            'data' => [
                'photo_path' => $path,
                'photo_url' => $photoUrl,
            ],
        ]);
    }

    /**
     * Menghapus foto profil siswa (kembali ke avatar inisial).
     */
    public function hapusFoto(Request $request): JsonResponse
    {
        $student = $this->resolveStudent($request);
        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Data siswa tidak ditemukan.',
            ], 404);
        }

        if ($student->photo_path && Storage::disk('public')->exists($student->photo_path)) {
            Storage::disk('public')->delete($student->photo_path);
        }

        $student->update([
            'photo_path' => null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Foto profil berhasil dihapus.',
            'data' => [
                'photo_path' => null,
                'photo_url' => null,
            ],
        ]);
    }

    /**
     * Memperbarui kata sandi akun login siswa.
     */
    public function updatePassword(Request $request): JsonResponse
    {
        $student = $this->resolveStudent($request);
        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Data siswa tidak ditemukan.',
            ], 404);
        }

        $user = $this->resolveUser($request);
        if (!$user && $student->user_id) {
            $user = User::find($student->user_id);
        }

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Akun pengguna siswa tidak ditemukan.',
            ], 404);
        }

        $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ], [
            'current_password.required' => 'Kata sandi saat ini wajib diisi.',
            'new_password.required' => 'Kata sandi baru wajib diisi.',
            'new_password.min' => 'Kata sandi baru minimal 8 karakter.',
            'new_password.confirmed' => 'Konfirmasi kata sandi baru tidak cocok.',
        ]);

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Kata sandi saat ini yang Anda masukkan salah.',
                'errors' => [
                    'current_password' => ['Kata sandi saat ini tidak cocok.'],
                ],
            ], 422);
        }

        $user->forceFill([
            'password' => Hash::make($request->new_password),
            'must_change_password' => false,
        ])->save();

        return response()->json([
            'success' => true,
            'message' => 'Kata sandi akun berhasil diperbarui!',
        ]);
    }
}

