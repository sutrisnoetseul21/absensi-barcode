<?php

namespace App\Livewire\Public;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use App\Models\Buku;
use App\Models\KategoriBuku;
use App\Models\MataPelajaran;
use App\Models\PengaturanSekolah;
use App\Models\Peminjaman;
use Illuminate\Support\Facades\Cache;

class KatalogPerpustakaan extends Component
{
    use WithPagination;

    #[Url(history: true)]
    public string $search = '';

    #[Url(history: true)]
    public ?string $kategori_id = null;

    #[Url(history: true)]
    public ?int $grade_level = null;

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingKategoriId()
    {
        $this->resetPage();
    }

    public function updatingGradeLevel()
    {
        $this->resetPage();
    }

    public function getActiveLoansProperty()
    {
        $loans = collect();

        if (array_key_exists('siswa', config('auth.guards')) && auth('siswa')->check()) {
            $loans = Peminjaman::with('eksemplar.buku')
                ->where('peminjam_type', 'siswa')
                ->where('peminjam_id', auth('siswa')->id())
                ->where('status', 'dipinjam')
                ->get();
        } elseif (array_key_exists('wali_kelas', config('auth.guards')) && auth('wali_kelas')->check()) {
            // Guard wali_kelas uses Teacher model. We MUST hardcode 'guru' for MorphMap.
            $loans = Peminjaman::with('eksemplar.buku')
                ->where('peminjam_type', 'guru')
                ->where('peminjam_id', auth('wali_kelas')->id())
                ->where('status', 'dipinjam')
                ->get();
        }

        return $loans;
    }

    #[Layout('components.layouts.app')]
    public function render()
    {
        $pengaturanSekolah = Cache::remember('public_pengaturan_sekolah', 300, function () {
            return PengaturanSekolah::current();
        });

        // Query Utama Buku (dengan hitungan eksemplar tersedia)
        $query = Buku::query()
            ->with(['kategoriBuku', 'mataPelajaran'])
            ->withCount(['eksemplarBukus as eksemplar_tersedia_count' => function ($q) {
                $q->where('status', 'tersedia');
            }]);

        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('judul', 'like', '%' . $this->search . '%')
                  ->orWhere('penulis', 'like', '%' . $this->search . '%')
                  ->orWhere('isbn', 'like', '%' . $this->search . '%');
            });
        }

        if (!empty($this->kategori_id)) {
            $query->where('kategori_id', $this->kategori_id);
        }

        if (!empty($this->grade_level)) {
            $query->where('grade_level', $this->grade_level);
        }

        $bukus = $query->orderBy('created_at', 'desc')->paginate(12);

        $kategoris = Cache::remember('kategori_bukus_all', 3600, function () {
            return KategoriBuku::orderBy('nama_kategori')->get();
        });

        // Statistik Agregat untuk Kartu Statistik (cache 10 menit agar tidak berat)
        $stats = Cache::remember('katalog_public_stats', 600, function () {
            return [
                'total_judul' => Buku::count(),
                'total_eksemplar' => \App\Models\EksemplarBuku::count(),
                'eksemplar_tersedia' => \App\Models\EksemplarBuku::where('status', 'tersedia')->count(),
                'buku_dipinjam' => \App\Models\EksemplarBuku::where('status', 'dipinjam')->count(),
            ];
        });

        // Kategori Terpopuler (diukur dari jumlah buku)
        $kategoriPopuler = Cache::remember('katalog_kategori_populer', 3600, function () {
            return KategoriBuku::withCount('bukus')
                ->orderBy('bukus_count', 'desc')
                ->take(5)
                ->get();
        });

        return view('livewire.public.katalog-perpustakaan', [
            'bukus' => $bukus,
            'kategoris' => $kategoris,
            'stats' => $stats,
            'kategoriPopuler' => $kategoriPopuler,
            'pengaturanSekolah' => $pengaturanSekolah,
            'activeLoans' => $this->activeLoans,
        ]);
    }
}
