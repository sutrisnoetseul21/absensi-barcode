<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use App\Models\Buku;
use App\Models\Peminjaman;
use App\Models\KunjunganPerpustakaan;
use Illuminate\Support\Facades\Auth;

class SiswaPerpustakaan extends Component
{
    use WithPagination;

    #[Url(history: true)]
    public string $search = '';

    #[Url(history: true)]
    public string $sortBy = 'terbaru'; // 'terbaru' or 'populer'

    public $activeTab = 'katalog'; // 'katalog' or 'riwayat'

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingSortBy()
    {
        $this->resetPage();
    }

    public function setTab($tab)
    {
        $this->activeTab = $tab;
        $this->resetPage();
    }

    #[Layout('components.layouts.portal')]
    public function render()
    {
        $studentId = Auth::user()->student?->id;
        $peminjamanAktif = collect();
        $riwayatPeminjaman = collect();
        $riwayatKunjungan = collect();
        $bukus = collect();

        if ($this->activeTab === 'peminjaman' && $studentId) {
            $peminjamanAktif = Peminjaman::with('eksemplarBuku.buku')
                ->where('peminjam_type', 'siswa')
                ->where('peminjam_id', $studentId)
                ->where('status', 'dipinjam')
                ->orderBy('created_at', 'desc')
                ->paginate(10);
        } elseif ($this->activeTab === 'riwayat' && $studentId) {
            $riwayatPeminjaman = Peminjaman::with('eksemplarBuku.buku')
                ->where('peminjam_type', 'siswa')
                ->where('peminjam_id', $studentId)
                ->where('status', '!=', 'dipinjam')
                ->orderBy('created_at', 'desc')
                ->paginate(10);
        } elseif ($this->activeTab === 'kunjungan' && $studentId) {
            $riwayatKunjungan = KunjunganPerpustakaan::where('pengunjung_type', 'siswa')
                ->where('pengunjung_id', $studentId)
                ->orderBy('tanggal', 'desc')
                ->orderBy('waktu_masuk', 'desc')
                ->paginate(10);
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

            // We need to fetch the closest return date for books that have 0 available exemplars
            // To be efficient, we can do this only for the paginated items that have 0 availability
            $bukus = $query->paginate(12);

            // Fetch return dates and check if student is currently borrowing this book
            foreach ($bukus as $buku) {
                $peminjamanSaya = Peminjaman::whereHas('eksemplarBuku', function ($q) use ($buku) {
                        $q->where('buku_id', $buku->id);
                    })
                    ->where('peminjam_type', 'siswa')
                    ->where('peminjam_id', $studentId)
                    ->where('status', 'dipinjam')
                    ->first();

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

        return view('livewire.siswa-perpustakaan', [
            'peminjamanAktif' => $peminjamanAktif,
            'riwayatPeminjaman' => $riwayatPeminjaman,
            'riwayatKunjungan' => $riwayatKunjungan,
            'bukus' => $bukus,
        ]);
    }
}
