<?php

namespace App\Livewire\PortalGuru\Bk;

use App\Models\EnrollmentSiswa;
use App\Models\Guru;
use App\Models\TahunAjaran;
use App\Models\KonselingBk;
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
        $this->selectedRujukan = KonselingBk::with(['siswa', 'guru', 'jurnalGuruWali.guru', 'konsultasiGuruWali.guru'])
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

    public function terimaRujukan($id)
    {
        $rujukan = KonselingBk::findOrFail($id);
        
        $teacherId = Auth::user()?->teacher?->id;
        if (!$teacherId) {
            session()->flash('error', 'Akun ini tidak terhubung dengan profil Guru.');
            return;
        }

        $rujukan->update([
            'teacher_id' => $teacherId,
            'status_kasus' => \App\Enums\StatusKasus::DalamProses,
        ]);

        session()->flash('success', 'Rujukan berhasil diterima dan akan ditangani oleh Anda.');
    }

    public function render()
    {
        // Kueri rujukan dari KonselingBk dengan is_rujukan = true
        $query = KonselingBk::with(['siswa', 'guru', 'jurnalGuruWali.guru', 'konsultasiGuruWali.guru'])
            ->where('is_rujukan', true);

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
            $query->where(function ($q) use ($searchTerm) {
                $q->whereHas('siswa', function ($sq) use ($searchTerm) {
                    $sq->where('name', 'like', $searchTerm)
                        ->orWhere('nisn', 'like', $searchTerm)
                        ->orWhere('nis', 'like', $searchTerm);
                })->orWhere('alasan_rujukan', 'like', $searchTerm);
            });
        }

        // Filter status tindak lanjut BK
        if ($this->filterStatus === 'menunggu') {
            $query->whereNull('teacher_id');
        } elseif ($this->filterStatus === 'proses') {
            $query->whereNotNull('teacher_id')->where('status_kasus', \App\Enums\StatusKasus::DalamProses);
        } elseif ($this->filterStatus === 'selesai') {
            $query->whereNotNull('teacher_id')->whereIn('status_kasus', [\App\Enums\StatusKasus::Selesai, \App\Enums\StatusKasus::DikonversiKeJurnal]);
        }

        $rujukans = $query->latest('created_at')->paginate(10);

        // Ringkasan metrik
        $baseCountQuery = KonselingBk::where('is_rujukan', true);
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
        $totalMenunggu = (clone $baseCountQuery)->whereNull('teacher_id')->count();
        $totalProses = (clone $baseCountQuery)->whereNotNull('teacher_id')->where('status_kasus', \App\Enums\StatusKasus::DalamProses)->count();
        $totalSelesai = (clone $baseCountQuery)->whereNotNull('teacher_id')->whereIn('status_kasus', [\App\Enums\StatusKasus::Selesai, \App\Enums\StatusKasus::DikonversiKeJurnal])->count();

        return view('livewire.portal-guru.bk.rujukan-kasus-list', [
            'rujukans'      => $rujukans,
            'totalRujukan'  => $totalRujukan,
            'totalMenunggu' => $totalMenunggu,
            'totalProses'   => $totalProses,
            'totalSelesai'  => $totalSelesai,
        ]);
    }
}
