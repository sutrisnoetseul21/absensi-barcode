<?php

namespace App\Livewire\PortalWeb;

use App\Models\Alumni;
use App\Models\Pengaduan;
use App\Models\Presensi;
use App\Models\TahunAjaran;
use App\Models\WebArtikel;
use App\Models\WebFaq;
use App\Models\WebGaleri;
use App\Models\WebHalamanAkademik;
use App\Models\WebHalamanLayanan;
use App\Models\WebMicrosite;
use App\Models\WebPillar;
use App\Models\WebQuickLink;
use App\Models\WebSarpra;
use App\Models\WebStatistic;
use Livewire\Attributes\Layout;
use Livewire\Component;

class Dashboard extends Component
{
    #[Layout('components.layouts.portal')]
    public function render()
    {
        $today = now('Asia/Jakarta')->toDateString();
        $activeYear = TahunAjaran::where('status', 'aktif')->first();

        // Statistik konten web
        $totalBerita         = WebArtikel::where('tipe', 'berita')->where('is_published', true)->count();
        $totalPengumuman     = WebArtikel::where('tipe', 'pengumuman')->where('is_published', true)->count();
        $totalPrestasi       = WebArtikel::where('tipe', 'prestasi')->where('is_published', true)->count();
        $totalGaleri         = WebGaleri::count();
        $totalMicrosite      = WebMicrosite::where('is_active', true)->count();
        $totalAlumni         = Alumni::count();
        $totalAksesCepat     = WebQuickLink::where('is_active', true)->count();
        $totalAkademik       = WebHalamanAkademik::where('is_active', true)->count();
        $totalHalamanLayanan = WebHalamanLayanan::where('is_active', true)->count();
        $totalFaq            = WebFaq::where('is_active', true)->count();
        $totalSarpras        = WebSarpra::count();
        $totalPilar          = WebPillar::where('is_active', true)->count();
        $totalStatistik      = WebStatistic::count();
        $pengaduanMenunggu   = Pengaduan::where('status', 'menunggu')->count();
        $totalPengaduan      = Pengaduan::count();

        // Konten terbaru
        $artikelTerbaru = WebArtikel::whereIn('tipe', ['berita', 'pengumuman'])
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
            'totalBerita'         => $totalBerita,
            'totalPengumuman'     => $totalPengumuman,
            'totalPrestasi'       => $totalPrestasi,
            'totalGaleri'         => $totalGaleri,
            'totalMicrosite'      => $totalMicrosite,
            'totalAlumni'         => $totalAlumni,
            'totalAksesCepat'     => $totalAksesCepat,
            'totalAkademik'       => $totalAkademik,
            'totalHalamanLayanan' => $totalHalamanLayanan,
            'totalFaq'            => $totalFaq,
            'totalSarpras'        => $totalSarpras,
            'totalPilar'          => $totalPilar,
            'totalStatistik'      => $totalStatistik,
            'pengaduanMenunggu'   => $pengaduanMenunggu,
            'totalPengaduan'      => $totalPengaduan,
            'artikelTerbaru'      => $artikelTerbaru,
            'presensiHariIni'     => $presensiHariIni,
        ])->title('Dashboard Portal Web');
    }
}
