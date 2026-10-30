<?php

namespace App\Http\Controllers\Api\Mobile\Spikap;

use App\Models\SpikapLaporan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class SpikapAnalitikController extends SpikapBaseController
{
    /**
     * GET /api/v1/guru/spikap/analitik
     * Mengambil data analitik dan grafik tren kasus SPIKAP.
     * Khusus diperuntukkan bagi Kepala Sekolah dan Guru BK.
     */
    public function analitik(Request $request): JsonResponse
    {
        $authResult = $this->requireSpikapAccess($request);
        if ($authResult instanceof JsonResponse) {
            return $authResult;
        }
        $user = $authResult;

        $access = $this->checkSpikapAccess($user);
        if (!$access['is_ks'] && !$access['is_bk'] && !$access['is_admin']) {
            return response()->json([
                'success' => false,
                'message' => 'Dasbor analitik SPIKAP hanya dapat diakses oleh Kepala Sekolah dan Guru BK.',
            ], 403);
        }

        $query = SpikapLaporan::query();

        // Filter periode (30_hari, 3_bulan, 6_bulan, 1_tahun)
        $periode = $request->input('periode', '1_tahun');
        match ($periode) {
            '30_hari' => $query->where('created_at', '>=', now()->subDays(30)),
            '3_bulan' => $query->where('created_at', '>=', now()->subMonths(3)),
            '6_bulan' => $query->where('created_at', '>=', now()->subMonths(6)),
            '1_tahun' => $query->where('created_at', '>=', now()->subYear()),
            default   => null,
        };

        // 1. Ringkasan Metrik
        $totalLaporan = (clone $query)->count();
        $totalDarurat = (clone $query)->where('sifat_laporan', 'darurat')->count();
        $totalBiasa   = (clone $query)->where('sifat_laporan', 'biasa')->count();
        $totalSelesai = (clone $query)->where('status', 'selesai')->count();
        $totalProses  = (clone $query)->whereIn('status', ['diterima', 'dalam_investigasi'])->count();
        $persenSelesai = $totalLaporan > 0 ? round(($totalSelesai / $totalLaporan) * 100, 1) : 0;

        // 2. Sebaran Kategori Perundungan
        $kategoriList = ['fisik', 'verbal', 'sosial', 'digital', 'lainnya'];
        $kategoriLabels = [
            'fisik' => 'Fisik',
            'verbal' => 'Verbal',
            'sosial' => 'Sosial',
            'digital' => 'Siber / Digital',
            'lainnya' => 'Lainnya',
        ];

        $kategoriStats = [];
        foreach ($kategoriList as $kat) {
            $count = (clone $query)->where('jenis_perundungan', $kat)->count();
            $kategoriStats[] = [
                'key' => $kat,
                'label' => $kategoriLabels[$kat],
                'count' => $count,
                'percentage' => $totalLaporan > 0 ? round(($count / $totalLaporan) * 100, 1) : 0,
            ];
        }

        // 3. Tren Bulanan 6 Bulan Terakhir
        $monthlyTrend = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $yearMonth = $month->format('Y-m');
            $label = $month->translatedFormat('M Y');

            $mTotal = SpikapLaporan::whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->count();
            $mDarurat = SpikapLaporan::whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->where('sifat_laporan', 'darurat')
                ->count();
            $mBiasa = $mTotal - $mDarurat;

            $monthlyTrend[] = [
                'key' => $yearMonth,
                'label' => $label,
                'total' => $mTotal,
                'darurat' => $mDarurat,
                'biasa' => $mBiasa,
            ];
        }

        return response()->json([
            'success' => true,
            'data' => [
                'periode' => $periode,
                'metrics' => [
                    'total' => $totalLaporan,
                    'darurat' => $totalDarurat,
                    'biasa' => $totalBiasa,
                    'selesai' => $totalSelesai,
                    'proses' => $totalProses,
                    'persen_selesai' => $persenSelesai,
                ],
                'kategori_breakdown' => $kategoriStats,
                'monthly_trend' => $monthlyTrend,
            ],
        ]);
    }
}
