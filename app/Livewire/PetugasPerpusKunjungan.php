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
    public $filterTanggalAwal = '';
    public $filterTanggalAkhir = '';
    public $filterType = '';

    // Modal Unduh
    public bool $showUnduhModal = false;
    public $filterMulaiUnduh = '';
    public $filterAkhirUnduh = '';
    public array $filterTipeAnggotaUnduh = [];
    public string $formatUnduh = 'pdf';
    public int $perPage = 15;

    public function mount()
    {
        $this->filterTanggalAwal = Carbon::today('Asia/Jakarta')->toDateString();
        $this->filterTanggalAkhir = Carbon::today('Asia/Jakarta')->toDateString();
    }

    public function openUnduhModal(): void
    {
        $this->filterMulaiUnduh       = '';
        $this->filterAkhirUnduh       = '';
        $this->filterTipeAnggotaUnduh = [];
        $this->formatUnduh            = 'pdf';
        $this->showUnduhModal         = true;
    }

    public function downloadKunjungan(): void
    {
        $routeName = $this->formatUnduh === 'excel'
            ? 'perpustakaan.kunjungan.excel'
            : 'perpustakaan.kunjungan.pdf';

        $params = [];
        if ($this->filterMulaiUnduh) {
            $params['start_date'] = $this->filterMulaiUnduh;
        }
        if ($this->filterAkhirUnduh) {
            $params['end_date'] = $this->filterAkhirUnduh;
        }
        if (!empty($this->filterTipeAnggotaUnduh)) {
            $params['tipe'] = $this->filterTipeAnggotaUnduh;
        }

        $this->showUnduhModal = false;
        $this->redirect(route($routeName, $params));
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
            ->when($this->filterTanggalAwal, function ($q) {
                $q->where('tanggal', '>=', $this->filterTanggalAwal);
            })
            ->when($this->filterTanggalAkhir, function ($q) {
                $q->where('tanggal', '<=', $this->filterTanggalAkhir);
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

        $kunjungans = $query->paginate($this->perPage);

        return view('livewire.petugas-perpus-kunjungan', [
            'kunjungans' => $kunjungans,
        ])->title('Riwayat Presensi Kunjungan - Portal Perpustakaan');
    }
}
