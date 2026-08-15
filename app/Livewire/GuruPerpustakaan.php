<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use App\Models\Buku;
use App\Models\Peminjaman;
use App\Models\KunjunganPerpustakaan;
use App\Models\TahunAjaran;
use App\Models\EnrollmentSiswa;
use Illuminate\Support\Facades\Auth;

class GuruPerpustakaan extends Component
{
    use WithPagination;

    #[Url(history: true)]
    public string $search = '';

    #[Url(history: true)]
    public string $sortBy = 'terbaru';

    public $activeTab = 'katalog';

    public $kelasAmpu = [];
    public $selectedKelasId = '';

    public function mount()
    {
        $this->loadKelasAmpu();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingSortBy()
    {
        $this->resetPage();
    }
    
    public function updatingSelectedKelasId()
    {
        $this->resetPage();
    }

    public function setTab($tab)
    {
        $this->activeTab = $tab;
        $this->resetPage();
    }

    private function loadKelasAmpu()
    {
        $guru = Auth::user()->teacher;
        if (!$guru) return;

        $activeYear = TahunAjaran::where('status', 'aktif')->first();
        if (!$activeYear) return;

        $kelasCollection = collect();

        // 1. Wali Kelas
        $kelasWali = $guru->kelasAjarans()
            ->where('academic_year_id', $activeYear->id)
            ->with('kelas')
            ->get();
        foreach ($kelasWali as $kw) {
            if ($kw->kelas) $kelasCollection->push($kw->kelas);
        }

        // 2. Guru Mapel
        $pengajarans = $guru->pengajarans()
            ->whereHas('kelasAjaran', function ($q) use ($activeYear) {
                $q->where('academic_year_id', $activeYear->id);
            })
            ->with('kelasAjaran.kelas')
            ->get();
        foreach ($pengajarans as $p) {
            if ($p->kelasAjaran && $p->kelasAjaran->kelas) {
                $kelasCollection->push($p->kelasAjaran->kelas);
            }
        }

        // 3. Kelas Pantau (Guru BK dll)
        $kelasPantau = $guru->kelasPantau()
            ->where('academic_year_id', $activeYear->id)
            ->with('kelas')
            ->get();
        foreach ($kelasPantau as $kp) {
            if ($kp->kelas) $kelasCollection->push($kp->kelas);
        }

        $this->kelasAmpu = $kelasCollection->unique('id')->sortBy('name')->values()->all();
        if (count($this->kelasAmpu) > 0) {
            $this->selectedKelasId = $this->kelasAmpu[0]->id;
        }
    }

    #[Layout('components.layouts.portal')]
    public function render()
    {
        $guruId = Auth::user()->teacher?->id;
        $peminjamanAktif = collect();
        $riwayatPeminjaman = collect();
        $riwayatKunjungan = collect();
        $peminjamanSiswa = collect();
        $bukus = collect();

        if ($this->activeTab === 'peminjaman' && $guruId) {
            $peminjamanAktif = Peminjaman::with('eksemplarBuku.buku')
                ->where('peminjam_type', 'guru')
                ->where('peminjam_id', $guruId)
                ->where('status', 'dipinjam')
                ->orderBy('created_at', 'desc')
                ->paginate(10);
        } elseif ($this->activeTab === 'riwayat' && $guruId) {
            $riwayatPeminjaman = Peminjaman::with('eksemplarBuku.buku')
                ->where('peminjam_type', 'guru')
                ->where('peminjam_id', $guruId)
                ->where('status', '!=', 'dipinjam')
                ->orderBy('created_at', 'desc')
                ->paginate(10);
        } elseif ($this->activeTab === 'kunjungan' && $guruId) {
            $riwayatKunjungan = KunjunganPerpustakaan::where('pengunjung_type', 'guru')
                ->where('pengunjung_id', $guruId)
                ->orderBy('tanggal', 'desc')
                ->orderBy('waktu_masuk', 'desc')
                ->paginate(10);
        } elseif ($this->activeTab === 'siswa' && $this->selectedKelasId) {
            $studentIds = EnrollmentSiswa::where('class_id', $this->selectedKelasId)
                ->whereHas('tahunAjaran', function($q) {
                    $q->where('status', 'aktif');
                })
                ->pluck('student_id');

            $peminjamanSiswa = Peminjaman::with(['eksemplarBuku.buku', 'peminjam'])
                ->where('peminjam_type', 'siswa')
                ->whereIn('peminjam_id', $studentIds)
                ->where('status', 'dipinjam')
                ->orderBy('created_at', 'desc')
                ->paginate(15);
        } elseif ($this->activeTab === 'katalog') {
            $query = Buku::query()
                ->with(['kategoriBuku'])
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

            if ($this->sortBy === 'populer') {
                $query->withCount('peminjamans')->orderBy('peminjamans_count', 'desc');
            } else {
                $query->orderBy('created_at', 'desc');
            }

            $bukus = $query->paginate(15);

            foreach ($bukus as $buku) {
                $peminjamanSaya = null;
                if ($guruId) {
                    $peminjamanSaya = Peminjaman::whereHas('eksemplarBuku', function ($q) use ($buku) {
                            $q->where('buku_id', $buku->id);
                        })
                        ->where('peminjam_type', 'guru')
                        ->where('peminjam_id', $guruId)
                        ->where('status', 'dipinjam')
                        ->first();
                }

                $buku->dipinjam_oleh_saya = $peminjamanSaya !== null;

                if ($buku->dipinjam_oleh_saya) {
                    $buku->earliest_return_date = $peminjamanSaya->tanggal_jatuh_tempo ? \Carbon\Carbon::parse($peminjamanSaya->tanggal_jatuh_tempo)->format('d M Y') : null;
                } elseif ($buku->eksemplar_tersedia_count == 0) {
                    $earliestReturnDate = Peminjaman::whereHas('eksemplarBuku', function ($q) use ($buku) {
                            $q->where('buku_id', $buku->id);
                        })
                        ->where('status', 'dipinjam')
                        ->whereNotNull('tanggal_jatuh_tempo')
                        ->min('tanggal_jatuh_tempo');
                    
                    $buku->earliest_return_date = $earliestReturnDate ? \Carbon\Carbon::parse($earliestReturnDate)->format('d M Y') : null;
                }
            }
        }

        return view('livewire.guru-perpustakaan', [
            'peminjamanAktif' => $peminjamanAktif,
            'riwayatPeminjaman' => $riwayatPeminjaman,
            'riwayatKunjungan' => $riwayatKunjungan,
            'peminjamanSiswa' => $peminjamanSiswa,
            'bukus' => $bukus,
        ]);
    }
}
