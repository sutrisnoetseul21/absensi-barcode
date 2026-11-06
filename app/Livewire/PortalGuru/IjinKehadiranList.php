<?php

namespace App\Livewire\PortalGuru;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use App\Models\LeaveRequest;
use App\Models\EnrollmentSiswa;
use App\Models\Kelas;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.portal')]
class IjinKehadiranList extends Component
{
    use WithPagination;

    public $teacher;
    public $accessibleClassIds = [];
    public $accessibleClasses = [];

    // Filters: Default langsung ke siswa yang menunggu ('pending') & semua waktu
    public $search = '';
    public $filterClass = '';
    public $filterStatus = 'pending';
    public $filterMonth = '';

    public $availableMonths = [];

    public function mount()
    {
        $user = Auth::user();
        $isWk = $user && $user->isWaliKelasMurni();
        $isBkWithClasses = $user && $user->isGuruBk() && $user->teacher?->kelasPantau()->exists();

        if (!$isWk && !$isBkWithClasses) {
            abort(403, 'Akses ditolak: Menu permohonan ijin hanya untuk Wali Kelas dan Guru BK.');
        }

        $this->teacher = $user->teacher;

        if ($this->teacher) {
            // Dapatkan kelas Wali Kelas
            $kelasWali = $this->teacher->kelasAjarans()->pluck('class_id')->toArray();
            // Dapatkan kelas Pantau (BK)
            $kelasPantau = $this->teacher->kelasPantau()->pluck('class_id')->toArray();

            $this->accessibleClassIds = array_unique(array_merge($kelasWali, $kelasPantau));
            $this->accessibleClasses = Kelas::whereIn('id', $this->accessibleClassIds)->orderBy('name')->get();
        } else {
            $this->accessibleClassIds = [];
            $this->accessibleClasses = collect();
        }

        $this->filterStatus = 'pending';
        $this->filterMonth = '';
        $this->generateAvailableMonths();
    }

    protected function generateAvailableMonths()
    {
        $months = [];
        for ($i = 0; $i < 6; $i++) {
            $date = now()->subMonths($i);
            $months[$date->format('Y-m')] = $date->translatedFormat('F Y');
        }
        $this->availableMonths = $months;
    }

    public function setStatusFilter(string $status): void
    {
        $this->filterStatus = $status;
        if ($status === 'pending') {
            $this->filterMonth = '';
        }
        $this->resetPage();
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedFilterClass()
    {
        $this->resetPage();
    }

    public function updatedFilterStatus()
    {
        if ($this->filterStatus === 'pending') {
            $this->filterMonth = '';
        }
        $this->resetPage();
    }

    public function updatedFilterMonth()
    {
        $this->resetPage();
    }

    public function render()
    {
        // 1. Dapatkan studentIds sesuai kelas yang dipilih (atau semua kelas yang bisa diakses)
        $classIdsToFilter = $this->filterClass ? [$this->filterClass] : $this->accessibleClassIds;

        $allStudentIds = EnrollmentSiswa::whereIn('class_id', $this->accessibleClassIds)
            ->where('status', 'aktif')
            ->pluck('student_id')
            ->toArray();

        $studentIds = EnrollmentSiswa::whereIn('class_id', $classIdsToFilter)
            ->where('status', 'aktif')
            ->pluck('student_id')
            ->toArray();

        $pendingCount = LeaveRequest::whereIn('student_id', $allStudentIds)->where('status', 'pending')->count();
        $approvedCount = LeaveRequest::whereIn('student_id', $allStudentIds)->where('status', 'approved')->count();
        $rejectedCount = LeaveRequest::whereIn('student_id', $allStudentIds)->where('status', 'rejected')->count();

        // 2. Query LeaveRequest
        $requests = LeaveRequest::whereIn('student_id', $studentIds)
            ->with(['student.enrollmentAktif.kelas', 'student.user'])
            ->when($this->search, function($query) {
                $query->whereHas('student', function($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('nisn', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->filterStatus !== '', function($query) {
                $query->where('status', $this->filterStatus);
            })
            ->when($this->filterMonth, function($query) {
                $parts = explode('-', $this->filterMonth);
                if (count($parts) == 2) {
                    $query->whereYear('start_date', $parts[0])
                          ->whereMonth('start_date', $parts[1]);
                }
            })
            ->orderByRaw("FIELD(status, 'pending') DESC")
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('livewire.portal-guru.ijin-kehadiran-list', [
            'requests' => $requests,
            'counts' => [
                'pending' => $pendingCount,
                'approved' => $approvedCount,
                'rejected' => $rejectedCount,
                'all' => $pendingCount + $approvedCount + $rejectedCount,
            ],
        ]);
    }
}
