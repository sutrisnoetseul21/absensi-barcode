<?php

namespace App\Http\Controllers\Api\Mobile\Siswa;

use App\Enums\StatusPengajuan;
use App\Enums\StudentFeedbackEmoji;
use App\Jobs\SendKonsultasiGuruWaliFcmNotificationJob;
use App\Models\JurnalGuruWali;
use App\Models\KelompokGuruWaliSiswa;
use App\Models\KonsultasiGuruWali;
use App\Models\PesanKonsultasiGuruWali;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SiswaGuruWaliController extends BaseSiswaApiController
{
    /**
     * GET /api/v1/siswa/guru-wali/dashboard
     * Mengambil informasi profil Guru Wali asuh siswa dan statistik counter konsultasi.
     */
    public function dashboard(Request $request): JsonResponse
    {
        $student = $this->resolveStudent($request);
        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Data profil siswa tidak ditemukan.',
            ], 404);
        }

        // Resolusi kelompok aktif siswa
        $keanggotaan = KelompokGuruWaliSiswa::where('student_id', $student->id)
            ->where('status_aktif', true)
            ->with(['kelompok.teacher'])
            ->first();

        $kelompok = $keanggotaan?->kelompok;
        $guruWali = $kelompok?->teacher;

        // Hitung counter status
        $baseKonsul = KonsultasiGuruWali::where('student_id', $student->id);

        $counts = [
            'semua' => (clone $baseKonsul)->count(),
            'menunggu' => (clone $baseKonsul)->where('status_pengajuan', StatusPengajuan::MenungguKonfirmasi->value)->count(),
            'dijadwalkan' => (clone $baseKonsul)->where('status_pengajuan', StatusPengajuan::Dijadwalkan->value)->count(),
            'selesai' => (clone $baseKonsul)->whereIn('status_pengajuan', [
                StatusPengajuan::Selesai->value,
                StatusPengajuan::DikonversiKeJurnal->value,
            ])->count(),
            'ditolak' => (clone $baseKonsul)->where('status_pengajuan', StatusPengajuan::Ditolak->value)->count(),
            'klasikal' => JurnalGuruWali::where('student_id', $student->id)
                ->where('jenis_pendampingan', 'Klasikal')
                ->count(),
            'kelompok_kecil' => JurnalGuruWali::where('student_id', $student->id)
                ->where('jenis_pendampingan', 'Kelompok Kecil')
                ->count(),
            'total_klasikal' => JurnalGuruWali::where('student_id', $student->id)
                ->whereIn('jenis_pendampingan', ['Klasikal', 'Kelompok Kecil'])
                ->count(),
            'catatan_guru' => JurnalGuruWali::where('student_id', $student->id)
                ->where('is_public_note', true)
                ->count(),
        ];

        // Format data profil Guru Wali
        $guruWaliData = null;
        if ($guruWali) {
            $photoUrl = null;
            if (!empty($guruWali->photo_path)) {
                $photoUrl = str_starts_with($guruWali->photo_path, 'http')
                    ? $guruWali->photo_path
                    : asset('storage/' . $guruWali->photo_path);
            }

            $guruWaliData = [
                'id' => $guruWali->id,
                'name' => $guruWali->name,
                'nip' => $guruWali->nip ?? '-',
                'photo_url' => $photoUrl,
                'phone' => $guruWali->phone,
                'nama_kelompok' => $kelompok?->nama_kelompok ?? 'Kelompok Asuh',
                'tahun_ajaran' => $kelompok?->academicYear?->name,
                'tanggal_bergabung' => $keanggotaan?->created_at ? $keanggotaan->created_at->translatedFormat('d M Y') : null,
                'formatted_tanggal_bergabung' => $keanggotaan?->created_at ? 'Mendampingi sejak ' . $keanggotaan->created_at->translatedFormat('d M Y') : null,
            ];
        }

        return response()->json([
            'success' => true,
            'message' => 'Ringkasan SI-WALI siswa berhasil dimuat.',
            'data' => [
                'has_guru_wali' => $guruWaliData !== null,
                'guru_wali' => $guruWaliData,
                'counts' => $counts,
            ],
        ]);
    }

    /**
     * GET /api/v1/siswa/guru-wali/konsultasi
     * Mengambil riwayat konsultasi siswa dengan filter status, kategori, search, dan paginasi.
     */
    public function konsultasi(Request $request): JsonResponse
    {
        $student = $this->resolveStudent($request);
        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Data profil siswa tidak ditemukan.',
            ], 404);
        }

        $query = KonsultasiGuruWali::where('student_id', $student->id)
            ->with(['guru', 'pesan'])
            ->orderBy('created_at', 'desc');

        // Filter Status
        $status = strtolower($request->input('status', 'all'));
        if ($status === 'menunggu') {
            $query->where('status_pengajuan', StatusPengajuan::MenungguKonfirmasi->value);
        } elseif ($status === 'dijadwalkan') {
            $query->where('status_pengajuan', StatusPengajuan::Dijadwalkan->value);
        } elseif ($status === 'selesai') {
            $query->whereIn('status_pengajuan', [
                StatusPengajuan::Selesai->value,
                StatusPengajuan::DikonversiKeJurnal->value,
            ]);
        } elseif ($status === 'ditolak') {
            $query->where('status_pengajuan', StatusPengajuan::Ditolak->value);
        }

        // Filter Kategori (4 Pilar)
        $kategori = $request->input('kategori');
        if (!empty($kategori) && $kategori !== 'all') {
            $query->where('kategori_pendampingan', $kategori);
        }

        // Filter Pencarian Teks
        $search = trim((string) $request->input('search', ''));
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('topik_konsultasi', 'like', "%{$search}%")
                    ->orWhere('detail_permasalahan', 'like', "%{$search}%")
                    ->orWhere('tanggapan_guru', 'like', "%{$search}%");
            });
        }

        $perPage = (int) $request->input('per_page', 10);
        $paginated = $query->paginate($perPage);

        $items = collect($paginated->items())->map(function (KonsultasiGuruWali $item) {
            return $this->formatKonsultasiItem($item);
        });

        return response()->json([
            'success' => true,
            'message' => 'Riwayat konsultasi berhasil dimuat.',
            'data' => [
                'items' => $items,
                'pagination' => [
                    'current_page' => $paginated->currentPage(),
                    'last_page' => $paginated->lastPage(),
                    'per_page' => $paginated->perPage(),
                    'total' => $paginated->total(),
                    'has_more' => $paginated->hasMorePages(),
                ],
            ],
        ]);
    }

    /**
     * POST /api/v1/siswa/guru-wali/konsultasi
     * Siswa mengirimkan permohonan konsultasi / curhat baru ke Guru Wali asuhnya.
     */
    public function store(Request $request): JsonResponse
    {
        $student = $this->resolveStudent($request);
        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Data profil siswa tidak ditemukan.',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'kategori' => 'required|in:Akademik,Karakter & Kedisiplinan,Minat & Bakat / Ekskul,Sosial & Psikologis',
            'mode' => 'required|in:Tatap Muka,Pesan Portal',
            'topik' => 'required|string|min:5|max:255',
            'detail' => 'required|string|min:10|max:5000',
            'usulan_tanggal_waktu' => 'nullable|date',
        ], [
            'kategori.required' => 'Pilih salah satu kategori pendampingan.',
            'kategori.in' => 'Kategori yang dipilih tidak valid.',
            'mode.required' => 'Pilih mode konsultasi (Tatap Muka atau Pesan Portal).',
            'topik.required' => 'Topik konsultasi wajib diisi.',
            'topik.min' => 'Topik konsultasi minimal 5 karakter.',
            'detail.required' => 'Ceritakan kendala atau hal yang ingin dikonsultasikan.',
            'detail.min' => 'Uraian detail permasalahan minimal 10 karakter.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        // Cek keanggotaan aktif siswa
        $keanggotaan = KelompokGuruWaliSiswa::where('student_id', $student->id)
            ->where('status_aktif', true)
            ->with(['kelompok.teacher'])
            ->first();

        if (!$keanggotaan || !$keanggotaan->kelompok || !$keanggotaan->kelompok->teacher) {
            return response()->json([
                'success' => false,
                'message' => 'Anda belum terdaftar dalam kelompok Guru Wali yang aktif. Silakan hubungi pihak sekolah.',
            ], 422);
        }

        $activeYear = $this->getActiveAcademicYear();

        $konsul = KonsultasiGuruWali::create([
            'student_id' => $student->id,
            'teacher_id' => $keanggotaan->kelompok->teacher_id,
            'kelompok_id' => $keanggotaan->kelompok_id,
            'kategori_pendampingan' => $request->input('kategori'),
            'topik_konsultasi' => $request->input('topik'),
            'detail_permasalahan' => $request->input('detail'),
            'mode_konsultasi' => $request->input('mode'),
            'usulan_tanggal_waktu' => $request->input('usulan_tanggal_waktu') ? Carbon::parse($request->input('usulan_tanggal_waktu')) : null,
            'status_pengajuan' => StatusPengajuan::MenungguKonfirmasi->value,
            'academic_year_id' => $activeYear?->id,
            'class_id' => $student->enrollmentAktif?->class_id,
        ]);

        // Pemicu FCM ke HP Guru Wali
        try {
            if (class_exists(SendKonsultasiGuruWaliFcmNotificationJob::class)) {
                SendKonsultasiGuruWaliFcmNotificationJob::dispatch(
                    $konsul->id,
                    'pengajuan_baru'
                );
            }
        } catch (\Throwable $e) {}

        return response()->json([
            'success' => true,
            'message' => 'Permohonan konsultasi berhasil dikirimkan ke Guru Wali Anda!',
            'data' => $this->formatKonsultasiItem($konsul->load(['guru', 'pesan'])),
        ], 201);
    }

    /**
     * GET /api/v1/siswa/guru-wali/konsultasi/{id}
     * Mengambil detail konsultasi, riwayat thread chat, dan status jadwal.
     */
    public function show(Request $request, string $id): JsonResponse
    {
        $student = $this->resolveStudent($request);
        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Data profil siswa tidak ditemukan.',
            ], 404);
        }

        $konsul = KonsultasiGuruWali::where('id', $id)
            ->where('student_id', $student->id)
            ->with(['guru', 'jurnal', 'pesan'])
            ->first();

        if (!$konsul) {
            return response()->json([
                'success' => false,
                'message' => 'Data konsultasi tidak ditemukan.',
            ], 404);
        }

        // Tandai pesan dari guru sebagai terbaca
        try {
            PesanKonsultasiGuruWali::where('konsultasi_id', $id)
                ->where('sender_type', 'guru')
                ->where('is_read', false)
                ->update(['is_read' => true]);
        } catch (\Throwable $e) {}

        return response()->json([
            'success' => true,
            'message' => 'Detail konsultasi berhasil dimuat.',
            'data' => $this->formatKonsultasiDetail($konsul),
        ]);
    }

    /**
     * POST /api/v1/siswa/guru-wali/konsultasi/{id}/pesan
     * Siswa mengirimkan pesan balasan inline dalam thread percakapan konsultasi.
     */
    public function storePesan(Request $request, string $id): JsonResponse
    {
        $student = $this->resolveStudent($request);
        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Data profil siswa tidak ditemukan.',
            ], 404);
        }

        $konsul = KonsultasiGuruWali::where('id', $id)
            ->where('student_id', $student->id)
            ->first();

        if (!$konsul) {
            return response()->json([
                'success' => false,
                'message' => 'Data konsultasi tidak ditemukan.',
            ], 404);
        }

        $statusVal = $konsul->status_pengajuan instanceof StatusPengajuan
            ? $konsul->status_pengajuan->value
            : (string) $konsul->status_pengajuan;

        if (!in_array($statusVal, [StatusPengajuan::MenungguKonfirmasi->value, StatusPengajuan::Dijadwalkan->value])) {
            return response()->json([
                'success' => false,
                'message' => 'Sesi konsultasi ini sudah selesai atau ditutup, tidak dapat mengirim pesan lagi.',
            ], 422);
        }

        $validator = Validator::make($request->all(), [
            'pesan' => 'required|string|min:2|max:2000',
        ], [
            'pesan.required' => 'Pesan tidak boleh kosong.',
            'pesan.min' => 'Pesan minimal 2 karakter.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $pesan = PesanKonsultasiGuruWali::create([
            'konsultasi_id' => $konsul->id,
            'sender_type' => 'siswa',
            'pesan' => $request->input('pesan'),
            'is_read' => false,
        ]);

        // Pemicu FCM ke HP Guru Wali
        try {
            if (class_exists(SendKonsultasiGuruWaliFcmNotificationJob::class)) {
                SendKonsultasiGuruWaliFcmNotificationJob::dispatch(
                    $konsul->id,
                    'pesan_baru',
                    $pesan->id
                );
            }
        } catch (\Throwable $e) {}

        return response()->json([
            'success' => true,
            'message' => 'Pesan berhasil dikirim.',
            'data' => [
                'id' => $pesan->id,
                'sender_type' => 'siswa',
                'is_me' => true,
                'pesan' => $pesan->pesan,
                'is_read' => false,
                'created_at' => $pesan->created_at?->toDateTimeString(),
                'formatted_time' => $pesan->created_at?->translatedFormat('H:i, d M Y'),
            ],
        ], 201);
    }

    /**
     * DELETE /api/v1/siswa/guru-wali/konsultasi/{id}
     * Siswa membatalkan permohonan konsultasi (hanya jika masih berstatus 'Menunggu Konfirmasi').
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        $student = $this->resolveStudent($request);
        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Data profil siswa tidak ditemukan.',
            ], 404);
        }

        $konsul = KonsultasiGuruWali::where('id', $id)
            ->where('student_id', $student->id)
            ->first();

        if (!$konsul) {
            return response()->json([
                'success' => false,
                'message' => 'Data permohonan konsultasi tidak ditemukan.',
            ], 404);
        }

        $statusVal = $konsul->status_pengajuan instanceof StatusPengajuan
            ? $konsul->status_pengajuan->value
            : (string) $konsul->status_pengajuan;

        if ($statusVal !== StatusPengajuan::MenungguKonfirmasi->value) {
            return response()->json([
                'success' => false,
                'message' => 'Konsultasi tidak dapat dibatalkan karena sudah diproses atau dijadwalkan oleh Guru Wali.',
            ], 422);
        }

        $konsul->delete();

        return response()->json([
            'success' => true,
            'message' => 'Permohonan konsultasi berhasil dibatalkan.',
        ]);
    }

    /**
     * POST /api/v1/siswa/guru-wali/konsultasi/{id}/feedback
     * Siswa memberikan ulasan bintang (1-5), emoji perasaan (Lega/Biasa/Bingung), dan catatan kesan.
     */
    public function storeFeedback(Request $request, string $id): JsonResponse
    {
        $student = $this->resolveStudent($request);
        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Data profil siswa tidak ditemukan.',
            ], 404);
        }

        $konsul = KonsultasiGuruWali::where('id', $id)
            ->where('student_id', $student->id)
            ->first();

        if (!$konsul) {
            return response()->json([
                'success' => false,
                'message' => 'Data konsultasi tidak ditemukan.',
            ], 404);
        }

        $statusVal = $konsul->status_pengajuan instanceof StatusPengajuan
            ? $konsul->status_pengajuan->value
            : (string) $konsul->status_pengajuan;

        if (!in_array($statusVal, [StatusPengajuan::Selesai->value, StatusPengajuan::DikonversiKeJurnal->value])) {
            return response()->json([
                'success' => false,
                'message' => 'Feedback hanya dapat diberikan untuk sesi konsultasi yang telah berstatus Selesai.',
            ], 422);
        }

        $validator = Validator::make($request->all(), [
            'rating' => 'required|integer|min:1|max:5',
            'emoji' => 'required|in:Lega,Biasa,Masih Bingung',
            'note' => 'nullable|string|max:1000',
        ], [
            'rating.required' => 'Pilih rating bintang (1-5) untuk sesi bimbingan ini.',
            'emoji.required' => 'Pilih perasaan Anda setelah sesi (Lega, Biasa, Masih Bingung).',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $emojiEnum = StudentFeedbackEmoji::tryFrom($request->input('emoji'));

        $konsul->update([
            'student_feedback_rating' => (int) $request->input('rating'),
            'student_feedback_emoji' => $emojiEnum,
            'student_feedback_note' => $request->input('note'),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Terima kasih atas ulasan Anda! Ini membantu kami mendampingi Anda lebih baik.',
            'data' => [
                'rating' => (int) $request->input('rating'),
                'emoji' => $request->input('emoji'),
                'note' => $request->input('note'),
            ],
        ]);
    }

    /**
     * GET /api/v1/siswa/guru-wali/pendampingan-klasikal
     * Mengambil daftar bimbingan klasikal dan kelompok kecil dari Guru Wali.
     */
    public function pendampinganKlasikal(Request $request): JsonResponse
    {
        $student = $this->resolveStudent($request);
        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Data profil siswa tidak ditemukan.',
            ], 404);
        }

        $query = JurnalGuruWali::where('student_id', $student->id)
            ->with(['guru'])
            ->orderBy('tanggal_waktu', 'desc');

        // Filter Jenis Pendampingan
        $jenis = $request->input('jenis');
        if (!empty($jenis) && $jenis !== 'all') {
            $query->where('jenis_pendampingan', $jenis);
        }

        // Filter Kategori (4 Pilar)
        $kategori = $request->input('kategori');
        if (!empty($kategori) && $kategori !== 'all') {
            $query->where('kategori_pendampingan', $kategori);
        }

        // Filter Pencarian Teks
        $search = trim((string) $request->input('search', ''));
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('uraian_pembahasan', 'like', "%{$search}%")
                    ->orWhere('rencana_tindak_lanjut', 'like', "%{$search}%");
            });
        }

        $perPage = (int) $request->input('per_page', 10);
        $paginated = $query->paginate($perPage);

        $items = collect($paginated->items())->map(function (JurnalGuruWali $jurnal) {
            return [
                'id' => $jurnal->id,
                'jenis_pendampingan' => $jurnal->jenis_pendampingan, // 'Klasikal' | 'Kelompok Kecil' | 'Individual'
                'kategori_pendampingan' => $jurnal->kategori_pendampingan,
                'tanggal_waktu' => $jurnal->tanggal_waktu?->toDateTimeString(),
                'formatted_date' => $jurnal->tanggal_waktu?->translatedFormat('l, d F Y'),
                'formatted_time' => $jurnal->tanggal_waktu?->translatedFormat('H:i') . ' WIB',
                'uraian_pembahasan' => $jurnal->uraian_pembahasan,
                'rencana_tindak_lanjut' => $jurnal->rencana_tindak_lanjut,
                'status_sesi' => $jurnal->status_sesi ?? 'Tuntas / Selesai',
                'guru_name' => $jurnal->guru?->name ?? 'Guru Wali',
                'is_public_note' => (bool) $jurnal->is_public_note,
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'Daftar pendampingan klasikal & kelompok berhasil dimuat.',
            'data' => [
                'items' => $items,
                'pagination' => [
                    'current_page' => $paginated->currentPage(),
                    'last_page' => $paginated->lastPage(),
                    'per_page' => $paginated->perPage(),
                    'total' => $paginated->total(),
                    'has_more' => $paginated->hasMorePages(),
                ],
            ],
        ]);
    }

    /**
     * Helper Format Item Konsultasi
     */
    private function formatKonsultasiItem(KonsultasiGuruWali $item): array
    {
        $statusVal = $item->status_pengajuan instanceof StatusPengajuan
            ? $item->status_pengajuan->value
            : (string) $item->status_pengajuan;

        $unreadCount = $item->pesan->where('sender_type', 'guru')->where('is_read', false)->count();

        $emojiVal = $item->student_feedback_emoji instanceof StudentFeedbackEmoji
            ? $item->student_feedback_emoji->value
            : ($item->student_feedback_emoji ? (string) $item->student_feedback_emoji : null);

        return [
            'id' => $item->id,
            'kategori' => $item->kategori_pendampingan,
            'topik' => $item->topik_konsultasi,
            'detail' => $item->detail_permasalahan,
            'mode' => $item->mode_konsultasi,
            'status' => $statusVal,
            'status_label' => $statusVal,
            'status_key' => match ($statusVal) {
                StatusPengajuan::MenungguKonfirmasi->value => 'menunggu',
                StatusPengajuan::Dijadwalkan->value => 'dijadwalkan',
                StatusPengajuan::Selesai->value, StatusPengajuan::DikonversiKeJurnal->value => 'selesai',
                StatusPengajuan::Ditolak->value => 'ditolak',
                default => 'lainnya',
            },
            'is_cancellable' => $statusVal === StatusPengajuan::MenungguKonfirmasi->value,
            'can_chat' => in_array($statusVal, [StatusPengajuan::MenungguKonfirmasi->value, StatusPengajuan::Dijadwalkan->value]),
            'can_feedback' => in_array($statusVal, [StatusPengajuan::Selesai->value, StatusPengajuan::DikonversiKeJurnal->value]),
            'unread_messages_count' => $unreadCount,
            'guru_name' => $item->guru?->name ?? 'Guru Wali',
            'usulan_waktu' => $item->usulan_tanggal_waktu?->toDateTimeString(),
            'formatted_usulan_waktu' => $item->usulan_tanggal_waktu?->translatedFormat('d M Y, H:i'),
            'jadwal_pasti' => $item->jadwal_pasti?->toDateTimeString(),
            'formatted_jadwal_pasti' => $item->jadwal_pasti ? $item->jadwal_pasti->translatedFormat('l, d F Y, H:i') . ' WIB' : null,
            'tanggapan_guru' => $item->tanggapan_guru,
            'alasan_penolakan' => $item->alasan_penolakan,
            'feedback_rating' => $item->student_feedback_rating,
            'feedback_emoji' => $emojiVal,
            'feedback_note' => $item->student_feedback_note,
            'created_at' => $item->created_at?->toDateTimeString(),
            'formatted_created_at' => $item->created_at?->translatedFormat('d M Y, H:i'),
        ];
    }

    /**
     * Helper Format Detail Konsultasi Lengkap dengan Percakapan Pesan
     */
    private function formatKonsultasiDetail(KonsultasiGuruWali $item): array
    {
        $base = $this->formatKonsultasiItem($item);

        $pesanList = $item->pesan->map(function (PesanKonsultasiGuruWali $p) {
            $isMe = $p->sender_type === 'siswa';
            return [
                'id' => $p->id,
                'sender_type' => $p->sender_type,
                'is_me' => $isMe,
                'pesan' => $p->pesan,
                'is_read' => (bool) $p->is_read,
                'created_at' => $p->created_at?->toDateTimeString(),
                'formatted_time' => $p->created_at?->translatedFormat('H:i'),
                'formatted_date' => $p->created_at?->translatedFormat('d M Y'),
            ];
        })->values()->all();

        $base['messages'] = $pesanList;

        return $base;
    }
}
