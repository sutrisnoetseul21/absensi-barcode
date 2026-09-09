<?php

namespace App\Livewire\PortalGuru;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Auth;
use App\Models\SpikapLaporan;

#[Layout('components.layouts.portal')]
class SpikapInbox extends Component
{
    use WithPagination;

    public $teacher;

    // Filters
    public string $search = '';
    public string $filterStatus = '';
    public string $filterSifat = '';
    public string $filterJenis = '';

    protected $queryString = [
        'search'       => ['except' => ''],
        'filterStatus' => ['except' => ''],
        'filterSifat'  => ['except' => ''],
        'filterJenis'  => ['except' => ''],
    ];

    public function mount(): void
    {
        $user = Auth::user();

        // Otorisasi: cek permission spikap.view_any atau role terkait SPIKAP / Wali Kelas (via role atau jabatan)
        $hasAccess = $user->can('spikap.view_any') ||
            $user->hasAnyRole(['spikap_guru_bk', 'spikap_wali_kelas', 'spikap_kepala_sekolah', 'spikap_admin', 'super_admin']) ||
            $user->isGuruBk() ||
            $user->isKepalaSekolah() ||
            ($user->hasRole('wali_kelas') && $user->teacher !== null);

        if (!$hasAccess) {
            abort(403, 'Akses ditolak. Anda tidak memiliki izin untuk melihat modul SPIKAP.');
        }

        $this->teacher = $user->teacher;
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFilterStatus(): void
    {
        $this->resetPage();
    }

    public function updatingFilterSifat(): void
    {
        $this->resetPage();
    }

    public function updatingFilterJenis(): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->reset(['search', 'filterStatus', 'filterSifat', 'filterJenis']);
        $this->resetPage();
    }

    public function render()
    {
        $user = Auth::user();

        // Base query yang sudah di-scope sesuai role user
        $baseQuery = SpikapLaporan::forUser($user);

        // Statistik cepat untuk header
        $totalCount   = (clone $baseQuery)->count();
        $daruratCount = (clone $baseQuery)->where('sifat_laporan', 'darurat')->count();
        $aktifCount   = (clone $baseQuery)->whereIn('status', ['diterima', 'dalam_investigasi'])->count();
        $selesaiCount = (clone $baseQuery)->where('status', 'selesai')->count();

        // Terapkan filter pencarian & kategori
        $query = (clone $baseQuery)->with(['siswa.enrollmentAktif.kelas', 'lampiran', 'lastHandledBy']);

        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->whereHas('siswa', function ($sq) {
                    $sq->where('name', 'like', '%' . $this->search . '%')
                       ->orWhere('nisn', 'like', '%' . $this->search . '%');
                })->orWhere('uraian_kejadian', 'like', '%' . $this->search . '%')
                  ->orWhere('lokasi_kejadian', 'like', '%' . $this->search . '%');
            });
        }

        if (!empty($this->filterStatus)) {
            $query->where('status', $this->filterStatus);
        }

        if (!empty($this->filterSifat)) {
            $query->where('sifat_laporan', $this->filterSifat);
        }

        if (!empty($this->filterJenis)) {
            $query->where('jenis_perundungan', $this->filterJenis);
        }

        // Prioritaskan laporan darurat yang belum selesai di bagian paling atas
        $laporan = $query->orderByRaw("CASE WHEN sifat_laporan = 'darurat' AND status != 'selesai' THEN 0 ELSE 1 END")
            ->latest()
            ->paginate(10);

        return view('livewire.portal-guru.spikap-inbox', compact(
            'laporan',
            'totalCount',
            'daruratCount',
            'aktifCount',
            'selesaiCount'
        ));
    }
}
