<?php

namespace App\Livewire\PortalGuru;

use App\Models\Kelas;
use App\Models\NilaiUjian;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use App\Models\UjianAkademik;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use Livewire\Component;
use Livewire\WithPagination;

class DaftarNilai extends Component
{
    use WithPagination;

    public $academicYears = [];
    public $selectedAcademicYearId = null;

    public $events = [];
    public $selectedEventName = null;

    // Filter level ke-3: Nama Agenda Ujian (sub dari Event)
    public $agendas = [];
    public $selectedAgendaUjianId = null;

    public $classes = [];
    public $selectedClassId = null;

    public function mount()
    {
        $user = Auth::user();
        if (!$user || !$user->teacher) {
            abort(403, 'Akses ditolak: Anda tidak memiliki profil guru.');
        }

        $this->academicYears = TahunAjaran::orderBy('start_year', 'desc')->get();
        $activeYear = TahunAjaran::where('status', 'aktif')->first() ?? $this->academicYears->first();
        if ($activeYear) {
            $this->selectedAcademicYearId = $activeYear->id;
        }

        $this->loadFilters();
    }

    public function updatedSelectedAcademicYearId()
    {
        $this->reset(['selectedEventName', 'selectedAgendaUjianId', 'selectedClassId']);
        $this->loadFilters();
        $this->resetPage();
    }

    public function updatedSelectedEventName()
    {
        $this->reset(['selectedAgendaUjianId', 'selectedClassId']);
        $this->loadFilters();
        $this->resetPage();
    }

    public function updatedSelectedAgendaUjianId()
    {
        $this->reset(['selectedClassId']);
        $this->loadFilters();
        $this->resetPage();
    }

    public function updatedSelectedClassId()
    {
        $this->resetPage();
    }

    private function getBaseQuery()
    {
        $teacherId = Auth::user()->teacher->id;
        $mapelIds = \App\Models\Pengajaran::where('teacher_id', $teacherId)
            ->pluck('mata_pelajaran_id')
            ->toArray();

        return [
            'teacherId' => $teacherId,
            'mapelIds'  => $mapelIds,
            'query'     => UjianAkademik::where(function ($q) use ($teacherId, $mapelIds) {
                $q->where('teacher_id', $teacherId)
                  ->orWhereIn('mata_pelajaran_id', $mapelIds);
            })->when($this->selectedAcademicYearId, fn($q) => $q->where('academic_year_id', $this->selectedAcademicYearId)),
        ];
    }

    public function loadFilters()
    {
        $teacherId = Auth::user()->teacher->id;

        // Ambil data mapel dan kelas yang diajarkan guru ini dari tabel Pengajaran
        $pengajarans = \App\Models\Pengajaran::where('teacher_id', $teacherId)
            ->with('kelasAjaran')
            ->get();

        $mapelClasses = [];
        foreach ($pengajarans as $p) {
            if ($p->kelasAjaran) {
                $mapelClasses[$p->mata_pelajaran_id][] = $p->kelasAjaran->class_id;
            }
        }

        $mapelIds = array_keys($mapelClasses);

        $baseQuery = UjianAkademik::where(function ($q) use ($teacherId, $mapelIds) {
                $q->where('teacher_id', $teacherId)
                  ->orWhereIn('mata_pelajaran_id', $mapelIds);
            })
            ->when($this->selectedAcademicYearId, fn($q) => $q->where('academic_year_id', $this->selectedAcademicYearId));

        // ── Level 1: Nama Event CBT ──────────────────────────────────────────
        $this->events = (clone $baseQuery)->whereNotNull('cbt_event_nama')
            ->select('cbt_event_nama')
            ->distinct()
            ->orderBy('cbt_event_nama')
            ->pluck('cbt_event_nama')
            ->toArray();

        if (!in_array($this->selectedEventName, $this->events)) {
            $this->selectedEventName = null;
        }

        // ── Level 2: Nama Agenda Ujian (sub dari event yang sama) ───────────
        $agendaQuery = clone $baseQuery;
        if ($this->selectedEventName) {
            $agendaQuery->where('cbt_event_nama', $this->selectedEventName);
        }
        $this->agendas = $agendaQuery->orderBy('nama_ujian')->get(['id', 'nama_ujian']);

        if (!$this->agendas->contains('id', $this->selectedAgendaUjianId)) {
            $this->selectedAgendaUjianId = null;
        }

        // ── Level 3: Kelas ───────────────────────────────────────────────────
        $allowedClassIds = [];
        if ($this->selectedAgendaUjianId) {
            // Sudah tahu ujian spesifiknya — ambil kelas dari pivot + pengajaran mapel itu
            $ujian = $this->agendas->firstWhere('id', $this->selectedAgendaUjianId);
            if ($ujian) {
                $explicitClassIds = UjianAkademik::find($this->selectedAgendaUjianId)?->classes()->pluck('classes.id')->toArray() ?? [];
                $implicitClassIds = $mapelClasses[UjianAkademik::find($this->selectedAgendaUjianId)?->mata_pelajaran_id] ?? [];
                $allowedClassIds = array_unique(array_merge($explicitClassIds, $implicitClassIds));
            }
        } else {
            // Belum pilih agenda — gabungkan semua kelas dari event yang terfilter
            $ujians = $agendaQuery->get();
            foreach ($ujians as $ujian) {
                $explicitClassIds = $ujian->classes()->pluck('classes.id')->toArray();
                $implicitClassIds = $mapelClasses[$ujian->mata_pelajaran_id] ?? [];
                $allowedClassIds = array_unique(array_merge($allowedClassIds, $explicitClassIds, $implicitClassIds));
            }
        }

        $this->classes = Kelas::whereIn('id', $allowedClassIds)->orderBy('name')->get();

        if (!$this->classes->contains('id', $this->selectedClassId)) {
            $this->selectedClassId = null;
        }
    }

    public function getActiveUjianProperty()
    {
        // Harus ada minimal: Tahun Ajaran + Nama Agenda (ujian spesifik) + Kelas
        if (!$this->selectedAcademicYearId || !$this->selectedAgendaUjianId || !$this->selectedClassId) {
            return null;
        }

        $teacherId = Auth::user()->teacher->id;
        $mapelIds = \App\Models\Pengajaran::where('teacher_id', $teacherId)->pluck('mata_pelajaran_id')->toArray();

        // Langsung fetch ujian spesifik berdasarkan ID yang dipilih — tidak perlu tebak-tebak
        return UjianAkademik::where('id', $this->selectedAgendaUjianId)
            ->where(function ($q) use ($teacherId, $mapelIds) {
                $q->where('teacher_id', $teacherId)
                  ->orWhereIn('mata_pelajaran_id', $mapelIds);
            })
            ->first();
    }

    public function pullNilaiDariZenCBT()
    {
        $activeUjian = $this->activeUjian;
        if (!$activeUjian) {
            session()->flash('error', 'Silakan pilih ujian dan kelas terlebih dahulu.');
            return;
        }

        $teacherId = Auth::user()->teacher->id;
        $isTeacher = $activeUjian->teacher_id === $teacherId;
        $isSubjectTeacher = \App\Models\Pengajaran::where('teacher_id', $teacherId)
            ->where('mata_pelajaran_id', $activeUjian->mata_pelajaran_id)
            ->exists();

        if (!$isTeacher && !$isSubjectTeacher) {
            abort(403, 'Anda tidak memiliki hak akses untuk ujian ini.');
        }

        try {
            $cbtService = app(\App\Services\Cbt\CbtServiceInterface::class);
            $cbtExamId = (int) $activeUjian->cbt_ujian_id;

            if (!$cbtExamId) {
                throw new \Exception('ID Ujian ZenCBT tidak ditemukan pada data ujian ini.');
            }

            $results = $cbtService->pullExamResults($cbtExamId, $activeUjian);
            $synced = $results['synced'] ?? 0;
            $skipped = $results['skipped'] ?? 0;

            session()->flash('success', "Penarikan nilai selesai. {$synced} disinkronkan, {$skipped} dilewati.");
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal menarik nilai dari ZenCBT: ' . $e->getMessage());
        }
    }

    public function exportExcel()
    {
        $activeUjian = $this->activeUjian;
        if (!$activeUjian || !$this->selectedClassId) {
            session()->flash('error', 'Silakan pilih ujian dan kelas terlebih dahulu untuk export.');
            return;
        }

        $kelas = Kelas::find($this->selectedClassId);
        $className = $kelas ? str_replace(' ', '_', $kelas->name) : 'Kelas';
        $ujianName = str_replace([' ', '/', '\\'], '_', $activeUjian->nama_ujian);
        $fileName = "Rekap_Nilai_{$ujianName}_{$className}.xlsx";

        return Excel::download(
            new \App\Exports\DaftarNilaiExport($activeUjian->id, $this->selectedClassId, $this->selectedAcademicYearId, $activeUjian->kkm),
            $fileName
        );
    }

    public function getIsPublishedProperty()
    {
        $activeUjian = $this->activeUjian;
        if (!$activeUjian || !$this->selectedClassId) {
            return false;
        }

        $published = $activeUjian->published_classes ?? [];
        return in_array($this->selectedClassId, $published);
    }

    public function togglePublishClass()
    {
        $activeUjian = $this->activeUjian;
        if (!$activeUjian || !$this->selectedClassId) {
            return;
        }

        $published = $activeUjian->published_classes ?? [];

        if (in_array($this->selectedClassId, $published)) {
            // Unpublish
            $published = array_values(array_diff($published, [$this->selectedClassId]));
            $message = 'Nilai berhasil disembunyikan dari siswa kelas ini.';
        } else {
            // Publish
            $published[] = $this->selectedClassId;
            $message = 'Nilai berhasil ditampilkan ke siswa kelas ini.';
        }

        $activeUjian->published_classes = $published;
        $activeUjian->save();

        session()->flash('success', $message);
    }

    public function render()
    {
        $students = collect([]);
        $nilais = collect([]);
        $activeUjian = $this->activeUjian;

        if ($this->selectedClassId && $activeUjian) {
            $studentIds = \App\Models\EnrollmentSiswa::where('class_id', $this->selectedClassId)
                ->where('academic_year_id', $this->selectedAcademicYearId)
                ->where('status', 'aktif')
                ->pluck('student_id');

            $students = Siswa::whereIn('id', $studentIds)
                ->orderBy('name')
                ->paginate(30);

            $nilais = NilaiUjian::where('ujian_akademik_id', $activeUjian->id)
                ->whereIn('student_id', $students->pluck('id'))
                ->get()
                ->keyBy('student_id');
        }

        return view('livewire.portal-guru.daftar-nilai', [
            'students'    => $students,
            'nilais'      => $nilais,
            'activeUjian' => $activeUjian,
        ])->layout('components.layouts.portal', ['title' => 'Daftar Nilai']);
    }
}
