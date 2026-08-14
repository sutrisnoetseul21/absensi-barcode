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

    // Modal Unduh
    public bool $showUnduhModal = false;
    public array $filterStatusUnduh = [];
    public string $formatUnduh = 'pdf';

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedFilterStatus()
    {
        $this->resetPage();
    }

    public function openUnduhModal(): void
    {
        $this->filterStatusUnduh = [];
        $this->formatUnduh       = 'pdf';
        $this->showUnduhModal    = true;
    }

    public function downloadInventaris(): void
    {
        $routeName = $this->formatUnduh === 'excel'
            ? 'perpustakaan.inventaris-buku.excel'
            : 'perpustakaan.inventaris-buku.pdf';

        $params = [];
        if (!empty($this->filterStatusUnduh)) {
            $params['status'] = $this->filterStatusUnduh;
        }

        $this->showUnduhModal = false;
        $this->redirect(route($routeName, $params));
    }

    public function render()
    {
        $query = InventarisBuku::with(['buku.kategoriBuku', 'buku.klasifikasiDdc'])
            ->when($this->search, function ($q) {
                $q->where(function ($subQ) {
                    $subQ->where('no_inventaris', 'like', "%{$this->search}%")
                        ->orWhereHas('eksemplarBukus', fn ($ek) => $ek->where('kode_eksemplar', $this->search))
                        ->orWhereHas('buku', function ($sub) {
                            $sub->where('judul', 'like', "%{$this->search}%")
                                ->orWhere('isbn', 'like', "%{$this->search}%");
                        });
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
