<?php

namespace App\Http\Controllers;

use App\Models\PengaturanSekolah;
use App\Models\WebSetting;
use App\Models\WebSarpra;
use App\Models\WebArtikel;
use App\Models\WebGaleri;
use App\Models\WebWidget;
use App\Models\WebStatistic;
use App\Models\WebQuickLink;
use App\Models\Guru;
use App\Services\HomepageStatsService;
use Illuminate\View\View;

class BerandaController extends Controller
{
    public function __construct(protected HomepageStatsService $statsService) {}

    public function index(): View
    {
        $sekolah      = PengaturanSekolah::current();
        $setting      = WebSetting::instance();
        $sarpras      = WebSarpra::orderBy('urutan')->get();
        $galeris      = WebGaleri::orderBy('urutan')->get();
        $widgets      = WebWidget::orderBy('urutan')->get();
        $artikels     = WebArtikel::published()->where('tipe', 'berita')->latest('published_at')->take(6)->get();
        $pengumumans  = WebArtikel::published()->where('tipe', 'pengumuman')->latest('published_at')->take(6)->get();
        $prestasis    = WebArtikel::published()->where('tipe', 'prestasi')->latest('published_at')->take(8)->get();
        $stats        = $this->statsService->getStats();

        $webStats     = WebStatistic::orderBy('order')->take(4)->get();
        $quickLinks   = WebQuickLink::where('is_active', true)->orderBy('order')->take(4)->get();
        
        $gurus = Guru::with(['user', 'jabatans', 'kelasAjarans.kelas', 'pengajarans.mataPelajaran', 'pengajarans.kelasAjaran.kelas'])->get();

        return view('beranda.index', compact(
            'sekolah',
            'setting',
            'sarpras',
            'galeris',
            'widgets',
            'artikels',
            'pengumumans',
            'prestasis',
            'stats',
            'webStats',
            'quickLinks',
            'gurus',
        ));
    }

    public function guru(): View
    {
        $sekolah = PengaturanSekolah::current();
        $setting = WebSetting::instance();
        
        $teachers = Guru::with(['user', 'jabatans', 'kelasAjarans.kelas', 'pengajarans.mataPelajaran', 'pengajarans.kelasAjaran.kelas'])
            ->paginate(12);

        return view('beranda.guru', compact('sekolah', 'setting', 'teachers'));
    }

    public function artikel(string $slug): View
    {
        $artikel     = WebArtikel::published()->where('slug', $slug)->firstOrFail();
        $recentPosts = WebArtikel::published()->where('id', '!=', $artikel->id)->latest('published_at')->take(5)->get();
        $sekolah     = PengaturanSekolah::current();
        $setting     = WebSetting::instance();

        return view('beranda.artikel', compact('artikel', 'recentPosts', 'sekolah', 'setting'));
    }
}
