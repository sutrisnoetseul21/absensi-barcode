<?php

namespace App\Http\Controllers\Api\Mobile\Siswa;

use App\Models\SpikapLampiran;
use App\Models\SpikapLaporan;
use App\Models\SpikapLogStatus;
use App\Models\SpikapNotifSetting;
use App\Services\SpikapNotificationService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class SiswaSpikapController extends BaseSiswaApiController
{
    /**
     * GET /api/v1/siswa/spikap/dashboard
     * Mengambil ringkasan data SPIKAP siswa, statistik status, branding, dan daftar riwayat aduan terbaru.
     */
    public function dashboard(Request $request): JsonResponse
    {
        $student = $this->resolveStudent($request);
        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Siswa tidak ditemukan atau sesi tidak valid.',
            ], 403);
        }

        $setting = SpikapNotifSetting::instance();

        // Query laporan milik siswa
        $laporanQuery = SpikapLaporan::where('student_id', $student->id);

        $totalCount = (clone $laporanQuery)->count();
        $diterimaCount = (clone $laporanQuery)->where('status', 'diterima')->count();
        $investigasiCount = (clone $laporanQuery)->where('status', 'dalam_investigasi')->count();
        $selesaiCount = (clone $laporanQuery)->where('status', 'selesai')->count();
        $daruratCount = (clone $laporanQuery)->where('sifat_laporan', 'darurat')->count();
        $biasaCount = (clone $laporanQuery)->where('sifat_laporan', 'biasa')->count();

        // 10 laporan terbaru
        $recentLaporan = (clone $laporanQuery)
            ->with(['lampiran', 'logStatus'])
            ->latest()
            ->take(10)
            ->get()
            ->map(function (SpikapLaporan $item) use ($request) {
                return $this->formatLaporanItem($item, $request);
            });

        return response()->json([
            'success' => true,
            'message' => 'Data dashboard SPIKAP siswa berhasil dimuat.',
            'data' => [
                'branding' => [
                    'nama_aplikasi' => $setting->getNamaAplikasi(),
                    'sub_judul' => $setting->getSubJudul(),
                    'penjelasan' => $setting->getPenjelasanAplikasi(),
                ],
                'counts' => [
                    'total' => $totalCount,
                    'diterima' => $diterimaCount,
                    'dalam_investigasi' => $investigasiCount,
                    'selesai' => $selesaiCount,
                    'darurat' => $daruratCount,
                    'biasa' => $biasaCount,
                ],
                'recent_laporan' => $recentLaporan,
            ],
        ]);
    }

    /**
     * GET /api/v1/siswa/spikap/laporan
     * Mengambil daftar laporan siswa dengan opsi filter status, sifat, dan pencarian teks.
     */
    public function index(Request $request): JsonResponse
    {
        $student = $this->resolveStudent($request);
        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Siswa tidak ditemukan atau sesi tidak valid.',
            ], 403);
        }

        $query = SpikapLaporan::where('student_id', $student->id)
            ->with(['lampiran', 'logStatus'])
            ->latest();

        // Filter status
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Filter sifat laporan
        if ($request->filled('sifat_laporan') && $request->sifat_laporan !== 'all') {
            $query->where('sifat_laporan', $request->sifat_laporan);
        }

        // Filter jenis perundungan
        if ($request->filled('jenis_perundungan') && $request->jenis_perundungan !== 'all') {
            $query->where('jenis_perundungan', $request->jenis_perundungan);
        }

        // Pencarian uraian atau lokasi
        if ($request->filled('search')) {
            $keyword = trim($request->search);
            $query->where(function ($q) use ($keyword) {
                $q->where('uraian_kejadian', 'like', "%{$keyword}%")
                  ->orWhere('lokasi_kejadian', 'like', "%{$keyword}%");
            });
        }

        $perPage = max(1, min(50, (int) $request->input('per_page', 15)));
        $paginated = $query->paginate($perPage);

        $items = collect($paginated->items())->map(function (SpikapLaporan $item) use ($request) {
            return $this->formatLaporanItem($item, $request);
        });

        return response()->json([
            'success' => true,
            'message' => 'Daftar laporan SPIKAP berhasil dimuat.',
            'data' => [
                'items' => $items,
                'pagination' => [
                    'current_page' => $paginated->currentPage(),
                    'last_page' => $paginated->lastPage(),
                    'per_page' => $paginated->perPage(),
                    'total' => $paginated->total(),
                ],
            ],
        ]);
    }

    /**
     * GET /api/v1/siswa/spikap/laporan/{id}
     * Mengambil detail laporan kasus, lampiran bukti, serta alur timeline investigasi.
     */
    public function show(Request $request, $id): JsonResponse
    {
        $student = $this->resolveStudent($request);
        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Siswa tidak ditemukan atau sesi tidak valid.',
            ], 403);
        }

        $laporan = SpikapLaporan::where('id', $id)
            ->where('student_id', $student->id)
            ->with(['lampiran', 'logStatus.changedBy', 'lastHandledBy'])
            ->first();

        if (!$laporan) {
            return response()->json([
                'success' => false,
                'message' => 'Laporan SPIKAP tidak ditemukan atau Anda tidak memiliki akses.',
            ], 404);
        }

        $identifierParam = $request->input('identifier') ? '?identifier=' . urlencode($request->input('identifier')) : '';

        // Format lampiran berkas bukti
        $lampirans = $laporan->lampiran->map(function (SpikapLampiran $lamp) use ($identifierParam) {
            return [
                'id' => $lamp->id,
                'tipe_file' => $lamp->tipe_file, // 'foto' atau 'video'
                'nama_asli' => $lamp->nama_asli,
                'ukuran_bytes' => $lamp->ukuran_bytes,
                'ukuran_label' => $lamp->ukuran_readable,
                'url' => url("/api/v1/siswa/spikap/lampiran/{$lamp->id}{$identifierParam}"),
                'download_url' => url("/api/v1/siswa/spikap/lampiran/{$lamp->id}/download{$identifierParam}"),
            ];
        });

        // Format riwayat alur status log (timeline)
        $logs = $laporan->logStatus->sortByDesc('created_at')->values()->map(function (SpikapLogStatus $log) {
            $changedBy = $log->changedBy;
            $namaPetugas = $changedBy ? $changedBy->name : 'Sistem Penanganan SPIKAP';
            $peranPetugas = $changedBy ? 'Guru / Tim TPPK' : 'Sistem Otomatis';

            return [
                'id' => $log->id,
                'status_lama' => $log->status_lama,
                'status_baru' => $log->status_baru,
                'label_status_baru' => match ($log->status_baru) {
                    'diterima' => 'Diterima',
                    'dalam_investigasi' => 'Dalam Investigasi',
                    'selesai' => 'Selesai',
                    default => $log->status_baru,
                },
                'catatan' => $log->catatan ?? '-',
                'petugas' => [
                    'nama' => $namaPetugas,
                    'peran' => $peranPetugas,
                ],
                'created_at' => $log->created_at?->toIso8601String(),
                'created_at_human' => $log->created_at?->diffForHumans(),
                'created_at_formatted' => $log->created_at?->translatedFormat('d M Y, H:i') . ' WIB',
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'Detail laporan SPIKAP berhasil dimuat.',
            'data' => [
                'laporan' => $this->formatLaporanItem($laporan, $request),
                'lampiran' => $lampirans,
                'tracking_logs' => $logs,
            ],
        ]);
    }

    /**
     * POST /api/v1/siswa/spikap/laporan
     * Mengajukan laporan SPIKAP baru dilengkapi berkas foto/video bukti serta auto-notifikasi.
     */
    public function store(Request $request): JsonResponse
    {
        $student = $this->resolveStudent($request);
        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Siswa tidak ditemukan atau sesi tidak valid.',
            ], 403);
        }

        $rules = [
            'sifat_laporan'     => 'required|in:biasa,darurat',
            'jenis_perundungan' => 'required|in:fisik,verbal,sosial,digital,lainnya',
            'uraian_kejadian'   => 'required|string|min:20|max:5000',
            'lokasi_kejadian'   => 'nullable|string|max:255',
            'waktu_kejadian'    => 'nullable|date',
            'foto_bukti'        => 'nullable|array|max:5',
            'foto_bukti.*'      => 'file|mimes:jpg,jpeg,png,webp|max:5120',
            'video_bukti'       => 'nullable|array|max:2',
            'video_bukti.*'     => 'file|mimes:mp4,mov,avi,mkv,webm|max:51200',
        ];

        // Jika sifat biasa, tujuan_penerima wajib
        if ($request->input('sifat_laporan') === 'biasa') {
            $rules['tujuan_penerima'] = 'required|in:guru_bk,wali_kelas';
        } else {
            $rules['tujuan_penerima'] = 'nullable';
        }

        $validator = Validator::make($request->all(), $rules, [
            'jenis_perundungan.required' => 'Pilih jenis perundungan.',
            'uraian_kejadian.required'   => 'Uraian kejadian wajib diisi.',
            'uraian_kejadian.min'        => 'Uraian kejadian minimal 20 karakter agar jelas dan dapat ditindaklanjuti.',
            'tujuan_penerima.required'   => 'Pilih tujuan penerima laporan (Guru BK atau Wali Kelas).',
            'foto_bukti.max'             => 'Maksimal 5 foto bukti.',
            'foto_bukti.*.max'           => 'Ukuran masing-masing foto maksimal 5 MB.',
            'video_bukti.max'            => 'Maksimal 2 video bukti.',
            'video_bukti.*.max'          => 'Ukuran masing-masing video maksimal 50 MB.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        $laporanId = null;

        try {
            DB::transaction(function () use ($request, $student, &$laporanId) {
                // 1. Simpan rekaman laporan utama
                $laporan = SpikapLaporan::create([
                    'student_id'        => $student->id,
                    'sifat_laporan'     => $request->sifat_laporan,
                    'jenis_perundungan' => $request->jenis_perundungan,
                    'uraian_kejadian'   => $request->uraian_kejadian,
                    'lokasi_kejadian'   => $request->lokasi_kejadian ?: null,
                    'waktu_kejadian'    => $request->waktu_kejadian ?: null,
                    'tujuan_penerima'   => $request->sifat_laporan === 'biasa' ? $request->tujuan_penerima : null,
                    'status'            => 'diterima',
                ]);

                // 2. Simpan lampiran foto
                if ($request->hasFile('foto_bukti')) {
                    $fotos = is_array($request->file('foto_bukti')) ? $request->file('foto_bukti') : [$request->file('foto_bukti')];
                    foreach ($fotos as $foto) {
                        if ($foto && $foto->isValid()) {
                            $ext = strtolower($foto->guessExtension() ?: $foto->extension() ?: 'jpg');
                            if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp'], true)) {
                                $ext = 'jpg';
                            }
                            $path = $foto->storeAs(
                                'spikap/' . $laporan->id,
                                'foto_' . uniqid() . '.' . $ext,
                                'local'
                            );
                            SpikapLampiran::create([
                                'laporan_id'   => $laporan->id,
                                'tipe_file'    => 'foto',
                                'path_file'    => $path,
                                'nama_asli'    => strip_tags($foto->getClientOriginalName()),
                                'ukuran_bytes' => $foto->getSize(),
                            ]);
                        }
                    }
                }

                // 3. Simpan lampiran video
                if ($request->hasFile('video_bukti')) {
                    $videos = is_array($request->file('video_bukti')) ? $request->file('video_bukti') : [$request->file('video_bukti')];
                    foreach ($videos as $video) {
                        if ($video && $video->isValid()) {
                            $ext = strtolower($video->guessExtension() ?: $video->extension() ?: 'mp4');
                            if (!in_array($ext, ['mp4', 'mov', 'avi', 'mkv', 'webm'], true)) {
                                $ext = 'mp4';
                            }
                            $path = $video->storeAs(
                                'spikap/' . $laporan->id,
                                'video_' . uniqid() . '.' . $ext,
                                'local'
                            );
                            SpikapLampiran::create([
                                'laporan_id'   => $laporan->id,
                                'tipe_file'    => 'video',
                                'path_file'    => $path,
                                'nama_asli'    => strip_tags($video->getClientOriginalName()),
                                'ukuran_bytes' => $video->getSize(),
                            ]);
                        }
                    }
                }

                // 4. Catat log status awal sistem
                SpikapLogStatus::catatSistem(
                    $laporan->id,
                    'diterima',
                    'Laporan pengaduan diterima melalui Portal Siswa Mobile dan disimpan ke sistem.'
                );

                $laporanId = $laporan->id;
            });

            // 5. Trigger notifikasi WhatsApp melalui SpikapNotificationService
            if ($laporanId) {
                $newLaporan = SpikapLaporan::find($laporanId);
                if ($newLaporan) {
                    try {
                        $notifService = app(SpikapNotificationService::class);
                        if ($newLaporan->sifat_laporan === 'darurat') {
                            $notifService->sendEmergencyNotification($newLaporan);
                        } else {
                            $notifService->sendBiasaNotificationToGuru($newLaporan);
                        }
                    } catch (\Throwable $e) {
                        Log::error("SPIKAP API: Gagal trigger notifikasi untuk laporan #{$laporanId}: " . $e->getMessage());
                    }
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Laporan pengaduan berhasil dikirim dan tersimpan dengan aman.',
                'data' => [
                    'laporan_id' => $laporanId,
                ],
            ], 201);

        } catch (\Throwable $e) {
            Log::error("SPIKAP API: Gagal menyimpan laporan siswa: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem saat menyimpan laporan: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * GET /api/v1/siswa/spikap/lampiran/{id}
     * Menampilkan berkas foto/video bukti secara inline/stream ke aplikasi mobile.
     */
    public function streamLampiran(Request $request, $id): Response
    {
        $student = $this->resolveStudent($request);
        if (!$student) {
            abort(403, 'Sesi tidak valid.');
        }

        $lampiran = SpikapLampiran::with('laporan')->findOrFail($id);

        // Pastikan hanya siswa pemilik laporan yang bisa mengakses berkas
        if ($lampiran->laporan->student_id !== $student->id) {
            abort(403, 'Akses ditolak. Anda tidak memiliki izin untuk berkas ini.');
        }

        if (!Storage::disk('local')->exists($lampiran->path_file)) {
            abort(404, 'Berkas lampiran tidak ditemukan di penyimpanan server.');
        }

        return Storage::disk('local')->response($lampiran->path_file, $lampiran->nama_asli);
    }

    /**
     * GET /api/v1/siswa/spikap/lampiran/{id}/download
     * Mengunduh berkas lampiran bukti.
     */
    public function downloadLampiran(Request $request, $id): Response
    {
        $student = $this->resolveStudent($request);
        if (!$student) {
            abort(403, 'Sesi tidak valid.');
        }

        $lampiran = SpikapLampiran::with('laporan')->findOrFail($id);

        if ($lampiran->laporan->student_id !== $student->id) {
            abort(403, 'Akses ditolak. Anda tidak memiliki izin untuk berkas ini.');
        }

        if (!Storage::disk('local')->exists($lampiran->path_file)) {
            abort(404, 'Berkas lampiran tidak ditemukan di penyimpanan server.');
        }

        return Storage::disk('local')->download($lampiran->path_file, $lampiran->nama_asli);
    }

    /**
     * Helper pemformat item laporan untuk respons JSON.
     */
    private function formatLaporanItem(SpikapLaporan $item, Request $request): array
    {
        $identifierParam = $request->input('identifier') ? '?identifier=' . urlencode($request->input('identifier')) : '';

        return [
            'id' => $item->id,
            'sifat_laporan' => $item->sifat_laporan,
            'is_darurat' => $item->sifat_laporan === 'darurat',
            'label_sifat' => $item->label_sifat,
            'jenis_perundungan' => $item->jenis_perundungan,
            'label_jenis' => $item->label_jenis,
            'tujuan_penerima' => $item->tujuan_penerima,
            'label_tujuan' => match ($item->tujuan_penerima) {
                'guru_bk' => 'Guru BK',
                'wali_kelas' => 'Wali Kelas',
                default => $item->sifat_laporan === 'darurat' ? 'Wali Kelas + Kepala Sekolah' : '-',
            },
            'uraian_kejadian' => $item->uraian_kejadian,
            'lokasi_kejadian' => $item->lokasi_kejadian,
            'waktu_kejadian' => $item->waktu_kejadian?->toIso8601String(),
            'waktu_kejadian_formatted' => $item->waktu_kejadian
                ? $item->waktu_kejadian->translatedFormat('l, d F Y - H:i') . ' WIB'
                : null,
            'status' => $item->status,
            'label_status' => $item->label_status,
            'jumlah_lampiran' => $item->lampiran->count(),
            'lampiran_preview' => $item->lampiran->take(3)->map(function (SpikapLampiran $lamp) use ($identifierParam) {
                return [
                    'id' => $lamp->id,
                    'tipe_file' => $lamp->tipe_file,
                    'nama_asli' => $lamp->nama_asli,
                    'ukuran_label' => $lamp->ukuran_readable,
                    'url' => url("/api/v1/siswa/spikap/lampiran/{$lamp->id}{$identifierParam}"),
                ];
            }),
            'created_at' => $item->created_at?->toIso8601String(),
            'created_at_human' => $item->created_at?->diffForHumans(),
            'created_at_formatted' => $item->created_at
                ? $item->created_at->translatedFormat('d M Y, H:i') . ' WIB'
                : null,
        ];
    }
}

