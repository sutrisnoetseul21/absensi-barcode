<?php

namespace App\Livewire\PortalWeb;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\WebArtikel;
use App\Models\WebGaleri;
use App\Models\WebQuickLink;
use App\Models\Alumni;
use App\Models\Presensi;
use App\Models\TahunAjaran;

class Dashboard extends Component
{
    #[Layout('components.layouts.portal')]
    public function render()
    {
        $today = now('Asia/Jakarta')->toDateString();
        $activeYear = TahunAjaran::where('status', 'aktif')->first();

        // Statistik konten web
        $totalBerita     = WebArtikel::where('tipe', 'berita')->where('is_published', true)->count();
        $totalPengumuman = WebArtikel::where('tipe', 'pengumuman')->where('is_published', true)->count();
        $totalPrestasi   = WebArtikel::where('tipe', 'prestasi')->where('is_published', true)->count();
        $totalGaleri     = WebGaleri::count();
        $totalAlumni     = Alumni::count();
        $totalPelayanan  = WebQuickLink::where('is_active', true)->count();

        // Konten terbaru
        $artikelTerbaru  = WebArtikel::whereIn('tipe', ['berita', 'pengumuman'])
            ->where('is_published', true)
            ->latest()
            ->limit(5)
            ->get();

        // Presensi hari ini
        $presensiHariIni = [];
        if ($activeYear) {
            $statusCounts = Presensi::where('academic_year_id', $activeYear->id)
                ->where('date', $today)
                ->selectRaw('status, COUNT(*) as total')
                ->groupBy('status')
                ->pluck('total', 'status');

            $presensiHariIni = [
                'hadir' => $statusCounts->get('hadir', 0),
                'telat' => $statusCounts->get('telat', 0),
                'sakit' => $statusCounts->get('sakit', 0),
                'izin'  => $statusCounts->get('izin', 0),
                'alpa'  => $statusCounts->get('alpa', 0),
            ];
        }

        return view('livewire.portal-web.dashboard', [
            'totalBerita'     => $totalBerita,
            'totalPengumuman' => $totalPengumuman,
            'totalPrestasi'   => $totalPrestasi,
            'totalGaleri'     => $totalGaleri,
            'totalAlumni'     => $totalAlumni,
            'totalPelayanan'  => $totalPelayanan,
            'artikelTerbaru'  => $artikelTerbaru,
            'presensiHariIni' => $presensiHariIni,
        ])->title('Dashboard Portal Web');
    }
}
