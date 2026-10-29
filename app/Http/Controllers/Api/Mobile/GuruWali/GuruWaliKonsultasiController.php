<?php

namespace App\Http\Controllers\Api\Mobile\GuruWali;

use App\Enums\StatusPengajuan;
use App\Models\JurnalGuruWali;
use App\Models\KonsultasiGuruWali;
use App\Models\PesanKonsultasiGuruWali;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class GuruWaliKonsultasiController extends GuruWaliBaseController
{
    /**
     * Mengambil daftar tiket konsultasi / cerita siswa.
     * GET /api/v1/guru/guru-wali/konsultasi
     */
    public function index(Request $request): JsonResponse
    {
        $teacher = $this->resolveTeacher($request);
        if (!$teacher) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $status = $request->query('status', 'semua');
        $query = KonsultasiGuruWali::with(['siswa.enrollmentAktif.kelas', 'jurnal', 'pesan'])
            ->where('teacher_id', $teacher->id);

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

        if ($request->filled('search')) {
            $search = trim($request->query('search'));
            $query->where(function ($q) use ($search) {
                $q->where('topik_konsultasi', 'like', "%{$search}%")
                    ->orWhere('detail_permasalahan', 'like', "%{$search}%")
                    ->orWhereHas('siswa', function ($sub) use ($search) {
                        $sub->where('name', 'like', "%{$search}%")
                            ->orWhere('nisn', 'like', "%{$search}%");
                    });
            });
        }

        $items = $query->latest('created_at')->get()->map(function ($konsul) {
            $siswa = $konsul->siswa;
            $unreadCount = $konsul->pesan->where('sender_type', 'siswa')->where('is_read', false)->count();
            $lastMessage = $konsul->pesan->sortByDesc('created_at')->first();

            return [
                'id' => $konsul->id,
                'student_id' => $konsul->student_id,
                'student_name' => $siswa?->name ?? '-',
                'student_nisn' => $siswa?->nisn ?? '-',
                'student_avatar' => $siswa?->avatar_url,
                'kelas_name' => $siswa?->enrollmentAktif?->kelas?->name ?? '-',
                'kategori_pendampingan' => $konsul->kategori_pendampingan,
                'topik_konsultasi' => $konsul->topik_konsultasi,
                'detail_permasalahan' => $konsul->detail_permasalahan,
                'mode_konsultasi' => $konsul->mode_konsultasi,
                'status_pengajuan' => $konsul->status_pengajuan?->value ?? $konsul->status_pengajuan,
                'usulan_tanggal_waktu' => $konsul->usulan_tanggal_waktu ? Carbon::parse($konsul->usulan_tanggal_waktu)->format('Y-m-d H:i') : null,
                'jadwal_pasti' => $konsul->jadwal_pasti ? Carbon::parse($konsul->jadwal_pasti)->format('Y-m-d H:i') : null,
                'jadwal_pasti_formatted' => $konsul->jadwal_pasti ? Carbon::parse($konsul->jadwal_pasti)->translatedFormat('l, d M Y - H:i') : null,
                'tanggapan_guru' => $konsul->tanggapan_guru,
                'alasan_penolakan' => $konsul->alasan_penolakan,
                'created_at' => Carbon::parse($konsul->created_at)->format('Y-m-d H:i'),
                'created_at_formatted' => Carbon::parse($konsul->created_at)->diffForHumans(),
                'unread_messages_count' => $unreadCount,
                'total_messages_count' => $konsul->pesan->count(),
                'last_message' => $lastMessage ? [
                    'pesan' => $lastMessage->pesan,
                    'sender_type' => $lastMessage->sender_type,
                    'created_at_formatted' => Carbon::parse($lastMessage->created_at)->diffForHumans(),
                ] : null,
                'is_dikonversi' => $konsul->jurnal_id !== null,
                'jurnal_id' => $konsul->jurnal_id,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $items,
        ]);
    }

    /**
     * Detail tiket konsultasi & untaian chat percakapan.
     * GET /api/v1/guru/guru-wali/konsultasi/{id}
     */
    public function show(Request $request, string $id): JsonResponse
    {
        $teacher = $this->resolveTeacher($request);
        if (!$teacher) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $konsul = KonsultasiGuruWali::with(['siswa.enrollmentAktif.kelas', 'jurnal', 'pesan'])
            ->where('teacher_id', $teacher->id)
            ->find($id);

        if (!$konsul) {
            return response()->json(['success' => false, 'message' => 'Tiket konsultasi tidak ditemukan.'], 404);
        }

        // Tandai pesan dari siswa sebagai sudah dibaca
        PesanKonsultasiGuruWali::where('konsultasi_id', $konsul->id)
            ->where('sender_type', 'siswa')
            ->where('is_read', false)
            ->update(['is_read' => true]);

        $siswa = $konsul->siswa;

        $pesanFormatted = $konsul->pesan->sortBy('created_at')->values()->map(function ($p) {
            return [
                'id' => $p->id,
                'sender_type' => $p->sender_type,
                'is_me' => $p->sender_type === 'guru',
                'pesan' => $p->pesan,
                'created_at' => Carbon::parse($p->created_at)->format('Y-m-d H:i:s'),
                'created_at_formatted' => Carbon::parse($p->created_at)->translatedFormat('d M Y, H:i'),
                'is_read' => (bool) $p->is_read,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $konsul->id,
                'student_id' => $konsul->student_id,
                'student_name' => $siswa?->name ?? '-',
                'student_nisn' => $siswa?->nisn ?? '-',
                'student_avatar' => $siswa?->avatar_url,
                'kelas_name' => $siswa?->enrollmentAktif?->kelas?->name ?? '-',
                'kategori_pendampingan' => $konsul->kategori_pendampingan,
                'topik_konsultasi' => $konsul->topik_konsultasi,
                'detail_permasalahan' => $konsul->detail_permasalahan,
                'mode_konsultasi' => $konsul->mode_konsultasi,
                'status_pengajuan' => $konsul->status_pengajuan?->value ?? $konsul->status_pengajuan,
                'usulan_tanggal_waktu' => $konsul->usulan_tanggal_waktu ? Carbon::parse($konsul->usulan_tanggal_waktu)->format('Y-m-d H:i') : null,
                'jadwal_pasti' => $konsul->jadwal_pasti ? Carbon::parse($konsul->jadwal_pasti)->format('Y-m-d H:i') : null,
                'jadwal_pasti_formatted' => $konsul->jadwal_pasti ? Carbon::parse($konsul->jadwal_pasti)->translatedFormat('l, d M Y - H:i') : null,
                'tanggapan_guru' => $konsul->tanggapan_guru,
                'alasan_penolakan' => $konsul->alasan_penolakan,
                'is_dikonversi' => $konsul->jurnal_id !== null,
                'jurnal_id' => $konsul->jurnal_id,
                'messages' => $pesanFormatted,
            ],
        ]);
    }

    /**
     * Kirim pesan obrolan dari Guru Wali ke Siswa.
     * POST /api/v1/guru/guru-wali/konsultasi/{id}/pesan
     */
    public function kirimPesan(Request $request, string $id): JsonResponse
    {
        $teacher = $this->resolveTeacher($request);
        if (!$teacher) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $konsul = KonsultasiGuruWali::where('teacher_id', $teacher->id)->find($id);
        if (!$konsul) {
            return response()->json(['success' => false, 'message' => 'Tiket konsultasi tidak ditemukan.'], 404);
        }

        $validator = Validator::make($request->all(), [
            'pesan' => 'required|string|min:2|max:2000',
        ], [
            'pesan.required' => 'Pesan obrolan tidak boleh kosong.',
            'pesan.min' => 'Pesan obrolan minimal 2 karakter.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $currentStatus = $konsul->status_pengajuan?->value ?? $konsul->status_pengajuan;
        if (in_array($currentStatus, [StatusPengajuan::Selesai->value, StatusPengajuan::Ditolak->value])) {
            return response()->json([
                'success' => false,
                'message' => 'Konsultasi ini telah ditutup atau selesai, tidak dapat mengirim pesan lagi.',
            ], 400);
        }

        $pesanTeks = $request->input('pesan');

        $pesan = PesanKonsultasiGuruWali::create([
            'konsultasi_id' => $konsul->id,
            'sender_type' => 'guru',
            'pesan' => $pesanTeks,
            'is_read' => false,
        ]);

        if ($currentStatus === StatusPengajuan::MenungguKonfirmasi->value) {
            $konsul->update([
                'status_pengajuan' => StatusPengajuan::Dijadwalkan,
                'tanggapan_guru' => $pesanTeks,
            ]);
        } else {
            $konsul->update([
                'tanggapan_guru' => $pesanTeks,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Pesan berhasil terkirim.',
            'data' => [
                'id' => $pesan->id,
                'sender_type' => 'guru',
                'is_me' => true,
                'pesan' => $pesan->pesan,
                'created_at' => Carbon::parse($pesan->created_at)->format('Y-m-d H:i:s'),
                'created_at_formatted' => Carbon::parse($pesan->created_at)->translatedFormat('d M Y, H:i'),
            ],
        ]);
    }

    /**
     * Tetapkan jadwal pasti pertemuan tatap muka.
     * POST /api/v1/guru/guru-wali/konsultasi/{id}/jadwalkan
     */
    public function jadwalkan(Request $request, string $id): JsonResponse
    {
        $teacher = $this->resolveTeacher($request);
        if (!$teacher) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $konsul = KonsultasiGuruWali::where('teacher_id', $teacher->id)->find($id);
        if (!$konsul) {
            return response()->json(['success' => false, 'message' => 'Tiket konsultasi tidak ditemukan.'], 404);
        }

        $validator = Validator::make($request->all(), [
            'jadwal_pasti' => 'required|date',
            'tanggapan_guru' => 'nullable|string|max:1000',
        ], [
            'jadwal_pasti.required' => 'Tentukan jadwal pasti pertemuan.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $konsul->update([
            'status_pengajuan' => StatusPengajuan::Dijadwalkan,
            'jadwal_pasti' => Carbon::parse($request->input('jadwal_pasti')),
            'tanggapan_guru' => $request->input('tanggapan_guru') ?: 'Jadwal tatap muka telah disetujui.',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Jadwal pertemuan tatap muka berhasil ditetapkan.',
        ]);
    }

    /**
     * Balas pesan / selesaikan sesi konsultasi.
     * POST /api/v1/guru/guru-wali/konsultasi/{id}/balas
     */
    public function balas(Request $request, string $id): JsonResponse
    {
        $teacher = $this->resolveTeacher($request);
        if (!$teacher) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $konsul = KonsultasiGuruWali::where('teacher_id', $teacher->id)->find($id);
        if (!$konsul) {
            return response()->json(['success' => false, 'message' => 'Tiket konsultasi tidak ditemukan.'], 404);
        }

        $validator = Validator::make($request->all(), [
            'tanggapan_pesan' => 'required|string|min:3',
            'mark_completed' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $updateData = [
            'tanggapan_guru' => $request->input('tanggapan_pesan'),
        ];

        if ($request->boolean('mark_completed')) {
            $updateData['status_pengajuan'] = StatusPengajuan::Selesai;
        } elseif (($konsul->status_pengajuan?->value ?? $konsul->status_pengajuan) === StatusPengajuan::MenungguKonfirmasi->value) {
            $updateData['status_pengajuan'] = StatusPengajuan::Dijadwalkan;
        }

        $konsul->update($updateData);

        return response()->json([
            'success' => true,
            'message' => 'Tanggapan konsultasi berhasil disimpan.',
        ]);
    }

    /**
     * Tolak permohonan konsultasi siswa.
     * POST /api/v1/guru/guru-wali/konsultasi/{id}/tolak
     */
    public function tolak(Request $request, string $id): JsonResponse
    {
        $teacher = $this->resolveTeacher($request);
        if (!$teacher) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $konsul = KonsultasiGuruWali::where('teacher_id', $teacher->id)->find($id);
        if (!$konsul) {
            return response()->json(['success' => false, 'message' => 'Tiket konsultasi tidak ditemukan.'], 404);
        }

        $validator = Validator::make($request->all(), [
            'alasan_penolakan' => 'required|string|min:3|max:500',
        ], [
            'alasan_penolakan.required' => 'Alasan penolakan konsultasi wajib diisi.',
            'alasan_penolakan.min' => 'Alasan penolakan minimal 3 karakter.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $konsul->update([
            'status_pengajuan' => StatusPengajuan::Ditolak,
            'alasan_penolakan' => $request->input('alasan_penolakan'),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Permohonan konsultasi ditolak.',
        ]);
    }

    /**
     * Konversi tiket konsultasi menjadi jurnal resmi Guru Wali (+1 Klik).
     * POST /api/v1/guru/guru-wali/konsultasi/{id}/konversi-jurnal
     */
    public function konversiJurnal(Request $request, string $id): JsonResponse
    {
        $teacher = $this->resolveTeacher($request);
        if (!$teacher) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $kelompok = $this->resolveKelompok($teacher);
        if (!$kelompok) {
            return response()->json(['success' => false, 'message' => 'Kelompok aktif tidak ditemukan.'], 403);
        }

        $konsul = KonsultasiGuruWali::where('teacher_id', $teacher->id)->find($id);
        if (!$konsul) {
            return response()->json(['success' => false, 'message' => 'Tiket konsultasi tidak ditemukan.'], 404);
        }

        $validator = Validator::make($request->all(), [
            'jenis_pendampingan' => 'required|in:Individu,Kelompok Kecil,Klasikal',
            'status_sesi' => 'required|in:Tuntas / Selesai,Dalam Pemantauan,Bimbingan Lanjutan',
            'rujukan_kolaborasi' => 'required|in:Mandiri,Wali Kelas,Guru BK,Orang Tua / Wali',
            'rencana_tindak_lanjut' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        DB::beginTransaction();
        try {
            $jurnal = JurnalGuruWali::create([
                'teacher_id' => $teacher->id,
                'kelompok_id' => $konsul->kelompok_id,
                'student_id' => $konsul->student_id,
                'tanggal_waktu' => $konsul->jadwal_pasti ?? now(),
                'jenis_pendampingan' => $request->input('jenis_pendampingan', 'Individu'),
                'kategori_pendampingan' => $konsul->kategori_pendampingan,
                'uraian_pembahasan' => $konsul->topik_konsultasi . "\n\n" . $konsul->detail_permasalahan,
                'status_sesi' => $request->input('status_sesi', 'Tuntas / Selesai'),
                'rujukan_kolaborasi' => $request->input('rujukan_kolaborasi', 'Mandiri'),
                'rencana_tindak_lanjut' => $request->input('rencana_tindak_lanjut') ?? '',
                'is_public_note' => false,
                'kategori_sentimen' => 'Netral',
            ]);

            $konsul->update([
                'status_pengajuan' => StatusPengajuan::DikonversiKeJurnal,
                'jurnal_id' => $jurnal->id,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Konsultasi berhasil dikonversi menjadi Jurnal Guru Wali.',
                'data' => [
                    'jurnal_id' => $jurnal->id,
                ],
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('GuruWaliKonsultasiController@konversiJurnal: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengonversi konsultasi: ' . $e->getMessage(),
            ], 500);
        }
    }
}
