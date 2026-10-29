<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Enums\StatusKasus;
use App\Enums\StatusPengajuan;
use App\Http\Controllers\Controller;
use App\Models\BadgeKarakterSiswa;
use App\Models\Guru;
use App\Models\JurnalGuruWali;
use App\Models\KelompokGuruWali;
use App\Models\KelompokGuruWaliSiswa;
use App\Models\KonselingBk;
use App\Models\KonsultasiGuruWali;
use App\Models\PesanKonsultasiGuruWali;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class GuruWaliApiController extends Controller
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

    // =========================================================================
    // 1. DASHBOARD & DATA KELOMPOK (COMMAND CENTER)
    // =========================================================================

    /**
     * Dashboard utama Guru Wali (Info Kelompok, Statistik, dan Grid Kotak Siswa Binaan).
     * GET /api/v1/guru/guru-wali/dashboard
     */
    public function dashboard(Request $request): JsonResponse
    {
        $teacher = $this->resolveTeacher($request);
        if (!$teacher) {
            return response()->json([
                'success' => false,
                'message' => 'Identitas profil Guru tidak ditemukan atau belum terautentikasi.',
            ], 401);
        }

        $kelompok = $this->resolveKelompok($teacher);
        if (!$kelompok) {
            return response()->json([
                'success' => true,
                'has_kelompok' => false,
                'message' => 'Anda belum memiliki tugas penugasan Guru Wali yang aktif (Permendikdasmen No. 11/2025).',
                'teacher' => [
                    'id' => $teacher->id,
                    'name' => $teacher->name,
                    'nip' => $teacher->nip ?? '-',
                ],
                'data' => null,
            ]);
        }

        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();
        $namaBulanIni = Carbon::now()->translatedFormat('F Y');
        $activeYear = $this->getActiveAcademicYear();

        $search = trim((string) $request->query('search', ''));

        // Query Siswa Aktif
        $queryAktif = $kelompok->anggotaAktif()
            ->with(['siswa.enrollmentAktif.kelas'])
            ->whereHas('siswa', function ($q) use ($search) {
                if ($search !== '') {
                    $q->where(function ($sub) use ($search) {
                        $sub->where('name', 'like', "%{$search}%")
                            ->orWhere('nisn', 'like', "%{$search}%");
                    });
                }
            });

        $siswaAktifList = $queryAktif->get();
        $siswaIds = $siswaAktifList->pluck('student_id')->filter()->values()->toArray();

        // Riwayat Jurnal Bulan Berjalan
        $jurnalsBulanIni = JurnalGuruWali::where('teacher_id', $teacher->id)
            ->whereIn('student_id', $siswaIds)
            ->whereBetween('tanggal_waktu', [$startOfMonth, $endOfMonth])
            ->latest('tanggal_waktu')
            ->get()
            ->groupBy('student_id');

        // Pesan Cerita / Konsultasi Aktif Siswa
        $konsultasiAktifMap = KonsultasiGuruWali::where('teacher_id', $teacher->id)
            ->whereIn('student_id', $siswaIds)
            ->whereIn('status_pengajuan', [
                StatusPengajuan::MenungguKonfirmasi->value,
                StatusPengajuan::Dijadwalkan->value,
            ])
            ->latest('created_at')
            ->get()
            ->groupBy('student_id');

        // Unread messages per siswa
        $unreadMessagesCount = PesanKonsultasiGuruWali::whereHas('konsultasi', function ($q) use ($teacher, $siswaIds) {
            $q->where('teacher_id', $teacher->id)->whereIn('student_id', $siswaIds);
        })
            ->where('sender_type', 'siswa')
            ->where('is_read', false)
            ->select('konsultasi_id', DB::raw('count(*) as total'))
            ->groupBy('konsultasi_id')
            ->pluck('total', 'konsultasi_id');

        // Format Siswa Aktif
        $siswaAktifFormatted = $siswaAktifList->map(function ($item) use ($jurnalsBulanIni, $konsultasiAktifMap, $unreadMessagesCount) {
            $siswa = $item->siswa;
            $jurnals = $jurnalsBulanIni->get($item->student_id, collect());
            $lastJurnal = $jurnals->first();

            $activeKonsuls = $konsultasiAktifMap->get($item->student_id, collect());
            $activeKonsul = $activeKonsuls->first();

            $unreadCount = 0;
            if ($activeKonsul) {
                $unreadCount = $unreadMessagesCount->get($activeKonsul->id, 0);
            }

            return [
                'anggota_id' => $item->id,
                'student_id' => $item->student_id,
                'name' => $siswa?->name ?? 'Tanpa Nama',
                'nisn' => $siswa?->nisn ?? '-',
                'gender' => $siswa?->gender ?? 'L',
                'kelas_name' => $siswa?->enrollmentAktif?->kelas?->name ?? 'Belum ada kelas',
                'avatar_url' => $siswa?->avatar_url ?? null,
                'tahun_masuk' => $item->tahun_masuk,
                'has_bimbingan' => $jurnals->isNotEmpty(),
                'bimbingan_count' => $jurnals->count(),
                'last_bimbingan_at' => $lastJurnal?->tanggal_waktu ? Carbon::parse($lastJurnal->tanggal_waktu)->format('Y-m-d H:i') : null,
                'last_bimbingan_date_formatted' => $lastJurnal?->tanggal_waktu ? Carbon::parse($lastJurnal->tanggal_waktu)->translatedFormat('d M Y') : null,
                'last_kategori' => $lastJurnal?->kategori_pendampingan,
                'last_status' => $lastJurnal?->status_sesi,
                'has_active_konsultasi' => $activeKonsul !== null,
                'active_konsultasi_id' => $activeKonsul?->id,
                'active_konsultasi_status' => $activeKonsul?->status_pengajuan?->value ?? $activeKonsul?->status_pengajuan,
                'unread_konsultasi_count' => $unreadCount,
            ];
        });

        // Siswa Arsip (Riwayat)
        $siswaArsipList = $kelompok->anggotaArsip()
            ->with(['siswa.enrollmentAktif.kelas'])
            ->get()
            ->map(function ($item) {
                $siswa = $item->siswa;
                return [
                    'anggota_id' => $item->id,
                    'student_id' => $item->student_id,
                    'name' => $siswa?->name ?? 'Tanpa Nama',
                    'nisn' => $siswa?->nisn ?? '-',
                    'kelas_name' => $siswa?->enrollmentAktif?->kelas?->name ?? 'Belum ada kelas',
                    'tahun_masuk' => $item->tahun_masuk,
                ];
            });

        // Statistik Metrik
        $totalAktif = $kelompok->anggotaAktif()->count();
        $totalArsip = $kelompok->anggotaArsip()->count();
        $totalDidampingiBulanIni = count($jurnalsBulanIni);
        $persenDidampingi = $totalAktif > 0 ? (int) round(($totalDidampingiBulanIni / $totalAktif) * 100) : 0;

        $pendingKonsultasiCount = KonsultasiGuruWali::where('teacher_id', $teacher->id)
            ->where('status_pengajuan', StatusPengajuan::MenungguKonfirmasi->value)
            ->count();

        return response()->json([
            'success' => true,
            'has_kelompok' => true,
            'data' => [
                'teacher' => [
                    'id' => $teacher->id,
                    'name' => $teacher->name,
                    'nip' => $teacher->nip ?? '-',
                    'beban_jp' => '2 JP/Minggu',
                ],
                'kelompok' => [
                    'id' => $kelompok->id,
                    'nama_kelompok' => $kelompok->nama_kelompok,
                    'status_aktif' => (bool) $kelompok->status_aktif,
                ],
                'tahun_ajaran' => [
                    'id' => $activeYear?->id,
                    'name' => $activeYear?->name ?? Carbon::now()->year . '/' . (Carbon::now()->year + 1),
                ],
                'bulan_ini' => $namaBulanIni,
                'statistik' => [
                    'total_siswa_aktif' => $totalAktif,
                    'total_didampingi_bulan_ini' => $totalDidampingiBulanIni,
                    'persen_kepatuhan' => $persenDidampingi,
                    'total_siswa_arsip' => $totalArsip,
                    'pending_konsultasi_count' => $pendingKonsultasiCount,
                ],
                'siswa_aktif' => $siswaAktifFormatted,
                'siswa_arsip' => $siswaArsipList,
            ],
        ]);
    }

    // =========================================================================
    // 2. JURNAL GURU WALI (INDIVIDU & KLASIKAL)
    // =========================================================================

    /**
     * Mengambil riwayat jurnal bimbingan.
     * GET /api/v1/guru/guru-wali/jurnal
     */
    public function jurnalIndex(Request $request): JsonResponse
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
    public function jurnalStoreIndividual(Request $request): JsonResponse
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

        // Pastikan siswa berada dalam kelompok ini
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

            // Jika ada badge karakter yang dianugerahkan
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

            // Jika terkait dengan konversi konsultasi
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
            Log::error('GuruWaliApiController@jurnalStoreIndividual: ' . $e->getMessage());

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
    public function jurnalStoreKlasikal(Request $request): JsonResponse
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
            Log::error('GuruWaliApiController@jurnalStoreKlasikal: ' . $e->getMessage());

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
    public function jurnalShow(Request $request, string $id): JsonResponse
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
    public function jurnalUpdate(Request $request, string $id): JsonResponse
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
    public function jurnalDestroy(Request $request, string $id): JsonResponse
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
    public function jurnalRujukBk(Request $request, string $id): JsonResponse
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
                'teacher_id' => null, // Menunggu penugasan konselor BK
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
            Log::error('GuruWaliApiController@jurnalRujukBk: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal merujuk kasus ke Guru BK: ' . $e->getMessage(),
            ], 500);
        }
    }

    // =========================================================================
    // 3. RUANG AMAN CERITA & KONSULTASI SISWA
    // =========================================================================

    /**
     * Mengambil daftar tiket konsultasi / cerita siswa.
     * GET /api/v1/guru/guru-wali/konsultasi
     */
    public function konsultasiIndex(Request $request): JsonResponse
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
    public function konsultasiShow(Request $request, string $id): JsonResponse
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
    public function konsultasiKirimPesan(Request $request, string $id): JsonResponse
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

        // Jika masih Menunggu Konfirmasi, otomatis tingkatkan ke Dijadwalkan
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
    public function konsultasiJadwalkan(Request $request, string $id): JsonResponse
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
    public function konsultasiBalas(Request $request, string $id): JsonResponse
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
    public function konsultasiTolak(Request $request, string $id): JsonResponse
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
    public function konsultasiKonversiJurnal(Request $request, string $id): JsonResponse
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
            Log::error('GuruWaliApiController@konsultasiKonversiJurnal: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengonversi konsultasi: ' . $e->getMessage(),
            ], 500);
        }
    }

    // =========================================================================
    // 4. PEMANTAUAN BULANAN (MATRIKS 12 BULAN & 4 PILAR)
    // =========================================================================

    /**
     * Mengambil matriks pemantauan bulanan per siswa dan distribusi 4 pilar.
     * GET /api/v1/guru/guru-wali/pemantauan
     */
    public function pemantauanIndex(Request $request): JsonResponse
    {
        $teacher = $this->resolveTeacher($request);
        if (!$teacher) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $kelompok = $this->resolveKelompok($teacher);
        if (!$kelompok) {
            return response()->json(['success' => false, 'message' => 'Kelompok aktif tidak ditemukan.'], 403);
        }

        $activeYear = $this->getActiveAcademicYear();
        $startYear = $activeYear?->start_year ?? Carbon::now()->year;
        $endYear = $activeYear?->end_year ?? ($startYear + 1);

        // Anggota Siswa Aktif
        $anggotaAktif = $kelompok->anggotaAktif()
            ->with(['siswa.enrollmentAktif.kelas'])
            ->get();

        $siswaIds = $anggotaAktif->pluck('student_id')->toArray();

        // Rentang Tahun Ajaran Penuh: 1 Juli startYear s.d. 30 Juni endYear
        $startAcademic = Carbon::createFromDate($startYear, 7, 1)->startOfDay();
        $endAcademic = Carbon::createFromDate($endYear, 6, 30)->endOfDay();

        $jurnals = JurnalGuruWali::where('teacher_id', $teacher->id)
            ->whereIn('student_id', $siswaIds)
            ->whereBetween('tanggal_waktu', [$startAcademic, $endAcademic])
            ->get();

        // 12 Bulan dalam 1 Tahun Ajaran (Juli - Desember startYear, Jan - Juni endYear)
        $bulanKeys = [
            ['key' => "{$startYear}-07", 'label' => 'Jul', 'bulan' => 7, 'tahun' => $startYear],
            ['key' => "{$startYear}-08", 'label' => 'Agu', 'bulan' => 8, 'tahun' => $startYear],
            ['key' => "{$startYear}-09", 'label' => 'Sep', 'bulan' => 9, 'tahun' => $startYear],
            ['key' => "{$startYear}-10", 'label' => 'Okt', 'bulan' => 10, 'tahun' => $startYear],
            ['key' => "{$startYear}-11", 'label' => 'Nov', 'bulan' => 11, 'tahun' => $startYear],
            ['key' => "{$startYear}-12", 'label' => 'Des', 'bulan' => 12, 'tahun' => $startYear],
            ['key' => "{$endYear}-01", 'label' => 'Jan', 'bulan' => 1, 'tahun' => $endYear],
            ['key' => "{$endYear}-02", 'label' => 'Feb', 'bulan' => 2, 'tahun' => $endYear],
            ['key' => "{$endYear}-03", 'label' => 'Mar', 'bulan' => 3, 'tahun' => $endYear],
            ['key' => "{$endYear}-04", 'label' => 'Apr', 'bulan' => 4, 'tahun' => $endYear],
            ['key' => "{$endYear}-05", 'label' => 'Mei', 'bulan' => 5, 'tahun' => $endYear],
            ['key' => "{$endYear}-06", 'label' => 'Jun', 'bulan' => 6, 'tahun' => $endYear],
        ];

        $matriksSiswa = $anggotaAktif->map(function ($item) use ($jurnals, $bulanKeys) {
            $siswa = $item->siswa;
            $jurnalSiswa = $jurnals->where('student_id', $item->student_id);

            $monthlyStatus = [];
            $totalBulanDidampingi = 0;

            foreach ($bulanKeys as $bk) {
                $count = $jurnalSiswa->filter(function ($j) use ($bk) {
                    $dt = Carbon::parse($j->tanggal_waktu);
                    return $dt->year == $bk['tahun'] && $dt->month == $bk['bulan'];
                })->count();

                $isDidampingi = $count > 0;
                if ($isDidampingi) {
                    $totalBulanDidampingi++;
                }

                $monthlyStatus[] = [
                    'key' => $bk['key'],
                    'label' => $bk['label'],
                    'is_didampingi' => $isDidampingi,
                    'count' => $count,
                ];
            }

            return [
                'student_id' => $item->student_id,
                'name' => $siswa?->name ?? '-',
                'nisn' => $siswa?->nisn ?? '-',
                'kelas_name' => $siswa?->enrollmentAktif?->kelas?->name ?? '-',
                'avatar_url' => $siswa?->avatar_url,
                'total_sesi' => $jurnalSiswa->count(),
                'total_bulan_didampingi' => $totalBulanDidampingi,
                'kepatuhan_persen' => (int) round(($totalBulanDidampingi / 12) * 100),
                'monthly_status' => $monthlyStatus,
            ];
        });

        // Distribusi 4 Pilar
        $distribusiPilar = [
            'Akademik' => $jurnals->where('kategori_pendampingan', 'Akademik')->count(),
            'Karakter & Kedisiplinan' => $jurnals->where('kategori_pendampingan', 'Karakter & Kedisiplinan')->count(),
            'Minat & Bakat / Ekskul' => $jurnals->where('kategori_pendampingan', 'Minat & Bakat / Ekskul')->count(),
            'Sosial & Psikologis' => $jurnals->where('kategori_pendampingan', 'Sosial & Psikologis')->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'bulan_headers' => $bulanKeys,
                'matriks_siswa' => $matriksSiswa,
                'distribusi_pilar' => $distribusiPilar,
                'total_seluruh_sesi' => $jurnals->count(),
            ],
        ]);
    }

    // =========================================================================
    // 5. CETAK / UNDUH PDF LAPORAN RESMI
    // =========================================================================

    /**
     * Download / Stream File PDF Cetak Laporan Resmi Guru Wali.
     * GET /api/v1/guru/guru-wali/cetak/pdf
     */
    public function cetakPdf(Request $request)
    {
        $user = $this->resolveUser($request);
        $teacher = $this->resolveTeacher($request);
        if (!$teacher || !$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        // Set auth context untuk GuruWaliCetakController
        if (!auth()->check()) {
            auth()->setUser($user);
        }

        $type = $request->query('type', 'rekap'); // 'rekap' | 'individual'

        try {
            // Forward ke controller cetak resmi
            $cetakController = app(\App\Http\Controllers\GuruWaliCetakController::class);

            if ($type === 'individual') {
                return $cetakController->pdfIndividual($request);
            }

            return $cetakController->pdfKelompok($request);
        } catch (\Throwable $e) {
            Log::error('GuruWaliApiController@cetakPdf: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal menghasilkan PDF: ' . $e->getMessage(),
            ], 500);
        }
    }
}
