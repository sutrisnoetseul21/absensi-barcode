<?php

namespace App\Http\Controllers\Api\Mobile\Siswa;

use App\Models\KunjunganPerpustakaan;
use App\Models\Peminjaman;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SiswaPerpustakaanController extends BaseSiswaApiController
{
    /**
     * GET /api/v1/siswa/perpustakaan/summary
     * Mengambil ringkasan sirkulasi perpustakaan siswa (jumlah pinjaman aktif, riwayat, dan kunjungan).
     */
    public function summary(Request $request): JsonResponse
    {
        $student = $this->resolveStudent($request);
        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Siswa tidak ditemukan atau sesi tidak valid.',
            ], 403);
        }

        $studentId = $student->id;
        $today = Carbon::today();

        // 1. Pinjaman Aktif
        $pinjamanAktifQuery = Peminjaman::where('peminjam_type', 'siswa')
            ->where('peminjam_id', $studentId)
            ->where('status', 'dipinjam');

        $pinjamanAktifCount = (clone $pinjamanAktifQuery)->count();

        // 2. Pinjaman Terlambat (lewat tanggal jatuh tempo)
        $terlambatCount = (clone $pinjamanAktifQuery)
            ->whereNotNull('tanggal_jatuh_tempo')
            ->where('tanggal_jatuh_tempo', '<', $today)
            ->count();

        // 3. Total Riwayat Pengembalian
        $riwayatCount = Peminjaman::where('peminjam_type', 'siswa')
            ->where('peminjam_id', $studentId)
            ->where('status', '!=', 'dipinjam')
            ->count();

        // 4. Total Kunjungan Fisik
        $kunjunganCount = KunjunganPerpustakaan::where('pengunjung_type', 'siswa')
            ->where('pengunjung_id', $studentId)
            ->count();

        // 5. Preview Buku Pinjaman Aktif Terbaru (maksimal 3)
        $recentPinjaman = (clone $pinjamanAktifQuery)
            ->with(['eksemplarBuku.buku.kategoriBuku'])
            ->orderBy('tanggal_pinjam', 'desc')
            ->take(3)
            ->get()
            ->map(function (Peminjaman $p) {
                return $this->formatPinjamanItem($p);
            });

        return response()->json([
            'success' => true,
            'message' => 'Ringkasan perpustakaan siswa berhasil dimuat.',
            'data' => [
                'student' => [
                    'id' => $student->id,
                    'name' => $student->name,
                    'nisn' => $student->nisn ?? $student->nis ?? '-',
                    'kelas' => $student->enrollmentAktif?->kelas?->name ?? '-',
                ],
                'counts' => [
                    'pinjaman_aktif' => $pinjamanAktifCount,
                    'pinjaman_terlambat' => $terlambatCount,
                    'total_riwayat_pinjam' => $riwayatCount,
                    'total_kunjungan' => $kunjunganCount,
                ],
                'recent_pinjaman' => $recentPinjaman,
            ],
        ]);
    }

    /**
     * GET /api/v1/siswa/perpustakaan/pinjaman-aktif
     * Mengambil seluruh daftar buku perpustakaan yang sedang dipinjam siswa.
     */
    public function pinjamanAktif(Request $request): JsonResponse
    {
        $student = $this->resolveStudent($request);
        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Siswa tidak ditemukan atau sesi tidak valid.',
            ], 403);
        }

        $items = Peminjaman::where('peminjam_type', 'siswa')
            ->where('peminjam_id', $student->id)
            ->where('status', 'dipinjam')
            ->with(['eksemplarBuku.buku.kategoriBuku'])
            ->orderBy('tanggal_pinjam', 'desc')
            ->get()
            ->map(function (Peminjaman $p) {
                return $this->formatPinjamanItem($p);
            });

        return response()->json([
            'success' => true,
            'message' => 'Daftar buku yang sedang dipinjam berhasil dimuat.',
            'data' => [
                'total' => $items->count(),
                'items' => $items,
            ],
        ]);
    }

    /**
     * GET /api/v1/siswa/perpustakaan/riwayat-pinjam
     * Mengambil daftar histori buku yang pernah dipinjam siswa dan sudah dikembalikan.
     */
    public function riwayatPinjam(Request $request): JsonResponse
    {
        $student = $this->resolveStudent($request);
        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Siswa tidak ditemukan atau sesi tidak valid.',
            ], 403);
        }

        $query = Peminjaman::where('peminjam_type', 'siswa')
            ->where('peminjam_id', $student->id)
            ->where('status', '!=', 'dipinjam')
            ->with(['eksemplarBuku.buku.kategoriBuku'])
            ->orderBy('tanggal_kembali', 'desc')
            ->orderBy('updated_at', 'desc');

        if ($request->filled('search')) {
            $keyword = trim($request->search);
            $query->whereHas('eksemplarBuku.buku', function ($q) use ($keyword) {
                $q->where('judul', 'like', "%{$keyword}%")
                  ->orWhere('penulis', 'like', "%{$keyword}%");
            });
        }

        $perPage = max(1, min(50, (int) $request->input('per_page', 15)));
        $paginated = $query->paginate($perPage);

        $items = collect($paginated->items())->map(function (Peminjaman $p) {
            return $this->formatPinjamanItem($p);
        });

        return response()->json([
            'success' => true,
            'message' => 'Riwayat peminjaman buku berhasil dimuat.',
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
     * GET /api/v1/siswa/perpustakaan/riwayat-kunjungan
     * Mengambil riwayat absensi fisik siswa di ruang perpustakaan sekolah.
     */
    public function riwayatKunjungan(Request $request): JsonResponse
    {
        $student = $this->resolveStudent($request);
        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Siswa tidak ditemukan atau sesi tidak valid.',
            ], 403);
        }

        $query = KunjunganPerpustakaan::where('pengunjung_type', 'siswa')
            ->where('pengunjung_id', $student->id)
            ->orderBy('tanggal', 'desc')
            ->orderBy('waktu_masuk', 'desc');

        $perPage = max(1, min(50, (int) $request->input('per_page', 15)));
        $paginated = $query->paginate($perPage);

        $items = collect($paginated->items())->map(function (KunjunganPerpustakaan $k) {
            $waktuMasuk = $k->waktu_masuk ? substr((string) $k->waktu_masuk, 0, 5) : '-';
            return [
                'id' => $k->id,
                'tanggal' => $k->tanggal?->format('Y-m-d'),
                'tanggal_formatted' => $k->tanggal ? $k->tanggal->translatedFormat('d M Y') : '-',
                'waktu_masuk' => $waktuMasuk,
                'waktu_masuk_formatted' => $waktuMasuk !== '-' ? $waktuMasuk . ' WIB' : '-',
                'tujuan_kunjungan' => $k->tujuan_kunjungan,
                'label_tujuan' => ucwords(str_replace('_', ' ', (string) $k->tujuan_kunjungan)),
                'catatan' => $k->catatan,
                'created_at' => $k->created_at?->toIso8601String(),
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'Riwayat kunjungan fisik perpustakaan berhasil dimuat.',
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
     * Helper pemformat item peminjaman untuk respons JSON.
     */
    private function formatPinjamanItem(Peminjaman $p): array
    {
        $eksemplar = $p->eksemplarBuku;
        $buku = $eksemplar?->buku;

        $coverUrl = null;
        if ($buku && $buku->sampul_buku) {
            $coverUrl = asset('storage/' . $buku->sampul_buku);
        }

        $today = Carbon::today();
        $isTerlambat = false;
        $terlambatHari = 0;
        $sisaHari = null;

        if ($p->status === 'dipinjam' && $p->tanggal_jatuh_tempo) {
            $jatuhTempo = Carbon::parse($p->tanggal_jatuh_tempo)->startOfDay();
            if ($today->gt($jatuhTempo)) {
                $isTerlambat = true;
                $terlambatHari = (int) $today->diffInDays($jatuhTempo);
            } else {
                $sisaHari = (int) $today->diffInDays($jatuhTempo, false);
            }
        }

        return [
            'id' => $p->id,
            'kode_eksemplar' => $eksemplar?->kode_eksemplar ?? '-',
            'nomor_barcode' => $eksemplar?->nomor_barcode ?? '-',
            'buku' => [
                'id' => $buku?->id,
                'judul' => $buku?->judul ?? 'Judul Tidak Diketahui',
                'penulis' => $buku?->penulis ?? 'Penulis Tidak Diketahui',
                'penerbit' => $buku?->penerbit ?? '-',
                'isbn' => $buku?->isbn ?? '-',
                'kategori' => $buku?->kategoriBuku?->nama_kategori ?? 'Umum',
                'lokasi_rak' => $buku?->lokasi_rak ?? '-',
                'sampul_url' => $coverUrl,
            ],
            'tanggal_pinjam' => $p->tanggal_pinjam?->format('Y-m-d'),
            'tanggal_pinjam_formatted' => $p->tanggal_pinjam ? $p->tanggal_pinjam->translatedFormat('d M Y') : '-',
            'tanggal_jatuh_tempo' => $p->tanggal_jatuh_tempo?->format('Y-m-d'),
            'tanggal_jatuh_tempo_formatted' => $p->tanggal_jatuh_tempo ? $p->tanggal_jatuh_tempo->translatedFormat('d M Y') : '-',
            'tanggal_kembali' => $p->tanggal_kembali?->format('Y-m-d'),
            'tanggal_kembali_formatted' => $p->tanggal_kembali ? $p->tanggal_kembali->translatedFormat('d M Y') : '-',
            'status' => $p->status,
            'label_status' => match ($p->status) {
                'dipinjam' => $isTerlambat ? 'Terlambat' : 'Sedang Dipinjam',
                'kembali' => 'Sudah Dikembalikan',
                'hilang' => 'Hilang',
                'rusak' => 'Rusak',
                default => ucfirst((string) $p->status),
            },
            'is_terlambat' => $isTerlambat,
            'terlambat_hari' => $terlambatHari,
            'sisa_hari' => $sisaHari,
            'catatan' => $p->catatan,
            'created_at' => $p->created_at?->toIso8601String(),
        ];
    }
}

