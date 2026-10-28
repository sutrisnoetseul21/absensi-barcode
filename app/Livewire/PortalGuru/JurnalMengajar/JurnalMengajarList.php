<?php

namespace App\Livewire\PortalGuru\JurnalMengajar;

use App\Models\Guru;
use App\Models\JurnalPembelajaran;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\Pengajaran;
use App\Models\PengaturanSekolah;
use App\Models\TahunAjaran;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.portal')]
class JurnalMengajarList extends Component
{
    use WithPagination;

    public bool $isSupervisor = false;
    public ?string $currentTeacherId = null;

    // Filters
    public $academicYears = [];
    public ?string $selectedAcademicYearId = null;

    public $teachers = [];
    public ?string $selectedTeacherId = null;

    public $mapels = [];
    public ?int $selectedMapelId = null;

    public $classes = [];
    public ?string $selectedClassId = null;

    public string $search = '';
    public ?string $dateFrom = null;
    public ?string $dateTo = null;

    protected $paginationTheme = 'tailwind';

    public function mount()
    {
        $user = Auth::user();
        if (!$user) {
            abort(403, 'Akses ditolak: Silakan login terlebih dahulu.');
        }

        $this->isSupervisor = $user->isSuperAdmin() || $user->hasPermissionTo('portal_guru:supervisi_jurnal');

        if (!$this->isSupervisor && !$user->teacher) {
            abort(403, 'Akses ditolak: Anda tidak memiliki profil guru.');
        }

        $this->currentTeacherId = $user->teacher?->id;

        // Tahun Ajaran
        $this->academicYears = TahunAjaran::orderBy('start_year', 'desc')->get();
        $activeYear = PengaturanSekolah::current()?->academic_year_id_active 
            ?? TahunAjaran::where('status', 'aktif')->first()?->id 
            ?? $this->academicYears->first()?->id;
        $this->selectedAcademicYearId = $activeYear;

        if ($this->isSupervisor) {
            $this->teachers = Guru::orderBy('name', 'asc')->get();
            // Default ke guru login jika ada, atau guru pertama
            $this->selectedTeacherId = $this->currentTeacherId ?? $this->teachers->first()?->id;
        } else {
            $this->selectedTeacherId = $this->currentTeacherId;
        }

        $this->loadMapelAndClasses();
    }

    public function updatedSelectedAcademicYearId()
    {
        $this->reset(['selectedMapelId', 'selectedClassId']);
        $this->loadMapelAndClasses();
        $this->resetPage();
    }

    public function updatedSelectedTeacherId()
    {
        $this->reset(['selectedMapelId', 'selectedClassId']);
        $this->loadMapelAndClasses();
        $this->resetPage();
    }

    public function updatedSelectedMapelId()
    {
        $this->reset(['selectedClassId']);
        $this->loadClassesForSelectedMapel();
        $this->resetPage();
    }

    public function updatedSelectedClassId()
    {
        $this->resetPage();
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedDateFrom()
    {
        $this->resetPage();
    }

    public function updatedDateTo()
    {
        $this->resetPage();
    }

    public function loadMapelAndClasses()
    {
        if (!$this->selectedTeacherId || !$this->selectedAcademicYearId) {
            $this->mapels = [];
            $this->classes = [];
            return;
        }

        // Ambil mapel dari penugasan pengajaran tahun ajaran aktif
        $pengajarans = Pengajaran::where('teacher_id', $this->selectedTeacherId)
            ->whereHas('kelasAjaran', fn($q) => $q->where('academic_year_id', $this->selectedAcademicYearId))
            ->with(['mataPelajaran', 'kelasAjaran.kelas'])
            ->get();

        $this->mapels = $pengajarans->pluck('mataPelajaran')->unique('id')->filter()->values();

        // Jika hanya 1 mapel, otomatis pilih
        if ($this->mapels->count() === 1) {
            $this->selectedMapelId = $this->mapels->first()->id;
            $this->loadClassesForSelectedMapel();
        } else {
            $this->loadClassesForSelectedMapel();
        }
    }

    public function loadClassesForSelectedMapel()
    {
        if (!$this->selectedTeacherId || !$this->selectedAcademicYearId) {
            $this->classes = [];
            return;
        }

        $query = Pengajaran::where('teacher_id', $this->selectedTeacherId)
            ->whereHas('kelasAjaran', fn($q) => $q->where('academic_year_id', $this->selectedAcademicYearId));

        if ($this->selectedMapelId) {
            $query->where('mata_pelajaran_id', $this->selectedMapelId);
        }

        $pengajarans = $query->with('kelasAjaran.kelas')->get();
        $this->classes = $pengajarans->pluck('kelasAjaran.kelas')->unique('id')->filter()->values();
    }

    public function delete(string $id)
    {
        $jurnal = JurnalPembelajaran::findOrFail($id);
        $user = Auth::user();

        // Guru biasa hanya boleh menghapus jurnal miliknya sendiri
        if (!$user->isSuperAdmin() && $jurnal->teacher_id !== $this->currentTeacherId) {
            session()->flash('error', 'Anda tidak memiliki hak untuk menghapus jurnal guru lain.');
            return;
        }

        $jurnal->delete();
        session()->flash('success', 'Jurnal mengajar berhasil dihapus.');
    }

    public function render()
    {
        $query = JurnalPembelajaran::query()
            ->with(['guru', 'kelas', 'mataPelajaran', 'tahunAjaran'])
            ->when($this->selectedAcademicYearId, fn($q) => $q->where('academic_year_id', $this->selectedAcademicYearId))
            ->when($this->selectedTeacherId, fn($q) => $q->where('teacher_id', $this->selectedTeacherId))
            ->when($this->selectedMapelId, fn($q) => $q->where('mata_pelajaran_id', $this->selectedMapelId))
            ->when($this->selectedClassId, fn($q) => $q->where('class_id', $this->selectedClassId))
            ->when($this->dateFrom, fn($q) => $q->whereDate('tanggal', '>=', $this->dateFrom))
            ->when($this->dateTo, fn($q) => $q->whereDate('tanggal', '<=', $this->dateTo))
            ->when($this->search, function ($q) {
                $q->where(function ($sub) {
                    $sub->where('materi', 'like', '%' . $this->search . '%')
                        ->orWhere('uraian_materi', 'like', '%' . $this->search . '%')
                        ->orWhere('refleksi', 'like', '%' . $this->search . '%')
                        ->orWhere('jam_ke_label', 'like', '%' . $this->search . '%');
                });
            })
            ->orderBy('tanggal', 'desc')
            ->orderBy('jam_mulai', 'desc');

        // Statistik ringkas untuk guru terpilih
        $statsQuery = clone $query;
        $totalJurnal = $statsQuery->count();
        $totalJp = (clone $statsQuery)->sum('durasi_jp');
        $totalSiswaTercatat = (clone $statsQuery)->sum('total_siswa');
        $totalSiswaHadir = (clone $statsQuery)->sum('total_hadir');
        $avgKehadiran = $totalSiswaTercatat > 0 ? round(($totalSiswaHadir / $totalSiswaTercatat) * 100, 1) : 0;

        $jurnals = $query->paginate(10);

        return view('livewire.portal-guru.jurnal-mengajar.jurnal-mengajar-list', [
            'jurnals'       => $jurnals,
            'totalJurnal'   => $totalJurnal,
            'totalJp'       => $totalJp,
            'avgKehadiran'  => $avgKehadiran,
        ]);
    }
}
