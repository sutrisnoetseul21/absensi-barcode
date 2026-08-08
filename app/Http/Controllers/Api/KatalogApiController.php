<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Buku;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\JsonResponse;

class KatalogApiController extends Controller
{
    /**
     * Mengembalikan data ringkas seluruh katalog buku ter-cache untuk client-side JS search & pagination.
     */
    public function getCatalogJson(): JsonResponse
    {
        $cacheKey = 'katalog_buku_js_cache_v1';
        $etag = md5(Cache::get('katalog_buku_version', '1.0'));

        $catalog = Cache::remember($cacheKey, 1800, function () {
            return Buku::query()
                ->with(['kategoriBuku:id,nama_kategori'])
                ->withCount(['eksemplarBukus as eksemplar_tersedia_count' => function ($q) {
                    $q->where('status', 'tersedia');
                }])
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($buku) {
                    return [
                        'id' => $buku->id,
                        'judul' => $buku->judul,
                        'penulis' => $buku->penulis ?? 'Penulis Tidak Diketahui',
                        'isbn' => $buku->isbn ?? '',
                        'kategori_id' => $buku->kategori_id,
                        'kategori_nama' => $buku->kategoriBuku?->nama_kategori ?? '',
                        'grade_level' => $buku->grade_level,
                        'sampul_buku' => $buku->sampul_buku ? asset('storage/' . $buku->sampul_buku) : null,
                        'eksemplar_tersedia_count' => (int) $buku->eksemplar_tersedia_count,
                        'lokasi_rak' => $buku->lokasi_rak ?? 'Tidak ditentukan',
                        'file_pdf' => $buku->file_pdf ? asset('storage/' . $buku->file_pdf) : null,
                    ];
                });
        });

        return response()->json([
            'status' => 'success',
            'total' => $catalog->count(),
            'version' => $etag,
            'data' => $catalog,
        ])->header('Cache-Control', 'public, max-age=1800')
          ->header('ETag', '"' . $etag . '"');
    }
}
