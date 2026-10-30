<?php

namespace App\Http\Controllers\Api\Mobile\Perpustakaan;

use App\Models\Buku;
use App\Models\EksemplarBuku;
use App\Models\KategoriBuku;
use App\Models\KunjunganPerpustakaan;
use App\Models\Peminjaman;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PerpustakaanKatalogController extends PerpustakaanBaseController
{
    /**
     * GET /api/v1/guru/perpustakaan/summary
     * Ringkasan statistik perpustakaan untuk guru.
     */
    public function summary(Request $request): JsonResponse
    {
        $guru = $this->resolveTeacher($request);
        $guruId = $guru?->id;
        $teacherTypes = $this->getTeacherPeminjamTypes();

        $pinjamanAktifCount = 0;
        $pinjamanTerlambatCount = 0;
        $riwayatPinjamanCount = 0;
        $kunjunganCount = 0;

        if ($guruId) {
            $pinjamanAktifQuery = Peminjaman::whereIn('peminjam_type', $teacherTypes)
                ->where('peminjam_id', $guruId)
                ->where('status', 'dipinjam');

            $pinjamanAktifCount = (clone $pinjamanAktifQuery)->count();

            $today = Carbon::today();
            $pinjamanTerlambatCount = (clone $pinjamanAktifQuery)
                ->whereNotNull('tanggal_jatuh_tempo')
                ->where('tanggal_jatuh_tempo', '<', $today)
                ->count();

            $riwayatPinjamanCount = Peminjaman::whereIn('peminjam_type', $teacherTypes)
                ->where('peminjam_id', $guruId)
                ->where('status', '!=', 'dipinjam')
                ->count();

            $kunjunganCount = KunjunganPerpustakaan::whereIn('pengunjung_type', $teacherTypes)
                ->where('pengunjung_id', $guruId)
                ->count();
        }

        $totalBukuCount = Buku::count();
        $totalEksemplarTersedia = EksemplarBuku::where('status', 'tersedia')->count();

        return $this->sendSuccess([
            'pinjaman_aktif_count' => $pinjamanAktifCount,
            'pinjaman_terlambat_count' => $pinjamanTerlambatCount,
            'riwayat_pinjaman_count' => $riwayatPinjamanCount,
            'kunjungan_count' => $kunjunganCount,
            'total_buku_count' => $totalBukuCount,
            'total_eksemplar_tersedia' => $totalEksemplarTersedia,
        ], 'Ringkasan data perpustakaan berhasil dimuat');
    }

    /**
     * GET /api/v1/guru/perpustakaan/kategori
     * Daftar kategori buku untuk filter katalog.
     */
    public function kategori(Request $request): JsonResponse
    {
        $kategoriList = KategoriBuku::withCount('bukus')
            ->orderBy('nama_kategori', 'asc')
            ->get()
            ->map(function ($kategori) {
                return [
                    'id' => (string) $kategori->id,
                    'nama_kategori' => (string) $kategori->nama_kategori,
                    'kode_prefix' => $kategori->kode_prefix,
                    'is_bisa_dipinjam' => (bool) $kategori->is_bisa_dipinjam,
                    'is_buku_pelajaran' => (bool) $kategori->is_buku_pelajaran,
                    'total_buku' => (int) $kategori->bukus_count,
                ];
            });

        return $this->sendSuccess($kategoriList, 'Daftar kategori buku berhasil dimuat');
    }

    /**
     * GET /api/v1/guru/perpustakaan/katalog
     * Katalog pencarian buku perpustakaan berpaginasi.
     */
    public function katalog(Request $request): JsonResponse
    {
        $guru = $this->resolveTeacher($request);
        $guruId = $guru?->id;
        $teacherTypes = $this->getTeacherPeminjamTypes();

        $search = trim((string) $request->input('search', ''));
        $sortBy = (string) $request->input('sort_by', 'terbaru');
        $kategoriId = $request->input('kategori_id');
        $perPage = max(1, min(50, (int) $request->input('per_page', 15)));

        $query = Buku::query()
            ->with(['kategoriBuku', 'klasifikasiDdc'])
            ->withCount(['eksemplarBukus as eksemplar_tersedia_count' => function ($q) {
                $q->where('status', 'tersedia');
            }])
            ->withCount('eksemplarBukus as total_eksemplar');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('judul', 'like', '%' . $search . '%')
                  ->orWhere('penulis', 'like', '%' . $search . '%')
                  ->orWhere('isbn', 'like', '%' . $search . '%');
            });
        }

        if (!empty($kategoriId)) {
            $query->where('kategori_id', $kategoriId);
        }

        if ($sortBy === 'populer') {
            $query->withCount('peminjamans')->orderBy('peminjamans_count', 'desc');
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $paginator = $query->paginate($perPage);

        // Transform data buku
        $items = collect($paginator->items())->map(function ($buku) use ($guruId, $teacherTypes) {
            $dipinjamOlehSaya = false;
            $earliestReturnDate = null;

            if ($guruId) {
                $peminjamanSaya = Peminjaman::whereHas('eksemplarBuku', function ($q) use ($buku) {
                        $q->where('buku_id', $buku->id);
                    })
                    ->whereIn('peminjam_type', $teacherTypes)
                    ->where('peminjam_id', $guruId)
                    ->where('status', 'dipinjam')
                    ->first();

                if ($peminjamanSaya) {
                    $dipinjamOlehSaya = true;
                    $earliestReturnDate = $peminjamanSaya->tanggal_jatuh_tempo 
                        ? $peminjamanSaya->tanggal_jatuh_tempo->format('d M Y') 
                        : null;
                }
            }

            if (!$dipinjamOlehSaya && (int) $buku->eksemplar_tersedia_count === 0) {
                $minDueDate = Peminjaman::whereHas('eksemplarBuku', function ($q) use ($buku) {
                        $q->where('buku_id', $buku->id);
                    })
                    ->where('status', 'dipinjam')
                    ->whereNotNull('tanggal_jatuh_tempo')
                    ->min('tanggal_jatuh_tempo');

                $earliestReturnDate = $minDueDate ? Carbon::parse($minDueDate)->format('d M Y') : null;
            }

            $sampulUrl = null;
            if (!empty($buku->sampul_buku)) {
                $sampulUrl = filter_var($buku->sampul_buku, FILTER_VALIDATE_URL) 
                    ? $buku->sampul_buku 
                    : url('storage/' . ltrim($buku->sampul_buku, '/'));
            }

            return [
                'id' => (string) $buku->id,
                'judul' => (string) $buku->judul,
                'penulis' => $buku->penulis ?? 'Penulis Tidak Diketahui',
                'penerbit' => $buku->penerbit ?? '-',
                'tahun_terbit' => $buku->tahun_terbit,
                'isbn' => $buku->isbn,
                'lokasi_rak' => $buku->lokasi_rak ?? 'Tidak Ditentukan',
                'sampul_buku' => $buku->sampul_buku,
                'sampul_buku_url' => $sampulUrl,
                'call_number' => $buku->call_number,
                'kategori' => $buku->kategoriBuku ? [
                    'id' => (string) $buku->kategoriBuku->id,
                    'nama_kategori' => $buku->kategoriBuku->nama_kategori,
                ] : null,
                'klasifikasi_ddc' => $buku->klasifikasiDdc ? [
                    'id' => (string) $buku->klasifikasiDdc->id,
                    'kode_ddc' => $buku->klasifikasiDdc->kode_ddc ?? null,
                    'nama_klasifikasi' => $buku->klasifikasiDdc->nama_klasifikasi ?? null,
                ] : null,
                'eksemplar_tersedia_count' => (int) $buku->eksemplar_tersedia_count,
                'total_eksemplar' => (int) $buku->total_eksemplar,
                'dipinjam_oleh_saya' => $dipinjamOlehSaya,
                'earliest_return_date' => $earliestReturnDate,
            ];
        });

        return $this->sendSuccess([
            'items' => $items,
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'has_more_pages' => $paginator->hasMorePages(),
            ],
        ], 'Katalog buku berhasil dimuat');
    }

    /**
     * GET /api/v1/guru/perpustakaan/katalog/{id}
     * Detail buku dan daftar ketersediaan eksemplar.
     */
    public function detail(Request $request, string $id): JsonResponse
    {
        $guru = $this->resolveTeacher($request);
        $guruId = $guru?->id;
        $teacherTypes = $this->getTeacherPeminjamTypes();

        $buku = Buku::with(['kategoriBuku', 'klasifikasiDdc', 'mataPelajaran', 'eksemplarBukus'])
            ->withCount(['eksemplarBukus as eksemplar_tersedia_count' => function ($q) {
                $q->where('status', 'tersedia');
            }])
            ->withCount('eksemplarBukus as total_eksemplar')
            ->find($id);

        if (!$buku) {
            return $this->sendError('Buku tidak ditemukan', 404);
        }

        $dipinjamOlehSaya = false;
        $peminjamanSayaDetail = null;

        if ($guruId) {
            $peminjamanSaya = Peminjaman::with('eksemplarBuku')
                ->whereHas('eksemplarBuku', function ($q) use ($buku) {
                    $q->where('buku_id', $buku->id);
                })
                ->whereIn('peminjam_type', $teacherTypes)
                ->where('peminjam_id', $guruId)
                ->where('status', 'dipinjam')
                ->first();

            if ($peminjamanSaya) {
                $dipinjamOlehSaya = true;
                $peminjamanSayaDetail = [
                    'id' => (string) $peminjamanSaya->id,
                    'kode_eksemplar' => $peminjamanSaya->eksemplarBuku?->kode_eksemplar,
                    'tanggal_pinjam' => $peminjamanSaya->tanggal_pinjam ? $peminjamanSaya->tanggal_pinjam->format('d M Y') : null,
                    'tanggal_jatuh_tempo' => $peminjamanSaya->tanggal_jatuh_tempo ? $peminjamanSaya->tanggal_jatuh_tempo->format('d M Y') : null,
                ];
            }
        }

        $earliestReturnDate = null;
        if ($dipinjamOlehSaya && $peminjamanSayaDetail) {
            $earliestReturnDate = $peminjamanSayaDetail['tanggal_jatuh_tempo'];
        } elseif ((int) $buku->eksemplar_tersedia_count === 0) {
            $minDueDate = Peminjaman::whereHas('eksemplarBuku', function ($q) use ($buku) {
                    $q->where('buku_id', $buku->id);
                })
                ->where('status', 'dipinjam')
                ->whereNotNull('tanggal_jatuh_tempo')
                ->min('tanggal_jatuh_tempo');

            $earliestReturnDate = $minDueDate ? Carbon::parse($minDueDate)->format('d M Y') : null;
        }

        $sampulUrl = null;
        if (!empty($buku->sampul_buku)) {
            $sampulUrl = filter_var($buku->sampul_buku, FILTER_VALIDATE_URL) 
                ? $buku->sampul_buku 
                : url('storage/' . ltrim($buku->sampul_buku, '/'));
        }

        $pdfUrl = null;
        if (!empty($buku->file_pdf)) {
            $pdfUrl = filter_var($buku->file_pdf, FILTER_VALIDATE_URL)
                ? $buku->file_pdf
                : url('storage/' . ltrim($buku->file_pdf, '/'));
        }

        $eksemplarList = $buku->eksemplarBukus->map(function ($eks) {
            return [
                'id' => (string) $eks->id,
                'kode_eksemplar' => (string) $eks->kode_eksemplar,
                'status' => (string) $eks->status,
                'kondisi_fisik' => (string) ($eks->kondisi_fisik ?? 'baik'),
            ];
        });

        return $this->sendSuccess([
            'id' => (string) $buku->id,
            'judul' => (string) $buku->judul,
            'penulis' => $buku->penulis ?? 'Penulis Tidak Diketahui',
            'penerbit' => $buku->penerbit ?? '-',
            'tahun_terbit' => $buku->tahun_terbit,
            'isbn' => $buku->isbn,
            'lokasi_rak' => $buku->lokasi_rak ?? 'Tidak Ditentukan',
            'sampul_buku' => $buku->sampul_buku,
            'sampul_buku_url' => $sampulUrl,
            'has_pdf' => !empty($buku->file_pdf),
            'file_pdf_url' => $pdfUrl,
            'call_number' => $buku->call_number,
            'kategori' => $buku->kategoriBuku ? [
                'id' => (string) $buku->kategoriBuku->id,
                'nama_kategori' => $buku->kategoriBuku->nama_kategori,
            ] : null,
            'klasifikasi_ddc' => $buku->klasifikasiDdc ? [
                'id' => (string) $buku->klasifikasiDdc->id,
                'kode_ddc' => $buku->klasifikasiDdc->kode_ddc ?? null,
                'nama_klasifikasi' => $buku->klasifikasiDdc->nama_klasifikasi ?? null,
            ] : null,
            'mata_pelajaran' => $buku->mataPelajaran ? [
                'id' => (string) $buku->mataPelajaran->id,
                'nama_mapel' => $buku->mataPelajaran->nama_mapel ?? null,
            ] : null,
            'eksemplar_tersedia_count' => (int) $buku->eksemplar_tersedia_count,
            'total_eksemplar' => (int) $buku->total_eksemplar,
            'dipinjam_oleh_saya' => $dipinjamOlehSaya,
            'peminjaman_saya' => $peminjamanSayaDetail,
            'earliest_return_date' => $earliestReturnDate,
            'eksemplar' => $eksemplarList,
        ], 'Detail buku berhasil dimuat');
    }
}

