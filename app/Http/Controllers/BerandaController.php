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

    public function berita(\Illuminate\Http\Request $request): View
    {
        $sekolah = PengaturanSekolah::current();
        $setting = WebSetting::instance();
        
        $query = WebArtikel::published()->where('tipe', 'berita');

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('judul', 'like', "%{$search}%")
                  ->orWhere('konten', 'like', "%{$search}%");
            });
        }

        $berita = $query->latest('published_at')->paginate(9)->withQueryString();
        $recent_posts = WebArtikel::published()->where('tipe', 'berita')->latest('published_at')->take(5)->get();

        return view('beranda.berita', compact('sekolah', 'setting', 'berita', 'recent_posts'));
    }

    public function pengumuman(\Illuminate\Http\Request $request): View
    {
        $sekolah = PengaturanSekolah::current();
        $setting = WebSetting::instance();
        
        $query = WebArtikel::published()->where('tipe', 'pengumuman');

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('judul', 'like', "%{$search}%")
                  ->orWhere('konten', 'like', "%{$search}%");
            });
        }

        $pengumuman = $query->latest('published_at')->paginate(10)->withQueryString();

        return view('beranda.pengumuman', compact('sekolah', 'setting', 'pengumuman'));
    }

    public function prestasi(\Illuminate\Http\Request $request): View
    {
        $sekolah = PengaturanSekolah::current();
        $setting = WebSetting::instance();
        
        $query = WebArtikel::published()->where('tipe', 'prestasi');

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('judul', 'like', "%{$search}%")
                  ->orWhere('konten', 'like', "%{$search}%");
            });
        }

        $prestasi = $query->latest('published_at')->paginate(9)->withQueryString();

        return view('beranda.prestasi', compact('sekolah', 'setting', 'prestasi'));
    }

    public function galeri(\Illuminate\Http\Request $request): View
    {
        $sekolah = PengaturanSekolah::current();
        $setting = WebSetting::instance();
        
        $query = WebGaleri::query();

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where('judul', 'like', "%{$search}%");
        }

        $galleries = $query->orderBy('urutan')->latest()->paginate(12)->withQueryString();

        return view('beranda.galeri', compact('sekolah', 'setting', 'galleries'));
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

    public function alumni(\Illuminate\Http\Request $request): View
    {
        $sekolah        = PengaturanSekolah::current();
        $setting        = WebSetting::instance();
        $alumniSetting  = \App\Models\AlumniSetting::instance();
        $jenjangs       = \App\Models\AlumniJenjang::all();
        
        $totalAlumniCount = \App\Models\Alumni::count();
        $melanjutkanCount = \App\Models\Alumni::where('melanjutkan', true)->count();
        $angkatanTerbaru  = \App\Models\Alumni::max('tahun_lulus');
        $tahunLulusList   = \App\Models\Alumni::select('tahun_lulus')
                                ->distinct()
                                ->orderBy('tahun_lulus', 'desc')
                                ->pluck('tahun_lulus');

        $query = \App\Models\Alumni::with('jenjang')->latest();

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('nisn', 'like', "%{$search}%")
                  ->orWhere('nama_sekolah', 'like', "%{$search}%");
            });
        }

        if ($request->filled('tahun')) {
            $query->where('tahun_lulus', $request->tahun);
        }

        if ($request->filled('jenjang')) {
            $query->where('jenjang_id', $request->jenjang);
        }

        if ($request->filled('status')) {
            if ($request->status === 'melanjutkan') {
                $query->where('melanjutkan', true);
            } elseif ($request->status === 'tidak') {
                $query->where('melanjutkan', false);
            }
        }

        $alumnis = $alumniSetting->show_table ? $query->paginate(16)->withQueryString() : collect();

        return view('beranda.alumni', compact(
            'sekolah',
            'setting',
            'alumniSetting',
            'jenjangs',
            'alumnis',
            'totalAlumniCount',
            'melanjutkanCount',
            'angkatanTerbaru',
            'tahunLulusList'
        ));
    }

    public function alumniStore(\Illuminate\Http\Request $request): \Illuminate\Http\RedirectResponse
    {
        // Honeypot anti-spam
        if ($request->filled('website')) {
            return redirect()->back()->with('success', 'Terima kasih! Data alumni Anda berhasil dikirimkan.');
        }

        $request->validate([
            'nisn'          => 'required|string|max:50',
            'nama'          => 'required|string|max:255',
            'jenis_kelamin' => 'required|in:L,P',
            'tahun_lulus'   => 'required|integer|min:1970|max:2099',
            'melanjutkan'   => 'nullable',
            'jenjang_id'    => 'nullable|exists:alumni_jenjangs,id',
            'nama_sekolah'  => 'nullable|string|max:255',
            'no_hp'         => 'nullable|string|max:50',
            'foto'          => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ], [
            'nisn.required'          => 'NISN wajib diisi.',
            'nama.required'          => 'Nama lengkap wajib diisi.',
            'jenis_kelamin.required' => 'Pilih jenis kelamin.',
            'tahun_lulus.required'   => 'Pilih tahun kelulusan.',
            'foto.image'             => 'File foto harus berformat gambar.',
            'foto.max'               => 'Ukuran foto maksimal 2MB.',
        ]);

        $nisn = trim($request->nisn);
        $melanjutkan = $request->boolean('melanjutkan');

        // Cek apakah NISN terdaftar di database siswa sistem
        $existingStudent = \App\Models\Siswa::where('nisn', $nisn)->first();

        if ($existingStudent && $existingStudent->user_id) {
            return redirect()->back()->withInput()->with('info_login', 'NISN Anda (' . $nisn . ') sudah terdaftar dalam akun sistem sekolah. Silakan Login ke Portal Siswa untuk memperbarui data tracer study Anda secara terintegrasi.');
        }

        $fotoPath = null;
        if ($request->hasFile('foto')) {
            $fotoPath = $request->file('foto')->store('alumni', 'public');
        }

        \App\Models\Alumni::updateOrCreate(
            ['nisn' => $nisn],
            [
                'student_id'   => $existingStudent?->id,
                'source'       => $existingStudent ? 'sistem' : 'web_mandiri',
                'nama'         => strip_tags($request->nama),
                'jenis_kelamin'=> $request->jenis_kelamin,
                'tahun_lulus'  => $request->tahun_lulus,
                'melanjutkan'  => $melanjutkan,
                'jenjang_id'   => $melanjutkan ? $request->jenjang_id : null,
                'nama_sekolah' => $melanjutkan ? strip_tags($request->nama_sekolah) : null,
                'no_hp'        => strip_tags($request->no_hp),
                'foto'         => $fotoPath,
            ]
        );

        return redirect()->back()->with('success', 'Terima kasih! Data tracer study Anda telah berhasil disimpan.');
    }
}
