<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use App\Models\KunjunganPerpustakaan;
use Carbon\Carbon;

#[Layout('components.layouts.portal')]
class PetugasPerpusKunjungan extends Component
{
    use WithPagination;

    public $search = '';
    public $filterTanggal = '';
    public $filterType = '';

    public function mount()
    {
        $this->filterTanggal = Carbon::today('Asia/Jakarta')->toDateString();
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedFilterTanggal()
    {
        $this->resetPage();
    }

    public function updatedFilterType()
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = KunjunganPerpustakaan::with(['pengunjung'])
            ->when($this->filterTanggal, function ($q) {
                $q->where('tanggal', $this->filterTanggal);
            })
            ->when($this->filterType, function ($q) {
                $q->where('pengunjung_type', $this->filterType);
            })
            ->when($this->search, function ($q) {
                $q->whereHasMorph('pengunjung', [\App\Models\Siswa::class, \App\Models\Guru::class], function ($sub) {
                    $sub->where('name', 'like', "%{$this->search}%");
                });
            })
            ->orderBy('created_at', 'desc');

        $kunjungans = $query->paginate(12);

        return view('livewire.petugas-perpus-kunjungan', [
            'kunjungans' => $kunjungans,
        ])->title('Riwayat Presensi Kunjungan - Portal Perpustakaan');
    }
}
