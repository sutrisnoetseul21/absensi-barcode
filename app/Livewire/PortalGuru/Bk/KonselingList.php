<?php

namespace App\Livewire\PortalGuru\Bk;

use App\Models\EnrollmentSiswa;
use App\Models\Kelas;
use App\Models\KonselingBk;
use App\Models\TahunAjaran;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.portal')]
class KonselingList extends Component
{
    use WithPagination;

    public $teacher;
    public $activeYear;
    public $accessibleClassIds = [];
    public $accessibleClasses = [];
    public $canAccessAll = false;

    // Filters
    public $search = '';
    public $filterClass = '';
    public $filterBidang = '';
    public $filterLayanan = '';
    public $filterStatus = '';
    public $bulan = ''; // 'Y-m'

    // Modal detail
    public $selectedKonseling = null;
    public $showDetailModal = false;

    // Modal delete
    public $konselingToDelete = null;
    public $showDeleteModal = false;

    public function mount()
    {
        $user = Auth::user();
        if (! $user || ! $user->canAccessPortalBk()) {
            abort(403, 'Akses ditolak: Anda tidak memiliki hak akses sebagai Guru BK.');
        }

        $this->teacher = $user->teacher;
        $this->activeYear = TahunAjaran::where('status', 'aktif')->first();
        $this->bulan = Carbon::now()->format('Y-m');

        if ($this->teacher) {
            $this->canAccessAll = $this->teacher->canAccessAllClasses();
            if ($this->canAccessAll) {
                $this->accessibleClasses = Kelas::orderBy('name')->get();
                $this->accessibleClassIds = $this->accessibleClasses->pluck('id')->toArray();
            } else {
                $this->accessibleClassIds = $this->teacher->getKelasBinaanBkIds($this->activeYear?->id);
                $this->accessibleClasses = Kelas::whereIn('id', $this->accessibleClassIds)->orderBy('name')->get();
            }
        }
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFilterClass()
    {
        $this->resetPage();
    }

    public function updatingFilterBidang()
    {
        $this->resetPage();
    }

    public function updatingFilterLayanan()
    {
        $this->resetPage();
    }

    public function updatingFilterStatus()
    {
        $this->resetPage();
    }

    public function updatingBulan()
    {
        $this->resetPage();
    }

    public function showDetail($id)
    {
        $this->selectedKonseling = KonselingBk::with(['siswa', 'guru', 'jurnalGuruWali.guru', 'tahunAjaran'])
            ->find($id);

        if ($this->selectedKonseling) {
            $this->showDetailModal = true;
        }
    }

    public function closeDetailModal()
    {
        $this->showDetailModal = false;
        $this->selectedKonseling = null;
    }

    public function confirmDelete($id)
    {
        $this->konselingToDelete = KonselingBk::with('siswa')->find($id);
        if ($this->konselingToDelete) {
            $this->showDeleteModal = true;
        }
    }

    public function cancelDelete()
    {
        $this->showDeleteModal = false;
        $this->konselingToDelete = null;
    }

    public function deleteKonseling()
    {
        if (! $this->konselingToDelete) {
            return;
        }

        // Pastikan hanya guru pembuat atau super admin yang dapat menghapus
        if (! $this->canAccessAll && $this->konselingToDelete->teacher_id !== $this->teacher?->id) {
            session()->flash('error', 'Anda hanya dapat menghapus sesi konseling yang Anda buat sendiri.');
            $this->cancelDelete();
            return;
        }

        $this->konselingToDelete->delete();
        session()->flash('success', 'Data sesi konseling berhasil dihapus.');
        $this->cancelDelete();
    }

    public function render()
    {
        $query = KonselingBk::with(['siswa', 'guru', 'jurnalGuruWali']);

        // Scope kelas binaan jika bukan akses semua kelas
        if (! $this->canAccessAll) {
            if (empty($this->accessibleClassIds)) {
                $query->whereRaw('1 = 0');
            } else {
                $studentIds = EnrollmentSiswa::whereIn('class_id', $this->accessibleClassIds)
                    ->when($this->activeYear, fn ($q) => $q->where('academic_year_id', $this->activeYear->id))
                    ->where('status', 'aktif')
                    ->pluck('student_id')
                    ->toArray();

                $query->whereIn('student_id', $studentIds);
            }
        }

        if ($this->filterClass) {
            $studentIdsInClass = EnrollmentSiswa::where('class_id', $this->filterClass)
                ->when($this->activeYear, fn ($q) => $q->where('academic_year_id', $this->activeYear->id))
                ->where('status', 'aktif')
                ->pluck('student_id')
                ->toArray();

            $query->whereIn('student_id', $studentIdsInClass);
        }

        if ($this->search) {
            $term = '%' . trim($this->search) . '%';
            $query->where(function ($q) use ($term) {
                $q->where('topik_masalah', 'like', $term)
                    ->orWhere('uraian_kasus', 'like', $term)
                    ->orWhereHas('siswa', function ($sq) use ($term) {
                        $sq->where('name', 'like', $term)
                            ->orWhere('nisn', 'like', $term);
                    });
            });
        }

        if ($this->filterBidang) {
            $query->where('bidang_bimbingan', $this->filterBidang);
        }

        if ($this->filterLayanan) {
            $query->where('jenis_layanan', $this->filterLayanan);
        }

        if ($this->filterStatus) {
            $query->where('status_kasus', $this->filterStatus);
        }

        if ($this->bulan) {
            try {
                $start = Carbon::createFromFormat('Y-m', $this->bulan)->startOfMonth();
                $end = Carbon::createFromFormat('Y-m', $this->bulan)->endOfMonth();
                $query->whereBetween('tanggal_waktu', [$start, $end]);
            } catch (\Exception $e) {
                // Ignore invalid date format
            }
        }

        $konselings = $query->latest('tanggal_waktu')->paginate(10);

        // Ringkasan metrik
        $metricQuery = KonselingBk::query();
        if (! $this->canAccessAll && ! empty($this->accessibleClassIds)) {
            $scopedIds = EnrollmentSiswa::whereIn('class_id', $this->accessibleClassIds)
                ->when($this->activeYear, fn ($q) => $q->where('academic_year_id', $this->activeYear->id))
                ->where('status', 'aktif')
                ->pluck('student_id')
                ->toArray();
            $metricQuery->whereIn('student_id', $scopedIds);
        }

        $totalSesi = (clone $metricQuery)->count();
        $totalBulanIni = (clone $metricQuery)
            ->whereBetween('tanggal_waktu', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()])
            ->count();
        $totalDalamPenanganan = (clone $metricQuery)
            ->whereIn('status_kasus', ['Dalam Penanganan', 'Bimbingan Lanjutan'])
            ->count();
        $totalRujukanWali = (clone $metricQuery)
            ->whereNotNull('jurnal_guru_wali_id')
            ->count();

        return view('livewire.portal-guru.bk.konseling-list', [
            'konselings'           => $konselings,
            'totalSesi'            => $totalSesi,
            'totalBulanIni'        => $totalBulanIni,
            'totalDalamPenanganan' => $totalDalamPenanganan,
            'totalRujukanWali'     => $totalRujukanWali,
        ]);
    }
}
