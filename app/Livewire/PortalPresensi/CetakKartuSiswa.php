<?php

namespace App\Livewire\PortalPresensi;

use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

class CetakKartuSiswa extends Component
{
    use WithPagination;

    // Filters
    public $academicYears = [];
    public $selectedAcademicYearId;
    public $classes = [];
    public $selectedClassId = null;
    public bool $hasSubmittedFilter = false;

    // Search & Select
    public $search = '';
    public $selectAll = false;
    public $selectedStudents = [];

    public function mount(): void
    {
        $this->academicYears = TahunAjaran::orderBy('start_year', 'desc')->get();

        $activeYear = TahunAjaran::where('status', 'aktif')->first() ?? $this->academicYears->first();
        if ($activeYear) {
            $this->selectedAcademicYearId = $activeYear->id;
        }

        $this->loadClasses();
    }

    public function loadClasses(): void
    {
        if (!$this->selectedAcademicYearId) {
            $this->classes = collect();
            $this->selectedClassId = null;
            return;
        }
        
        if (request()->routeIs('portal-guru.*')) {
            $user = Auth::user();
            if ($user->hasRole('wali_kelas') && $user->teacher === null) {
                abort(403, 'Akses Ditolak: Data profil guru Anda belum ditautkan oleh Admin.');
            }

            $isAdminMode = $user->hasAnyRole(['super_admin', 'admin_presensi']);
            $hasBypass   = $user->can('portal_guru:akses_semua_kelas');

            if (!$isAdminMode && !$hasBypass) {
                $actor = $user->teacher;
                $this->classes = Kelas::whereHas('kelasAjarans', function ($query) use ($actor) {
                    $query->where('academic_year_id', $this->selectedAcademicYearId)
                          ->where('teacher_id', $actor->id);
                })->orderBy('name', 'asc')->get();
            } else {
                $this->classes = Kelas::whereHas('kelasAjarans', function ($query) {
                    $query->where('academic_year_id', $this->selectedAcademicYearId);
                })->orderBy('name', 'asc')->get();
            }
        } else {
            $this->classes = Kelas::orderBy('name', 'asc')->get();
        }

        if ($this->classes->isNotEmpty()) {
            if (!collect($this->classes)->contains('id', $this->selectedClassId)) {
                $this->selectedClassId = collect($this->classes)->first()->id;
            }
        } else {
            $this->selectedClassId = null;
        }
    }

    public function updatedSelectedAcademicYearId(): void
    {
        $this->hasSubmittedFilter = false;
        $this->loadClasses();
    }

    public function updatedSelectedClassId(): void
    {
        if (request()->routeIs('portal-guru.*')) {
            $user = Auth::user();
            if ($user->hasRole('wali_kelas') && $user->teacher === null) {
                abort(403, 'Akses Ditolak: Data profil guru tidak lengkap.');
            }

            $isAdminMode = $user->hasAnyRole(['super_admin', 'admin_presensi']);
            $hasBypass   = $user->can('portal_guru:akses_semua_kelas');
            
            if (!$isAdminMode && !$hasBypass) {
                if (!collect($this->classes)->contains('id', $this->selectedClassId)) {
                    abort(403, 'Unauthorized action. Anda tidak memiliki akses ke kelas ini.');
                }
            }
        }
        $this->hasSubmittedFilter = false;
    }

    public function filterData(): void
    {
        if (!$this->selectedClassId) {
            session()->flash('warning', 'Silakan pilih Kelas terlebih dahulu sebelum memproses.');
            return;
        }

        $this->hasSubmittedFilter = true;
        $this->resetPage(); // Reset pagination when filter changes
        $this->selectedStudents = [];
        $this->selectAll = false;
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->selectedStudents = $this->getStudentsQuery()->pluck('id')->map(fn($id) => (string)$id)->toArray();
        } else {
            $this->selectedStudents = [];
        }
    }

    public function getStudentsQuery()
    {
        if (!$this->hasSubmittedFilter || !$this->selectedClassId) {
            return Siswa::query()->whereRaw('1 = 0');
        }

        return Siswa::query()
            ->where('status', 'aktif')
            ->whereHas('enrollments', function ($q) {
                $q->where('class_id', $this->selectedClassId)
                  ->where('academic_year_id', $this->selectedAcademicYearId)
                  ->where('status', 'aktif');
            })
            ->when($this->search, function ($q) {
                $q->where(function ($query) {
                    $query->where('name', 'like', '%' . $this->search . '%')
                          ->orWhere('nisn', 'like', '%' . $this->search . '%')
                          ->orWhere('nis', 'like', '%' . $this->search . '%');
                });
            })
            ->orderBy('name');
    }

    public function getStudentsProperty()
    {
        return $this->getStudentsQuery()->paginate(15);
    }

    public function cetakKartu(string $studentId)
    {
        $url = route('siswa.cetak-kartu-login', $studentId);
        $this->dispatch('open-url', url: $url);
    }

    public function cetakTerpilih()
    {
        if (empty($this->selectedStudents)) {
            session()->flash('error', 'Tidak ada siswa yang dipilih.');
            return;
        }

        $ids = implode(',', $this->selectedStudents);
        $url = route('siswa.cetak-kartu-login-massal', ['ids' => $ids]);
        $this->dispatch('open-url', url: $url);
    }

    public function cetakSemua()
    {
        $records = $this->getStudentsQuery()->get();
        if ($records->isEmpty()) {
            session()->flash('error', 'Tidak ada data siswa untuk kelas ini.');
            return;
        }

        $ids = $records->pluck('id')->implode(',');
        $url = route('siswa.cetak-kartu-login-massal', ['ids' => $ids]);
        $this->dispatch('open-url', url: $url);
    }

    #[Layout('components.layouts.portal')]
    public function render()
    {
        return view('livewire.portal-presensi.cetak-kartu-siswa', [
            'students' => $this->students
        ])->title('Cetak Kartu Siswa - Portal Absensi');
    }
}
