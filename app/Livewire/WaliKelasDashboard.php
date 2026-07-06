<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Kelas;
use App\Models\TahunAjaran;
use App\Models\Presensi;
use App\Services\PresensiRekapService;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class WaliKelasDashboard extends Component
{
    public $classes = [];
    public $selectedClassId;
    public $selectedMonth;
    public $academicYears = [];
    public $selectedAcademicYearId;

    public $students = [];
    public $monthlyStats = [];
    public $alerts = [];
    public $todayStats = [];

    public $daysInMonth = 0;
    public $todayDate;

    // Manual Input Modals
    public $showInputModal = false;
    public $inputDate;
    public $inputStudents = [];

    public function mount()
    {
        $this->selectedMonth  = date('m');
        $this->academicYears  = TahunAjaran::orderBy('start_year', 'desc')->get();

        $activeYear = TahunAjaran::where('status', 'aktif')->first() ?? $this->academicYears->first();
        if ($activeYear) {
            $this->selectedAcademicYearId = $activeYear->id;
        }

        $this->loadClasses();
    }

    public function loadClasses()
    {
        if (!$this->selectedAcademicYearId) {
            $this->classes        = [];
            $this->selectedClassId = null;
            return;
        }

        $isAdminMode = request()->is('admin*') || request()->routeIs('filament.*') || !Auth::guard('wali_kelas')->check();

        if (!$isAdminMode) {
            $actor        = Auth::guard('wali_kelas')->user();
            $this->classes = Kelas::whereHas('kelasAjarans', function ($query) use ($actor) {
                $query->where('academic_year_id', $this->selectedAcademicYearId)
                      ->where('teacher_id', $actor->id);
            })->get();
        } else {
            $this->classes = Kelas::orderBy('name', 'asc')->get();
        }

        if ($this->classes->isNotEmpty()) {
            if (!collect($this->classes)->contains('id', $this->selectedClassId)) {
                $this->selectedClassId = collect($this->classes)->first()->id;
            }
        } else {
            $this->selectedClassId = null;
            $this->todayStats      = [];
        }

        $this->loadDashboardData();
    }

    public function updatedSelectedAcademicYearId()
    {
        $this->loadClasses();
    }

    public function updatedSelectedClassId()
    {
        $isAdminMode = request()->is('admin*') || request()->routeIs('filament.*') || !Auth::guard('wali_kelas')->check();
        if (!$isAdminMode) {
            if (!collect($this->classes)->contains('id', $this->selectedClassId)) {
                abort(403, 'Unauthorized action.');
            }
        }
        $this->loadDashboardData();
    }

    public function updatedSelectedMonth()
    {
        $this->loadDashboardData();
    }

    public function loadDashboardData()
    {
        if (!$this->selectedClassId || !$this->selectedAcademicYearId) {
            $this->students     = collect();
            $this->monthlyStats = [];
            $this->alerts       = [];
            $this->todayStats   = [];
            return;
        }

        $service = app(PresensiRekapService::class);
        $result  = $service->getMonthlyCalendarData(
            $this->selectedAcademicYearId,
            $this->selectedClassId,
            $this->selectedMonth
        );

        $this->students     = $result['students'];
        $this->monthlyStats = $result['monthlyStats'];
        $this->todayStats   = $result['todayStats'];
        $this->alerts       = $result['alerts'];
        $this->daysInMonth  = $result['daysInMonth'];
        $this->todayDate    = $result['todayDate'];
    }

    public function openInputModal()
    {
        $this->inputDate = $this->todayDate ?? Carbon::now('Asia/Jakarta')->toDateString();
        $this->loadStudentsForInput();
        $this->showInputModal = true;
    }

    public function updatedInputDate()
    {
        $this->loadStudentsForInput();
    }

    public function loadStudentsForInput()
    {
        if (!$this->selectedClassId || !$this->selectedAcademicYearId || !$this->inputDate) return;

        $attendances = Presensi::where('academic_year_id', $this->selectedAcademicYearId)
            ->where('class_id', $this->selectedClassId)
            ->where('date', $this->inputDate)
            ->get()->keyBy('student_id');

        $list = [];
        foreach ($this->students as $student) {
            $att            = $attendances->get($student->id);
            $list[$student->id] = [
                'id'          => $student->id,
                'name'        => $student->name,
                'status'      => $att ? $att->status : '',
                'late_minutes' => $att ? $att->late_minutes : null,
            ];
        }
        $this->inputStudents = $list;
    }

    public function saveManualInput()
    {
        if (!$this->selectedClassId || !$this->selectedAcademicYearId || !$this->inputDate) return;

        foreach ($this->inputStudents as $studentId => $data) {
            if (empty($data['status'])) continue;

            // Cari enrollment_id
            $enrollment = \App\Models\EnrollmentSiswa::where('student_id', $studentId)
                ->where('academic_year_id', $this->selectedAcademicYearId)
                ->where('status', 'aktif')
                ->first();

            Presensi::updateOrCreate(
                [
                    'student_id'       => $studentId,
                    'class_id'         => $this->selectedClassId,
                    'academic_year_id' => $this->selectedAcademicYearId,
                    'date'             => $this->inputDate,
                ],
                [
                    'enrollment_id'   => $enrollment?->id,
                    'status'          => $data['status'],
                    'late_minutes'    => ($data['status'] === 'telat') ? ($data['late_minutes'] ?: 0) : null,
                    'is_manual_input' => true,
                    'manual_input_by_id'   => Auth::guard('wali_kelas')->id() ?? Auth::guard('web')->id(),
                    'manual_input_by_type' => Auth::guard('wali_kelas')->check() ? \App\Models\Guru::class : \App\Models\User::class,
                ]
            );
        }

        $this->showInputModal = false;
        $this->loadDashboardData();

        $this->dispatch('notify', [
            'type'    => 'success',
            'message' => 'Absensi tanggal ' . $this->inputDate . ' berhasil disimpan!',
        ]);
    }

    public function render()
    {
        return view('livewire.wali-kelas-dashboard');
    }
}
