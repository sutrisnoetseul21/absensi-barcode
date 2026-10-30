<?php

namespace App\Http\Controllers\Api\Mobile\Perpustakaan;

use App\Models\Peminjaman;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PerpustakaanPeminjamanController extends PerpustakaanBaseController
{
    /**
     * GET /api/v1/guru/perpustakaan/pinjaman-saya
     * Daftar buku yang sedang dipinjam oleh guru.
     */
    public function pinjamanSaya(Request $request): JsonResponse
    {
        $guru = $this->resolveTeacher($request);
        $guruId = $guru?->id;
        $teacherTypes = $this->getTeacherPeminjamTypes();

        if (!$guruId) {
            return $this->sendSuccess([
                'items' => [],
                'pagination' => [
                    'current_page' => 1,
                    'last_page' => 1,
                    'per_page' => 10,
                    'total' => 0,
                    'has_more_pages' => false,
                ],
            ], 'Profil guru tidak ditemukan');
        }

        $perPage = max(1, min(50, (int) $request->input('per_page', 10)));
        $today = Carbon::today();

        $paginator = Peminjaman::with(['eksemplarBuku.buku.kategoriBuku'])
            ->whereIn('peminjam_type', $teacherTypes)
            ->where('peminjam_id', $guruId)
            ->where('status', 'dipinjam')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        $items = collect($paginator->items())->map(function ($pinjam) use ($today) {
            $isTerlambat = $pinjam->tanggal_jatuh_tempo && $today->gt($pinjam->tanggal_jatuh_tempo->copy()->startOfDay());
            $hariTerlambat = $isTerlambat ? $today->diffInDays($pinjam->tanggal_jatuh_tempo->copy()->startOfDay()) : 0;
            $sisaHari = (!$isTerlambat && $pinjam->tanggal_jatuh_tempo) 
                ? $pinjam->tanggal_jatuh_tempo->copy()->startOfDay()->diffInDays($today) 
                : 0;

            $buku = $pinjam->eksemplarBuku?->buku;
            $sampulUrl = null;
            if ($buku && !empty($buku->sampul_buku)) {
                $sampulUrl = filter_var($buku->sampul_buku, FILTER_VALIDATE_URL)
                    ? $buku->sampul_buku
                    : url('storage/' . ltrim($buku->sampul_buku, '/'));
            }

            return [
                'id' => (string) $pinjam->id,
                'eksemplar_id' => (string) $pinjam->eksemplar_id,
                'kode_eksemplar' => $pinjam->eksemplarBuku?->kode_eksemplar ?? '-',
                'buku' => $buku ? [
                    'id' => (string) $buku->id,
                    'judul' => (string) $buku->judul,
                    'penulis' => $buku->penulis ?? 'Penulis Tidak Diketahui',
                    'sampul_buku' => $buku->sampul_buku,
                    'sampul_buku_url' => $sampulUrl,
                    'lokasi_rak' => $buku->lokasi_rak ?? 'Tidak Ditentukan',
                    'kategori_nama' => $buku->kategoriBuku?->nama_kategori ?? '-',
                ] : null,
                'tanggal_pinjam' => $pinjam->tanggal_pinjam ? $pinjam->tanggal_pinjam->format('d M Y') : null,
                'tanggal_jatuh_tempo' => $pinjam->tanggal_jatuh_tempo ? $pinjam->tanggal_jatuh_tempo->format('d M Y') : null,
                'is_terlambat' => $isTerlambat,
                'hari_terlambat' => $hariTerlambat,
                'sisa_hari' => $sisaHari,
                'status' => (string) $pinjam->status,
                'catatan' => $pinjam->catatan,
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
        ], 'Daftar pinjaman aktif berhasil dimuat');
    }

    /**
     * GET /api/v1/guru/perpustakaan/riwayat-pinjaman
     * Riwayat peminjaman buku guru yang sudah selesai.
     */
    public function riwayatPinjaman(Request $request): JsonResponse
    {
        $guru = $this->resolveTeacher($request);
        $guruId = $guru?->id;
        $teacherTypes = $this->getTeacherPeminjamTypes();

        if (!$guruId) {
            return $this->sendSuccess([
                'items' => [],
                'pagination' => [
                    'current_page' => 1,
                    'last_page' => 1,
                    'per_page' => 10,
                    'total' => 0,
                    'has_more_pages' => false,
                ],
            ], 'Profil guru tidak ditemukan');
        }

        $perPage = max(1, min(50, (int) $request->input('per_page', 10)));

        $paginator = Peminjaman::with(['eksemplarBuku.buku.kategoriBuku'])
            ->whereIn('peminjam_type', $teacherTypes)
            ->where('peminjam_id', $guruId)
            ->where('status', '!=', 'dipinjam')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        $items = collect($paginator->items())->map(function ($pinjam) {
            $buku = $pinjam->eksemplarBuku?->buku;
            $sampulUrl = null;
            if ($buku && !empty($buku->sampul_buku)) {
                $sampulUrl = filter_var($buku->sampul_buku, FILTER_VALIDATE_URL)
                    ? $buku->sampul_buku
                    : url('storage/' . ltrim($buku->sampul_buku, '/'));
            }

            return [
                'id' => (string) $pinjam->id,
                'eksemplar_id' => (string) $pinjam->eksemplar_id,
                'kode_eksemplar' => $pinjam->eksemplarBuku?->kode_eksemplar ?? '-',
                'buku' => $buku ? [
                    'id' => (string) $buku->id,
                    'judul' => (string) $buku->judul,
                    'penulis' => $buku->penulis ?? 'Penulis Tidak Diketahui',
                    'sampul_buku' => $buku->sampul_buku,
                    'sampul_buku_url' => $sampulUrl,
                    'lokasi_rak' => $buku->lokasi_rak ?? 'Tidak Ditentukan',
                    'kategori_nama' => $buku->kategoriBuku?->nama_kategori ?? '-',
                ] : null,
                'tanggal_pinjam' => $pinjam->tanggal_pinjam ? $pinjam->tanggal_pinjam->format('d M Y') : null,
                'tanggal_kembali' => $pinjam->tanggal_kembali ? $pinjam->tanggal_kembali->format('d M Y') : null,
                'status' => (string) $pinjam->status,
                'catatan' => $pinjam->catatan,
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
        ], 'Riwayat peminjaman buku berhasil dimuat');
    }
}

