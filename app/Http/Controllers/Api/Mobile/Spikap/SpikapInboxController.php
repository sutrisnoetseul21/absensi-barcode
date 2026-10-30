<?php

namespace App\Http\Controllers\Api\Mobile\Spikap;

use App\Models\SpikapLaporan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SpikapInboxController extends SpikapBaseController
{
    /**
     * GET /api/v1/guru/spikap/check-access
     * Mengecek apakah user berhak mengakses modul SPIKAP (BK / Wali / KS / Admin)
     * beserta rekap cepat statistik tiket aduan.
     */
    public function checkAccess(Request $request): JsonResponse
    {
        $user = $this->resolveUser($request);
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Sesi tidak valid atau identitas guru tidak ditemukan.',
                'data' => [
                    'can_access' => false,
                    'role_label' => 'Tamu',
                ],
            ], 401);
        }

        $access = $this->checkSpikapAccess($user);

        $stats = [
            'total' => 0,
            'darurat' => 0,
            'aktif' => 0,
            'selesai' => 0,
        ];

        if ($access['can_access']) {
            try {
                $baseQuery = $this->getScopedReports($user);
                $stats['total'] = (clone $baseQuery)->count();
                $stats['darurat'] = (clone $baseQuery)->where('sifat_laporan', 'darurat')->count();
                $stats['aktif'] = (clone $baseQuery)->whereIn('status', ['diterima', 'dalam_investigasi'])->count();
                $stats['selesai'] = (clone $baseQuery)->where('status', 'selesai')->count();
            } catch (\Throwable $e) {}
        }

        return response()->json([
            'success' => true,
            'data' => [
                'can_access' => $access['can_access'],
                'role_label' => $access['role_label'],
                'is_bk' => $access['is_bk'],
                'is_wali' => $access['is_wali'],
                'is_ks' => $access['is_ks'],
                'is_admin' => $access['is_admin'],
                'stats' => $stats,
            ],
        ]);
    }

    /**
     * GET /api/v1/guru/spikap/inbox
     * Mengambil daftar tiket laporan aduan SPIKAP terfilter sesuai kewenangan pengguna.
     */
    public function inbox(Request $request): JsonResponse
    {
        $authResult = $this->requireSpikapAccess($request);
        if ($authResult instanceof JsonResponse) {
            return $authResult;
        }
        $user = $authResult;

        $baseQuery = $this->getScopedReports($user);

        // 1. Ringkasan KPI Laporan
        $stats = [
            'total' => (clone $baseQuery)->count(),
            'darurat' => (clone $baseQuery)->where('sifat_laporan', 'darurat')->count(),
            'aktif' => (clone $baseQuery)->whereIn('status', ['diterima', 'dalam_investigasi'])->count(),
            'selesai' => (clone $baseQuery)->where('status', 'selesai')->count(),
        ];

        // 2. Filter Pencarian & Kategori
        $query = (clone $baseQuery)->with(['siswa.enrollmentAktif.kelas', 'lampiran', 'lastHandledBy']);

        // Search live (nama siswa, NISN, uraian, lokasi)
        $search = trim((string) $request->input('search', ''));
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->whereHas('siswa', function ($sq) use ($search) {
                    $sq->where('name', 'like', "%{$search}%")
                       ->orWhere('nisn', 'like', "%{$search}%");
                })->orWhere('uraian_kejadian', 'like', "%{$search}%")
                  ->orWhere('lokasi_kejadian', 'like', "%{$search}%");
            });
        }

        // Filter status (diterima, dalam_investigasi, selesai)
        $filterStatus = $request->input('status');
        if ($filterStatus && in_array($filterStatus, ['diterima', 'dalam_investigasi', 'selesai'])) {
            $query->where('status', $filterStatus);
        }

        // Filter sifat (darurat, biasa)
        $filterSifat = $request->input('sifat');
        if ($filterSifat && in_array($filterSifat, ['darurat', 'biasa'])) {
            $query->where('sifat_laporan', $filterSifat);
        }

        // Filter jenis perundungan (fisik, verbal, sosial, digital, lainnya)
        $filterJenis = $request->input('jenis');
        if ($filterJenis && in_array($filterJenis, ['fisik', 'verbal', 'sosial', 'digital', 'lainnya'])) {
            $query->where('jenis_perundungan', $filterJenis);
        }

        // Urutan: Prioritas Laporan Darurat di atas, lalu laporan terbaru
        $query->orderByRaw("CASE WHEN sifat_laporan = 'darurat' THEN 0 ELSE 1 END")
              ->orderBy('created_at', 'desc');

        $perPage = max(1, min((int) $request->input('per_page', 15), 50));
        $paginator = $query->paginate($perPage);

        $reports = collect($paginator->items())->map(function (SpikapLaporan $lap) {
            $siswa = $lap->siswa;
            $kelas = $siswa?->enrollmentAktif?->kelas;

            $statusLabels = [
                'diterima' => 'Diterima',
                'dalam_investigasi' => 'Dalam Investigasi',
                'selesai' => 'Selesai',
            ];

            $jenisLabels = [
                'fisik' => 'Kekerasan Fisik',
                'verbal' => 'Kekerasan Verbal',
                'sosial' => 'Kekerasan Sosial / Relasional',
                'digital' => 'Perundungan Siber (Digital)',
                'lainnya' => 'Lainnya',
            ];

            return [
                'id' => $lap->id,
                'ticket_number' => '#SPK-' . str_pad($lap->id, 4, '0', STR_PAD_LEFT),
                'student' => [
                    'id' => (string) ($siswa?->id ?? ''),
                    'name' => $siswa?->name ?? 'Siswa Tidak Diketahui',
                    'nisn' => $siswa?->nisn ?? '-',
                    'gender' => $siswa?->gender ?? '-',
                    'class_name' => $kelas?->name ?? '-',
                    'photo_url' => $siswa?->photo_url,
                ],
                'sifat_laporan' => $lap->sifat_laporan,
                'is_darurat' => $lap->sifat_laporan === 'darurat',
                'sifat_label' => $lap->sifat_laporan === 'darurat' ? '🚨 Darurat' : 'Biasa',
                'jenis_perundungan' => $lap->jenis_perundungan,
                'jenis_label' => $jenisLabels[$lap->jenis_perundungan] ?? ucfirst($lap->jenis_perundungan),
                'status' => $lap->status,
                'status_label' => $statusLabels[$lap->status] ?? ucfirst($lap->status),
                'uraian_kejadian' => $lap->uraian_kejadian,
                'uraian_preview' => Str::limit($lap->uraian_kejadian, 110),
                'lokasi_kejadian' => $lap->lokasi_kejadian ?: 'Lingkungan Sekolah',
                'waktu_kejadian' => $lap->waktu_kejadian ? $lap->waktu_kejadian->format('d M Y, H:i') : null,
                'waktu_relatif' => $lap->created_at ? $lap->created_at->diffForHumans() : '-',
                'lampiran_count' => $lap->lampiran->count(),
                'has_lampiran' => $lap->lampiran->isNotEmpty(),
                'last_handled_by_name' => $lap->lastHandledBy?->name,
                'created_at' => $lap->created_at ? $lap->created_at->format('Y-m-d H:i:s') : null,
            ];
        });

        $access = $this->checkSpikapAccess($user);

        return response()->json([
            'success' => true,
            'data' => [
                'reports' => $reports,
                'stats' => $stats,
                'access' => $access,
                'pagination' => [
                    'current_page' => $paginator->currentPage(),
                    'last_page' => $paginator->lastPage(),
                    'per_page' => $paginator->perPage(),
                    'total' => $paginator->total(),
                    'has_more' => $paginator->hasMorePages(),
                ],
            ],
        ]);
    }
}
