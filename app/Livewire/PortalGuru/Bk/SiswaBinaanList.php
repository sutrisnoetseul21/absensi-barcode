<?php

namespace App\Livewire\PortalGuru\Bk;

use App\Models\EnrollmentSiswa;
use App\Models\JurnalGuruWali;
use App\Models\Kelas;
use App\Models\KonselingBk;
use App\Models\Presensi;
use App\Models\TahunAjaran;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.portal')]
class SiswaBinaanList extends Component
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
    public $filterIssue = 'all'; // 'all', 'pernah_konseling', 'ada_rujukan', 'ada_alpa'

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

    public function updatingFilterIssue()
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = EnrollmentSiswa::with(['siswa', 'kelas'])
            ->where('status', 'aktif')
            ->when($this->activeYear, fn ($q) => $q->where('academic_year_id', $this->activeYear->id));

        if (! $this->canAccessAll) {
            if (empty($this->accessibleClassIds)) {
                $query->whereRaw('1 = 0');
            } else {
                $query->whereIn('class_id', $this->accessibleClassIds);
            }
        }

        if ($this->filterClass) {
            $query->where('class_id', $this->filterClass);
        }

        if ($this->search) {
            $term = '%' . trim($this->search) . '%';
            $query->whereHas('siswa', function ($q) use ($term) {
                $q->where('name', 'like', $term)
                    ->orWhere('nisn', 'like', $term)
                    ->orWhere('nis', 'like', $term);
            });
        }

        // Subquery / filter issue
        if ($this->filterIssue === 'pernah_konseling') {
            $studentIdsWithKonseling = KonselingBk::distinct()->pluck('student_id')->toArray();
            $query->whereIn('student_id', $studentIdsWithKonseling);
        } elseif ($this->filterIssue === 'ada_rujukan') {
            $studentIdsWithRujukan = JurnalGuruWali::where('rujukan_kolaborasi', 'like', '%Guru BK%')
                ->distinct()
                ->pluck('student_id')
                ->toArray();
            $query->whereIn('student_id', $studentIdsWithRujukan);
        } elseif ($this->filterIssue === 'ada_alpa') {
            $studentIdsWithAlpa = Presensi::where('status', 'alpa')
                ->when($this->activeYear, fn ($q) => $q->where('academic_year_id', $this->activeYear->id))
                ->distinct()
                ->pluck('student_id')
                ->toArray();
            $query->whereIn('student_id', $studentIdsWithAlpa);
        }

        $enrollments = $query->paginate(12);

        // Fetch counts for the visible students
        $visibleStudentIds = $enrollments->pluck('student_id')->toArray();

        $konselingCounts = KonselingBk::whereIn('student_id', $visibleStudentIds)
            ->selectRaw('student_id, count(*) as total')
            ->groupBy('student_id')
            ->pluck('total', 'student_id')
            ->toArray();

        $rujukanCounts = JurnalGuruWali::where('rujukan_kolaborasi', 'like', '%Guru BK%')
            ->whereIn('student_id', $visibleStudentIds)
            ->selectRaw('student_id, count(*) as total')
            ->groupBy('student_id')
            ->pluck('total', 'student_id')
            ->toArray();

        $alpaCounts = Presensi::where('status', 'alpa')
            ->whereIn('student_id', $visibleStudentIds)
            ->when($this->activeYear, fn ($q) => $q->where('academic_year_id', $this->activeYear->id))
            ->selectRaw('student_id, count(*) as total')
            ->groupBy('student_id')
            ->pluck('total', 'student_id')
            ->toArray();

        // Total scoped metrics
        $baseStudentQuery = EnrollmentSiswa::where('status', 'aktif')
            ->when($this->activeYear, fn ($q) => $q->where('academic_year_id', $this->activeYear->id));

        if (! $this->canAccessAll && ! empty($this->accessibleClassIds)) {
            $baseStudentQuery->whereIn('class_id', $this->accessibleClassIds);
        } elseif (! $this->canAccessAll && empty($this->accessibleClassIds)) {
            $baseStudentQuery->whereRaw('1 = 0');
        }

        $totalSiswaBinaan = (clone $baseStudentQuery)->count();

        return view('livewire.portal-guru.bk.siswa-binaan-list', [
            'enrollments'      => $enrollments,
            'konselingCounts'  => $konselingCounts,
            'rujukanCounts'    => $rujukanCounts,
            'alpaCounts'       => $alpaCounts,
            'totalSiswaBinaan' => $totalSiswaBinaan,
        ]);
    }
}
