<?php

namespace App\Http\Controllers\Api\Mobile\Perpustakaan;

use App\Models\KunjunganPerpustakaan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PerpustakaanKunjunganController extends PerpustakaanBaseController
{
    /**
     * GET /api/v1/guru/perpustakaan/kunjungan-saya
     * Riwayat kunjungan fisik guru ke perpustakaan.
     */
    public function kunjunganSaya(Request $request): JsonResponse
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

        $paginator = KunjunganPerpustakaan::whereIn('pengunjung_type', $teacherTypes)
            ->where('pengunjung_id', $guruId)
            ->orderBy('tanggal', 'desc')
            ->orderBy('waktu_masuk', 'desc')
            ->paginate($perPage);

        $items = collect($paginator->items())->map(function ($kunjungan) {
            $waktuMasukStr = '-';
            if ($kunjungan->waktu_masuk) {
                $waktuMasukStr = substr((string) $kunjungan->waktu_masuk, 0, 5);
            }

            $tujuanLabel = $kunjungan->tujuan_kunjungan 
                ? ucwords(str_replace('_', ' ', (string) $kunjungan->tujuan_kunjungan))
                : 'Kunjungan Umum';

            return [
                'id' => (string) $kunjungan->id,
                'tanggal' => $kunjungan->tanggal ? $kunjungan->tanggal->format('d M Y') : '-',
                'tanggal_raw' => $kunjungan->tanggal ? $kunjungan->tanggal->format('Y-m-d') : null,
                'waktu_masuk' => $waktuMasukStr,
                'tujuan_kunjungan' => (string) ($kunjungan->tujuan_kunjungan ?? 'umum'),
                'tujuan_kunjungan_label' => $tujuanLabel,
                'catatan' => $kunjungan->catatan,
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
        ], 'Riwayat kunjungan perpustakaan berhasil dimuat');
    }
}
