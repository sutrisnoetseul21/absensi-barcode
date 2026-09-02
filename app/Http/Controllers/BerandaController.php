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

    public function pengaduan(): View
    {
        $sekolah          = PengaturanSekolah::current();
        $setting          = WebSetting::instance();
        $pengaduanSetting = \App\Models\PengaduanSetting::instance();
        $kategoris        = \App\Models\PengaduanKategori::orderBy('urutan', 'asc')->get();

        return view('beranda.pengaduan', compact('sekolah', 'setting', 'pengaduanSetting', 'kategoris'));
    }

    public function pengaduanStore(\Illuminate\Http\Request $request): \Illuminate\Http\RedirectResponse
    {
        // Honeypot anti-spam
        if ($request->filled('website')) {
            return redirect()->back()->with('success', 'Pesan Anda berhasil dikirim.');
        }

        $request->validate([
            'nama'                  => 'required|string|max:255',
            'email'                 => 'required|email|max:255',
            'no_hp'                 => 'nullable|string|max:50',
            'pengaduan_kategori_id' => 'required|exists:pengaduan_kategoris,id',
            'isi_pengaduan'         => 'required|string|max:5000',
        ], [
            'nama.required'                  => 'Nama lengkap wajib diisi.',
            'email.required'                 => 'Alamat email wajib diisi.',
            'email.email'                    => 'Format email tidak valid.',
            'pengaduan_kategori_id.required' => 'Silakan pilih kategori pengaduan.',
            'isi_pengaduan.required'         => 'Isi pesan / pengaduan wajib diisi.',
        ]);

        \App\Models\Pengaduan::create([
            'nama'                  => strip_tags($request->nama),
            'email'                 => strip_tags($request->email),
            'no_hp'                 => strip_tags($request->no_hp),
            'pengaduan_kategori_id' => $request->pengaduan_kategori_id,
            'isi_pengaduan'         => strip_tags($request->isi_pengaduan),
            'status'                => 'menunggu',
        ]);

        return redirect()->back()->with('success', 'Terima kasih! Pesan dan aspirasi Anda telah kami terima dan akan segera ditindaklanjuti.');
    }
}
