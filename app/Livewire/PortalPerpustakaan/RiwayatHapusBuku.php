<?php

namespace App\Livewire\PortalPerpustakaan;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use App\Models\Buku;
use App\Models\KategoriBuku;

#[Layout('components.layouts.portal')]
class RiwayatHapusBuku extends Component
{
    use WithPagination;

    public $search = '';
    public $filterKategori = '';

    // Pagination Reset on Filter
    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFilterKategori()
    {
        $this->resetPage();
    }

    // Modal state for detail
    public $showDetailModal = false;
    public $selectedBukuDetail = null;
    public $eksemplarDetailList = [];

    public function openDetailModal($id)
    {
        // Load the trashed book
        $buku = Buku::onlyTrashed()->with(['eksemplarBukus'])->find($id);
        if($buku) {
            $this->selectedBukuDetail = $buku;
            $this->eksemplarDetailList = $buku->eksemplarBukus;
            $this->showDetailModal = true;
        }
    }

    // Action: Restore
    public function restoreBuku($id)
    {
        $buku = Buku::onlyTrashed()->find($id);
        if($buku) {
            $buku->restore();
            session()->flash('message', 'Buku "' . $buku->judul . '" berhasil dipulihkan.');
        }
    }

    // Action: Force Delete
    public function forceDeleteBuku($id)
    {
        $buku = Buku::onlyTrashed()->find($id);
        if($buku) {
            $judul = $buku->judul;
            $buku->forceDelete();
            session()->flash('message', 'Buku "' . $judul . '" berhasil dihapus permanen.');
        }
    }

    public function render()
    {
        $kategoriList = KategoriBuku::orderBy('nama_kategori')->get();

        $bukus = Buku::onlyTrashed()
            ->with(['kategoriBuku', 'eksemplarBukus'])
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('judul', 'like', '%' . $this->search . '%')
                      ->orWhere('penulis', 'like', '%' . $this->search . '%')
                      ->orWhere('isbn', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->filterKategori, function ($query) {
                $query->where('kategori_id', $this->filterKategori);
            })
            ->orderBy('deleted_at', 'desc')
            ->paginate(10);

        return view('livewire.portal-perpustakaan.riwayat-hapus-buku', [
            'bukus' => $bukus,
            'kategoriList' => $kategoriList,
        ]);
    }
}
