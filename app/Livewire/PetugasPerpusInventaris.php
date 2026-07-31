<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use App\Models\InventarisBuku;

#[Layout('components.layouts.portal')]
class PetugasPerpusInventaris extends Component
{
    use WithPagination;

    public $search = '';
    public $filterStatus = '';

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedFilterStatus()
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = InventarisBuku::with(['buku.kategoriBuku', 'buku.klasifikasiDdc'])
            ->when($this->search, function ($q) {
                $q->where('no_inventaris', 'like', "%{$this->search}%")
                  ->orWhereHas('buku', function ($sub) {
                      $sub->where('judul', 'like', "%{$this->search}%")
                          ->orWhere('isbn', 'like', "%{$this->search}%");
                  });
            })
            ->when($this->filterStatus, function ($q) {
                $q->where('status', $this->filterStatus);
            })
            ->orderBy('created_at', 'desc');

        $inventaris = $query->paginate(12);

        return view('livewire.petugas-perpus-inventaris', [
            'inventaris' => $inventaris,
        ])->title('Inventaris Buku - Portal Perpustakaan');
    }
}
