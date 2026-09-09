<?php

namespace App\Livewire\PortalGuru;

use App\Models\SpikapLaporan;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class SpikapAnalitik extends Component
{
    public string $periode = '1_tahun';
    public string $sifatFilter = '';

    public function mount(): void
    {
        $user = Auth::user();
        if (!$user) {
            abort(401);
        }

        $allowed = $user->hasAnyRole(['spikap_kepala_sekolah', 'spikap_guru_bk', 'super_admin', 'spikap_admin'])
            || $user->can('spikap.view_analytics')
            || $user->isGuruBk()
            || $user->isKepalaSekolah();

        if (!$allowed) {
            abort(403, 'Akses ditolak. Dashboard analitik hanya dapat diakses oleh Kepala Sekolah dan Guru BK.');
        }
    }

    public function updatedPeriode(): void
    {
        $this->dispatch('analitik-data-updated', $this->getChartData());
    }

    public function updatedSifatFilter(): void
    {
        $this->dispatch('analitik-data-updated', $this->getChartData());
    }

    /**
     * Scope query berdasarkan periode dan filter sifat laporan.
     */
    protected function getFilteredQuery()
    {
        $query = SpikapLaporan::query();

        if ($this->sifatFilter) {
            $query->where('sifat_laporan', $this->sifatFilter);
        }

        match ($this->periode) {
            '30_hari' => $query->where('created_at', '>=', now()->subDays(30)),
            '3_bulan' => $query->where('created_at', '>=', now()->subMonths(3)),
            '6_bulan' => $query->where('created_at', '>=', now()->subMonths(6)),
            '1_tahun' => $query->where('created_at', '>=', now()->subYear()),
            default   => null,
        };

        return $query;
    }

    /**
     * Hitung metrik ringkasan.
     */
    protected function getMetrics(): array
    {
        $query = $this->getFilteredQuery();

        $totalLaporan = (clone $query)->count();
        $totalDarurat = (clone $query)->where('sifat_laporan', 'darurat')->count();
        $totalBiasa   = (clone $query)->where('sifat_laporan', 'biasa')->count();
        $totalSelesai = (clone $query)->where('status', 'selesai')->count();
        $totalProses  = (clone $query)->whereIn('status', ['diterima', 'dalam_investigasi'])->count();

        $persenSelesai = $totalLaporan > 0 ? round(($totalSelesai / $totalLaporan) * 100, 1) : 0;

        return [
            'total'          => $totalLaporan,
            'darurat'        => $totalDarurat,
            'biasa'          => $totalBiasa,
            'selesai'        => $totalSelesai,
            'proses'         => $totalProses,
            'persen_selesai' => $persenSelesai,
        ];
    }

    /**
     * Ambil data untuk seluruh grafik analitik.
     */
    public function getChartData(): array
    {
        // 1. Tren Bulanan (6 bulan terakhir)
        $monthlyLabels = [];
        $monthlyDarurat = [];
        $monthlyBiasa = [];
        $monthlyTotal = [];

        for ($i = 5; $i >= 0; $i--) {
            $dt = now()->subMonths($i);
            $monthKey = $dt->format('Y-m');
            $label = $dt->translatedFormat('M Y');

            $daruratCount = SpikapLaporan::where('sifat_laporan', 'darurat')
                ->whereYear('created_at', $dt->year)
                ->whereMonth('created_at', $dt->month)
                ->count();

            $biasaCount = SpikapLaporan::where('sifat_laporan', 'biasa')
                ->whereYear('created_at', $dt->year)
                ->whereMonth('created_at', $dt->month)
                ->count();

            $monthlyLabels[]  = $label;
            $monthlyDarurat[] = $daruratCount;
            $monthlyBiasa[]   = $biasaCount;
            $monthlyTotal[]   = $daruratCount + $biasaCount;
        }

        // 2. Distribusi Kategori Perundungan
        $kategoriCounts = [
            'fisik'   => (clone $this->getFilteredQuery())->where('jenis_perundungan', 'fisik')->count(),
            'verbal'  => (clone $this->getFilteredQuery())->where('jenis_perundungan', 'verbal')->count(),
            'sosial'  => (clone $this->getFilteredQuery())->where('jenis_perundungan', 'sosial')->count(),
            'digital' => (clone $this->getFilteredQuery())->where('jenis_perundungan', 'digital')->count(),
            'lainnya' => (clone $this->getFilteredQuery())->where('jenis_perundungan', 'lainnya')->count(),
        ];

        // 3. Titik Lokasi Rawan (Top 5)
        $lokasiRawan = (clone $this->getFilteredQuery())
            ->whereNotNull('lokasi_kejadian')
            ->where('lokasi_kejadian', '!=', '')
            ->select('lokasi_kejadian', DB::raw('count(*) as total'))
            ->groupBy('lokasi_kejadian')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        $lokasiLabels = $lokasiRawan->pluck('lokasi_kejadian')->toArray();
        $lokasiTotals = $lokasiRawan->pluck('total')->toArray();

        // 4. Waktu / Jam Rawan Kejadian
        $laporansWithTime = (clone $this->getFilteredQuery())
            ->whereNotNull('waktu_kejadian')
            ->get(['waktu_kejadian']);

        $waktuSlots = [
            'Pagi (< 07:00)'       => 0,
            'Pelajaran Pagi (07-09)'=> 0,
            'Istirahat 1 (09:30)'   => 0,
            'Pelajaran Siang (10-12)'=> 0,
            'Istirahat 2 (12-13)'   => 0,
            'Pulang (> 13:00)'      => 0,
        ];

        foreach ($laporansWithTime as $lap) {
            $hour = $lap->waktu_kejadian?->format('H:i');
            if (!$hour) continue;

            if ($hour < '07:00') {
                $waktuSlots['Pagi (< 07:00)']++;
            } elseif ($hour >= '07:00' && $hour < '09:30') {
                $waktuSlots['Pelajaran Pagi (07-09)']++;
            } elseif ($hour >= '09:30' && $hour < '10:00') {
                $waktuSlots['Istirahat 1 (09:30)']++;
            } elseif ($hour >= '10:00' && $hour < '12:00') {
                $waktuSlots['Pelajaran Siang (10-12)']++;
            } elseif ($hour >= '12:00' && $hour < '13:00') {
                $waktuSlots['Istirahat 2 (12-13)']++;
            } else {
                $waktuSlots['Pulang (> 13:00)']++;
            }
        }

        // 5. Smart Insights
        $highestKategori = collect($kategoriCounts)->sortDesc();
        $topKategoriName = $highestKategori->keys()->first();
        $topKategoriCount = $highestKategori->first();

        $topLokasi = $lokasiRawan->first();

        return [
            'monthly' => [
                'labels'  => $monthlyLabels,
                'darurat' => $monthlyDarurat,
                'biasa'   => $monthlyBiasa,
                'total'   => $monthlyTotal,
            ],
            'kategori' => [
                'labels' => ['Fisik', 'Verbal', 'Sosial', 'Cyberbullying', 'Lainnya'],
                'data'   => array_values($kategoriCounts),
            ],
            'lokasi' => [
                'labels' => !empty($lokasiLabels) ? $lokasiLabels : ['Belum Ada Data'],
                'data'   => !empty($lokasiTotals) ? $lokasiTotals : [0],
            ],
            'waktu' => [
                'labels' => array_keys($waktuSlots),
                'data'   => array_values($waktuSlots),
            ],
            'insights' => [
                'topKategori' => $topKategoriCount > 0 ? ucfirst($topKategoriName) : null,
                'topKategoriCount' => $topKategoriCount,
                'topLokasi' => $topLokasi?->lokasi_kejadian ?? null,
                'topLokasiCount' => $topLokasi?->total ?? 0,
            ],
        ];
    }

    public function render()
    {
        $metrics = $this->getMetrics();
        $chartData = $this->getChartData();

        return view('livewire.portal-guru.spikap-analitik', [
            'metrics'   => $metrics,
            'chartData' => $chartData,
        ])->layout('components.layouts.portal', [
            'title' => 'Dashboard Analitik Pola Perundungan — SPIKAP',
        ]);
    }
}
