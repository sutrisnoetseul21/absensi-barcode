<?php

namespace App\Livewire\PortalGuru\Bk;

use App\Models\EnrollmentSiswa;
use App\Models\Guru;
use App\Models\JurnalGuruWali;
use App\Models\Kelas;
use App\Models\TahunAjaran;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.portal')]
class RujukanKasusList extends Component
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
    public $filterStatus = 'all'; // 'all', 'menunggu', 'proses', 'selesai'

    // Modal detail
    public $selectedRujukan = null;
    public $showDetailModal = false;

    public function mount()
    {
        $user = Auth::user();
        if (! $user || ! $user->canAccessPortalBk()) {
            abort(403, 'Akses ditolak: Anda tidak memiliki hak akses sebagai Guru BK.');
        }

        $this->teacher = $user->teacher;
        $this->activeYear = TahunAjaran::where('status', 'aktif')->first();

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

    public function updatingFilterStatus()
    {
        $this->resetPage();
    }

    public function showDetail($id)
    {
        $this->selectedRujukan = JurnalGuruWali::with(['siswa', 'guru', 'kelompok', 'konselingBk.guru'])
            ->find($id);

        if ($this->selectedRujukan) {
            $this->showDetailModal = true;
        }
    }

    public function closeDetailModal()
    {
        $this->showDetailModal = false;
        $this->selectedRujukan = null;
    }

    public function render()
    {
        // Kueri rujukan dari Guru Wali
        $query = JurnalGuruWali::with(['siswa', 'guru', 'kelompok', 'konselingBk'])
            ->where('rujukan_kolaborasi', 'like', '%Guru BK%');

        // Batasi siswa ke kelas binaan jika bukan akses semua kelas
        if (! $this->canAccessAll) {
            if (empty($this->accessibleClassIds)) {
                $query->whereRaw('1 = 0'); // Belum ada kelas binaan
            } else {
                $studentIds = EnrollmentSiswa::whereIn('class_id', $this->accessibleClassIds)
                    ->when($this->activeYear, fn ($q) => $q->where('academic_year_id', $this->activeYear->id))
                    ->where('status', 'aktif')
                    ->pluck('student_id')
                    ->toArray();

                $query->whereIn('student_id', $studentIds);
            }
        }

        // Filter kelas spesifik jika dipilih
        if ($this->filterClass) {
            $studentIdsInClass = EnrollmentSiswa::where('class_id', $this->filterClass)
                ->when($this->activeYear, fn ($q) => $q->where('academic_year_id', $this->activeYear->id))
                ->where('status', 'aktif')
                ->pluck('student_id')
                ->toArray();

            $query->whereIn('student_id', $studentIdsInClass);
        }

        // Search siswa
        if ($this->search) {
            $searchTerm = '%' . trim($this->search) . '%';
            $query->whereHas('siswa', function ($q) use ($searchTerm) {
                $q->where('name', 'like', $searchTerm)
                    ->orWhere('nisn', 'like', $searchTerm)
                    ->orWhere('nis', 'like', $searchTerm);
            });
        }

        // Filter status tindak lanjut BK
        if ($this->filterStatus === 'menunggu') {
            $query->whereDoesntHave('konselingBk');
        } elseif ($this->filterStatus === 'proses') {
            $query->whereHas('konselingBk', function ($q) {
                $q->whereIn('status_kasus', ['Dalam Penanganan', 'Bimbingan Lanjutan']);
            });
        } elseif ($this->filterStatus === 'selesai') {
            $query->whereHas('konselingBk', function ($q) {
                $q->where('status_kasus', 'Tuntas / Selesai');
            });
        }

        $rujukans = $query->latest('tanggal_waktu')->paginate(10);

        // Ringkasan metrik
        $baseCountQuery = JurnalGuruWali::where('rujukan_kolaborasi', 'like', '%Guru BK%');
        if (! $this->canAccessAll) {
            if (! empty($this->accessibleClassIds)) {
                $scopedStudentIds = EnrollmentSiswa::whereIn('class_id', $this->accessibleClassIds)
                    ->when($this->activeYear, fn ($q) => $q->where('academic_year_id', $this->activeYear->id))
                    ->where('status', 'aktif')
                    ->pluck('student_id')
                    ->toArray();
                $baseCountQuery->whereIn('student_id', $scopedStudentIds);
            } else {
                $baseCountQuery->whereRaw('1 = 0');
            }
        }

        $totalRujukan = (clone $baseCountQuery)->count();
        $totalMenunggu = (clone $baseCountQuery)->whereDoesntHave('konselingBk')->count();
        $totalProses = (clone $baseCountQuery)->whereHas('konselingBk', function ($q) {
            $q->whereIn('status_kasus', ['Dalam Penanganan', 'Bimbingan Lanjutan']);
        })->count();
        $totalSelesai = (clone $baseCountQuery)->whereHas('konselingBk', function ($q) {
            $q->where('status_kasus', 'Tuntas / Selesai');
        })->count();

        return view('livewire.portal-guru.bk.rujukan-kasus-list', [
            'rujukans'      => $rujukans,
            'totalRujukan'  => $totalRujukan,
            'totalMenunggu' => $totalMenunggu,
            'totalProses'   => $totalProses,
            'totalSelesai'  => $totalSelesai,
        ]);
    }
}
