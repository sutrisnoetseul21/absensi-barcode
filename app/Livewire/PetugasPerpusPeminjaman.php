<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use App\Models\Peminjaman;
use Carbon\Carbon;

#[Layout('components.layouts.portal')]
class PetugasPerpusPeminjaman extends Component
{
    use WithPagination;

    public string $activeTab = 'dipinjam'; // 'dipinjam' | 'terlambat' | 'dikembalikan'
    public string $search = '';
    public int $perPage = 15;

    // Modal Unduh
    public bool $showUnduhModal = false;
    public array $filterStatusUnduh = [];
    public array $filterTipeAnggotaUnduh = [];
    public string $formatUnduh = 'pdf';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedActiveTab(): void
    {
        $this->resetPage();
        $this->search = '';
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
        $this->resetPage();
        $this->search = '';
    }

    public function openUnduhModal(): void
    {
        $this->filterStatusUnduh      = [];
        $this->filterTipeAnggotaUnduh = [];
        $this->formatUnduh            = 'pdf';
        $this->showUnduhModal         = true;
    }

    public function downloadPeminjaman(): void
    {
        $routeName = $this->formatUnduh === 'excel'
            ? 'perpustakaan.peminjaman-buku.excel'
            : 'perpustakaan.peminjaman-buku.pdf';

        $params = [];
        if (!empty($this->filterStatusUnduh)) {
            $params['status'] = $this->filterStatusUnduh;
        }
        if (!empty($this->filterTipeAnggotaUnduh)) {
            $params['tipe'] = $this->filterTipeAnggotaUnduh;
        }

        $this->showUnduhModal = false;
        $this->redirect(route($routeName, $params));
    }

    public function kembalikanBuku(string $peminjamanId): void
    {
        $peminjaman = Peminjaman::find($peminjamanId);
        if (!$peminjaman || $peminjaman->status !== 'dipinjam') return;

        $eksemplar = $peminjaman->eksemplarBuku;
        $peminjaman->update([
            'status'          => 'dikembalikan',
            'tanggal_kembali' => now()->toDateString(),
        ]);

        if ($eksemplar) {
            $eksemplar->update(['status' => 'tersedia']);
        }

        session()->flash('flash_success', 'Buku "' . ($eksemplar?->buku?->judul ?? '') . '" berhasil dikembalikan!');
        $this->resetPage();
    }

    public function render()
    {
        $today = Carbon::today('Asia/Jakarta');

        $baseQuery = Peminjaman::with(['peminjam', 'eksemplarBuku.buku'])
            ->when($this->search, function ($q) {
                $q->where(function ($q2) {
                    $q2->whereHas('eksemplarBuku', function ($sub) {
                        $sub->where('kode_eksemplar', 'like', "%{$this->search}%")
                            ->orWhereHas('buku', fn ($b) => $b->where('judul', 'like', "%{$this->search}%"));
                    })->orWhereHasMorph('peminjam', ['App\Models\Siswa', 'App\Models\Guru'], function ($pm) {
                        $pm->where('name', 'like', "%{$this->search}%");
                    });
                });
            });

        // Count badges (independent of search)
        $countDipinjam    = Peminjaman::where('status', 'dipinjam')->where('tanggal_jatuh_tempo', '>=', $today)->count();
        $countTerlambat   = Peminjaman::where('status', 'dipinjam')->where('tanggal_jatuh_tempo', '<', $today)->count();
        $countDikembalikan = Peminjaman::where('status', 'dikembalikan')->count();

        $query = clone $baseQuery;
        match ($this->activeTab) {
            'terlambat'    => $query->where('status', 'dipinjam')->where('tanggal_jatuh_tempo', '<', $today),
            'dikembalikan' => $query->where('status', 'dikembalikan'),
            default        => $query->where('status', 'dipinjam')->where('tanggal_jatuh_tempo', '>=', $today),
        };

        $peminjamans = $query->orderBy('created_at', 'desc')->paginate($this->perPage);

        return view('livewire.petugas-perpus-peminjaman', [
            'peminjamans'       => $peminjamans,
            'today'             => $today,
            'countDipinjam'     => $countDipinjam,
            'countTerlambat'    => $countTerlambat,
            'countDikembalikan' => $countDikembalikan,
        ])->title('Data Peminjaman - Portal Perpustakaan');
    }
}
