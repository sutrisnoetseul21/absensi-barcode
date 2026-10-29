<?php

namespace App\Http\Controllers\Api\Mobile\GuruWali;

use App\Enums\StatusKasus;
use App\Enums\StatusPengajuan;
use App\Models\BadgeKarakterSiswa;
use App\Models\JurnalGuruWali;
use App\Models\KonselingBk;
use App\Models\KonsultasiGuruWali;
use App\Models\Siswa;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class GuruWaliJurnalController extends GuruWaliBaseController
{
    /**
     * Mengambil riwayat jurnal bimbingan.
     * GET /api/v1/guru/guru-wali/jurnal
     */
    public function index(Request $request): JsonResponse
    {
        $teacher = $this->resolveTeacher($request);
        if (!$teacher) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $query = JurnalGuruWali::with(['siswa.enrollmentAktif.kelas', 'konselingBk.guru'])
            ->where('teacher_id', $teacher->id);

        if ($request->filled('student_id')) {
            $query->where('student_id', $request->query('student_id'));
        }

        if ($request->filled('kategori')) {
            $query->where('kategori_pendampingan', $request->query('kategori'));
        }

        if ($request->filled('jenis_pendampingan')) {
            $query->where('jenis_pendampingan', $request->query('jenis_pendampingan'));
        }

        if ($request->filled('status_sesi')) {
            $query->where('status_sesi', $request->query('status_sesi'));
        }

        if ($request->filled('bulan')) {
            $bulan = $request->query('bulan'); // 'Y-m'
            try {
                $start = Carbon::createFromFormat('Y-m', $bulan)->startOfMonth();
                $end = Carbon::createFromFormat('Y-m', $bulan)->endOfMonth();
                $query->whereBetween('tanggal_waktu', [$start, $end]);
            } catch (\Throwable $e) {}
        }

        if ($request->filled('search')) {
            $search = trim($request->query('search'));
            $query->where(function ($q) use ($search) {
                $q->where('uraian_pembahasan', 'like', "%{$search}%")
                    ->orWhere('rencana_tindak_lanjut', 'like', "%{$search}%")
                    ->orWhereHas('siswa', function ($sub) use ($search) {
                        $sub->where('name', 'like', "%{$search}%")
                            ->orWhere('nisn', 'like', "%{$search}%");
                    });
            });
        }

        $perPage = (int) $request->query('per_page', 15);
        $paginator = $query->latest('tanggal_waktu')->paginate($perPage);

        $items = collect($paginator->items())->map(function ($jurnal) {
            $siswa = $jurnal->siswa;
            return [
                'id' => $jurnal->id,
                'student_id' => $jurnal->student_id,
                'student_name' => $siswa?->name ?? 'Semua Siswa (Klasikal)',
                'student_nisn' => $siswa?->nisn ?? '-',
                'student_avatar' => $siswa?->avatar_url,
                'kelas_name' => $siswa?->enrollmentAktif?->kelas?->name ?? '-',
                'tanggal_waktu' => Carbon::parse($jurnal->tanggal_waktu)->format('Y-m-d H:i'),
                'tanggal_waktu_formatted' => Carbon::parse($jurnal->tanggal_waktu)->translatedFormat('d M Y, H:i'),
                'jenis_pendampingan' => $jurnal->jenis_pendampingan,
                'kategori_pendampingan' => $jurnal->kategori_pendampingan,
                'uraian_pembahasan' => $jurnal->uraian_pembahasan,
                'status_sesi' => $jurnal->status_sesi,
                'rujukan_kolaborasi' => $jurnal->rujukan_kolaborasi,
                'rencana_tindak_lanjut' => $jurnal->rencana_tindak_lanjut ?? '',
                'kategori_sentimen' => $jurnal->kategori_sentimen ?? 'Netral',
                'is_public_note' => (bool) $jurnal->is_public_note,
                'is_rujuk_bk' => $jurnal->konselingBk !== null,
                'rujuk_bk_id' => $jurnal->konselingBk?->id,
                'rujuk_bk_status' => $jurnal->konselingBk?->status_kasus?->value ?? $jurnal->konselingBk?->status_kasus,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $items,
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ]);
    }

    /**
     * Simpan jurnal bimbingan individual (1 siswa).
     * POST /api/v1/guru/guru-wali/jurnal/individual
     */
    public function storeIndividual(Request $request): JsonResponse
    {
        $teacher = $this->resolveTeacher($request);
        if (!$teacher) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $kelompok = $this->resolveKelompok($teacher);
        if (!$kelompok) {
            return response()->json(['success' => false, 'message' => 'Kelompok Guru Wali aktif tidak ditemukan.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'student_id' => 'required|exists:students,id',
            'tanggal_waktu' => 'required|date',
            'jenis_pendampingan' => 'required|in:Individu,Kelompok Kecil,Klasikal',
            'kategori_pendampingan' => 'required|in:Akademik,Karakter & Kedisiplinan,Minat & Bakat / Ekskul,Sosial & Psikologis',
            'uraian_pembahasan' => 'required|string|min:5',
            'status_sesi' => 'required|in:Tuntas / Selesai,Dalam Pemantauan,Bimbingan Lanjutan',
            'rujukan_kolaborasi' => 'required|in:Mandiri,Wali Kelas,Guru BK,Orang Tua / Wali',
            'rencana_tindak_lanjut' => 'nullable|string',
            'is_public_note' => 'boolean',
            'kategori_sentimen' => 'nullable|in:Positif,Negatif,Netral',
            'give_badge' => 'boolean',
            'nama_badge' => 'required_if:give_badge,true|nullable|string|max:255',
            'catatan_apresiasi' => 'required_if:give_badge,true|nullable|string',
            'konsultasi_id' => 'nullable|exists:konsultasi_guru_wali,id',
        ], [
            'student_id.required' => 'Pilih siswa asuh terlebih dahulu.',
            'uraian_pembahasan.required' => 'Uraian pembahasan bimbingan wajib diisi.',
            'uraian_pembahasan.min' => 'Uraian pembahasan minimal 5 karakter.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        $studentId = $request->input('student_id');

        $isAnggota = $kelompok->anggotaAktif()->where('student_id', $studentId)->exists();
        if (!$isAnggota) {
            return response()->json([
                'success' => false,
                'message' => 'Siswa yang dipilih bukan anggota kelompok binaan aktif Anda.',
            ], 403);
        }

        DB::beginTransaction();
        try {
            $jurnal = JurnalGuruWali::create([
                'teacher_id' => $teacher->id,
                'kelompok_id' => $kelompok->id,
                'student_id' => $studentId,
                'tanggal_waktu' => Carbon::parse($request->input('tanggal_waktu')),
                'jenis_pendampingan' => $request->input('jenis_pendampingan', 'Individu'),
                'kategori_pendampingan' => $request->input('kategori_pendampingan'),
                'uraian_pembahasan' => $request->input('uraian_pembahasan'),
                'status_sesi' => $request->input('status_sesi', 'Tuntas / Selesai'),
                'rujukan_kolaborasi' => $request->input('rujukan_kolaborasi', 'Mandiri'),
                'rencana_tindak_lanjut' => $request->input('rencana_tindak_lanjut') ?? '',
                'is_public_note' => (bool) $request->input('is_public_note', false),
                'kategori_sentimen' => $request->input('kategori_sentimen', 'Netral'),
            ]);

            if ($request->boolean('give_badge')) {
                $siswa = Siswa::with('enrollmentAktif')->find($studentId);
                $activeYear = $this->getActiveAcademicYear();

                BadgeKarakterSiswa::create([
                    'student_id' => $studentId,
                    'teacher_id' => $teacher->id,
                    'academic_year_id' => $activeYear?->id ?? $siswa?->enrollmentAktif?->academic_year_id,
                    'class_id' => $siswa?->enrollmentAktif?->class_id,
                    'nama_badge' => $request->input('nama_badge'),
                    'catatan_apresiasi' => $request->input('catatan_apresiasi'),
                    'source_type' => JurnalGuruWali::class,
                    'source_id' => $jurnal->id,
                ]);
            }

            if ($request->filled('konsultasi_id')) {
                KonsultasiGuruWali::where('id', $request->input('konsultasi_id'))
                    ->where('teacher_id', $teacher->id)
                    ->update([
                        'status_pengajuan' => StatusPengajuan::DikonversiKeJurnal,
                        'jurnal_id' => $jurnal->id,
                    ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Catatan bimbingan individu berhasil disimpan.',
                'data' => [
                    'id' => $jurnal->id,
                ],
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('GuruWaliJurnalController@storeIndividual: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan jurnal bimbingan: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Simpan bimbingan kelompok massal (Klasikal).
     * POST /api/v1/guru/guru-wali/jurnal/klasikal
     */
    public function storeKlasikal(Request $request): JsonResponse
    {
        $teacher = $this->resolveTeacher($request);
        if (!$teacher) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $kelompok = $this->resolveKelompok($teacher);
        if (!$kelompok) {
            return response()->json(['success' => false, 'message' => 'Kelompok Guru Wali aktif tidak ditemukan.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'tanggal_waktu' => 'required|date',
            'jenis_pendampingan' => 'required|in:Kelompok Kecil,Klasikal',
            'kategori_pendampingan' => 'required|in:Akademik,Karakter & Kedisiplinan,Minat & Bakat / Ekskul,Sosial & Psikologis',
            'uraian_pembahasan' => 'required|string|min:5',
            'rujukan_kolaborasi' => 'required|in:Mandiri,Wali Kelas,Guru BK,Orang Tua / Wali',
            'rencana_tindak_lanjut' => 'nullable|string',
            'is_public_note' => 'boolean',
            'student_ids' => 'required|array|min:1',
            'student_ids.*' => 'required|exists:students,id',
            'status_sesi' => 'nullable|in:Tuntas / Selesai,Dalam Pemantauan,Bimbingan Lanjutan',
        ], [
            'student_ids.required' => 'Pilih minimal satu siswa peserta bimbingan.',
            'uraian_pembahasan.required' => 'Uraian pembahasan bimbingan kelompok wajib diisi.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        $studentIds = $request->input('student_ids');
        $tanggalWaktu = Carbon::parse($request->input('tanggal_waktu'));
        $statusSesi = $request->input('status_sesi', 'Tuntas / Selesai');
        $uraian = $request->input('uraian_pembahasan');
        $rtl = $request->input('rencana_tindak_lanjut') ?? '';
        $kategori = $request->input('kategori_pendampingan');
        $jenis = $request->input('jenis_pendampingan');
        $rujukan = $request->input('rujukan_kolaborasi');
        $isPublic = (bool) $request->input('is_public_note', false);

        DB::beginTransaction();
        try {
            $createdCount = 0;
            foreach ($studentIds as $sid) {
                JurnalGuruWali::create([
                    'teacher_id' => $teacher->id,
                    'kelompok_id' => $kelompok->id,
                    'student_id' => $sid,
                    'tanggal_waktu' => $tanggalWaktu,
                    'jenis_pendampingan' => $jenis,
                    'kategori_pendampingan' => $kategori,
                    'uraian_pembahasan' => $uraian,
                    'status_sesi' => $statusSesi,
                    'rujukan_kolaborasi' => $rujukan,
                    'rencana_tindak_lanjut' => $rtl,
                    'is_public_note' => $isPublic,
                    'kategori_sentimen' => 'Netral',
                ]);
                $createdCount++;
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Berhasil mencatat bimbingan klasikal untuk {$createdCount} siswa.",
                'created_count' => $createdCount,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('GuruWaliJurnalController@storeKlasikal: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan jurnal klasikal: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Detail 1 jurnal bimbingan.
     * GET /api/v1/guru/guru-wali/jurnal/{id}
     */
    public function show(Request $request, string $id): JsonResponse
    {
        $teacher = $this->resolveTeacher($request);
        if (!$teacher) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $jurnal = JurnalGuruWali::with(['siswa.enrollmentAktif.kelas', 'konselingBk.guru'])
            ->where('teacher_id', $teacher->id)
            ->find($id);

        if (!$jurnal) {
            return response()->json(['success' => false, 'message' => 'Jurnal bimbingan tidak ditemukan.'], 404);
        }

        $siswa = $jurnal->siswa;

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $jurnal->id,
                'student_id' => $jurnal->student_id,
                'student_name' => $siswa?->name ?? 'Semua Siswa',
                'student_nisn' => $siswa?->nisn ?? '-',
                'student_avatar' => $siswa?->avatar_url,
                'kelas_name' => $siswa?->enrollmentAktif?->kelas?->name ?? '-',
                'tanggal_waktu' => Carbon::parse($jurnal->tanggal_waktu)->format('Y-m-d H:i'),
                'tanggal_waktu_formatted' => Carbon::parse($jurnal->tanggal_waktu)->translatedFormat('l, d F Y - H:i'),
                'jenis_pendampingan' => $jurnal->jenis_pendampingan,
                'kategori_pendampingan' => $jurnal->kategori_pendampingan,
                'uraian_pembahasan' => $jurnal->uraian_pembahasan,
                'status_sesi' => $jurnal->status_sesi,
                'rujukan_kolaborasi' => $jurnal->rujukan_kolaborasi,
                'rencana_tindak_lanjut' => $jurnal->rencana_tindak_lanjut ?? '',
                'kategori_sentimen' => $jurnal->kategori_sentimen ?? 'Netral',
                'is_public_note' => (bool) $jurnal->is_public_note,
                'konseling_bk' => $jurnal->konselingBk ? [
                    'id' => $jurnal->konselingBk->id,
                    'status_kasus' => $jurnal->konselingBk->status_kasus?->value ?? $jurnal->konselingBk->status_kasus,
                    'alasan_rujukan' => $jurnal->konselingBk->alasan_rujukan,
                    'guru_bk_name' => $jurnal->konselingBk->guru?->name ?? 'Tim BK',
                ] : null,
            ],
        ]);
    }

    /**
     * Update catatan jurnal.
     * PUT /api/v1/guru/guru-wali/jurnal/{id}
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $teacher = $this->resolveTeacher($request);
        if (!$teacher) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $jurnal = JurnalGuruWali::where('teacher_id', $teacher->id)->find($id);
        if (!$jurnal) {
            return response()->json(['success' => false, 'message' => 'Jurnal bimbingan tidak ditemukan.'], 404);
        }

        $validator = Validator::make($request->all(), [
            'tanggal_waktu' => 'required|date',
            'kategori_pendampingan' => 'required|in:Akademik,Karakter & Kedisiplinan,Minat & Bakat / Ekskul,Sosial & Psikologis',
            'uraian_pembahasan' => 'required|string|min:5',
            'status_sesi' => 'required|in:Tuntas / Selesai,Dalam Pemantauan,Bimbingan Lanjutan',
            'rujukan_kolaborasi' => 'required|in:Mandiri,Wali Kelas,Guru BK,Orang Tua / Wali',
            'rencana_tindak_lanjut' => 'nullable|string',
            'is_public_note' => 'boolean',
            'kategori_sentimen' => 'nullable|in:Positif,Negatif,Netral',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $jurnal->update([
            'tanggal_waktu' => Carbon::parse($request->input('tanggal_waktu')),
            'kategori_pendampingan' => $request->input('kategori_pendampingan'),
            'uraian_pembahasan' => $request->input('uraian_pembahasan'),
            'status_sesi' => $request->input('status_sesi'),
            'rujukan_kolaborasi' => $request->input('rujukan_kolaborasi'),
            'rencana_tindak_lanjut' => $request->input('rencana_tindak_lanjut') ?? '',
            'is_public_note' => (bool) $request->input('is_public_note', false),
            'kategori_sentimen' => $request->input('kategori_sentimen', 'Netral'),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Jurnal bimbingan berhasil diperbarui.',
        ]);
    }

    /**
     * Hapus catatan jurnal.
     * DELETE /api/v1/guru/guru-wali/jurnal/{id}
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        $teacher = $this->resolveTeacher($request);
        if (!$teacher) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $jurnal = JurnalGuruWali::where('teacher_id', $teacher->id)->find($id);
        if (!$jurnal) {
            return response()->json(['success' => false, 'message' => 'Jurnal bimbingan tidak ditemukan.'], 404);
        }

        $jurnal->delete();

        return response()->json([
            'success' => true,
            'message' => 'Jurnal bimbingan berhasil dihapus.',
        ]);
    }

    /**
     * Eskalasi catatan jurnal ke Guru BK.
     * POST /api/v1/guru/guru-wali/jurnal/{id}/rujuk-bk
     */
    public function rujukBk(Request $request, string $id): JsonResponse
    {
        $teacher = $this->resolveTeacher($request);
        if (!$teacher) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $jurnal = JurnalGuruWali::with(['siswa.enrollmentAktif'])->where('teacher_id', $teacher->id)->find($id);
        if (!$jurnal) {
            return response()->json(['success' => false, 'message' => 'Jurnal bimbingan tidak ditemukan.'], 404);
        }

        $validator = Validator::make($request->all(), [
            'alasan_rujukan' => 'required|string|min:5|max:1000',
        ], [
            'alasan_rujukan.required' => 'Alasan rujukan ke Guru BK wajib dicantumkan.',
            'alasan_rujukan.min' => 'Alasan rujukan minimal 5 karakter.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $activeYear = $this->getActiveAcademicYear();
        $classId = $jurnal->siswa?->enrollmentAktif?->class_id;

        DB::beginTransaction();
        try {
            $konseling = KonselingBk::create([
                'academic_year_id' => $activeYear?->id,
                'class_id' => $classId,
                'teacher_id' => null,
                'student_id' => $jurnal->student_id,
                'jurnal_guru_wali_id' => $jurnal->id,
                'is_rujukan' => true,
                'alasan_rujukan' => $request->input('alasan_rujukan'),
                'tanggal_waktu' => now(),
                'jenis_layanan' => 'Konseling Individu',
                'bidang_bimbingan' => $jurnal->kategori_pendampingan,
                'topik_masalah' => 'Rujukan Kasus dari Guru Wali',
                'uraian_kasus' => $jurnal->uraian_pembahasan,
                'status_kasus' => StatusKasus::DalamPenanganan,
                'is_rahasia' => true,
            ]);

            $jurnal->update([
                'rujukan_kolaborasi' => 'Guru BK',
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Kasus bimbingan berhasil dirujuk ke Guru BK.',
                'data' => [
                    'konseling_bk_id' => $konseling->id,
                ],
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('GuruWaliJurnalController@rujukBk: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal merujuk kasus ke Guru BK: ' . $e->getMessage(),
            ], 500);
        }
    }
}
