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
    public $selectedMonthYear;
    public $availableMonths = [];
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
        $this->academicYears  = TahunAjaran::orderBy('start_year', 'desc')->get();

        $activeYear = TahunAjaran::where('status', 'aktif')->first() ?? $this->academicYears->first();
        if ($activeYear) {
            $this->selectedAcademicYearId = $activeYear->id;
        }

        $this->loadClasses();
    }
    
    private function generateAvailableMonths()
    {
        $this->availableMonths = [];
        if (!$this->selectedAcademicYearId) return;
        
        $ay = TahunAjaran::find($this->selectedAcademicYearId);
        if ($ay) {
            $sy = $ay->start_year;
            $ey = $ay->end_year;
            
            $monthNames = [
                1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
            ];
            
            // Juli - Desember (Start Year)
            for ($m = 7; $m <= 12; $m++) {
                $key = sprintf('%02d-%d', $m, $sy);
                $this->availableMonths[$key] = $monthNames[$m] . ' ' . $sy;
            }
            // Januari - Juni (End Year)
            for ($m = 1; $m <= 6; $m++) {
                $key = sprintf('%02d-%d', $m, $ey);
                $this->availableMonths[$key] = $monthNames[$m] . ' ' . $ey;
            }
        }
        
        $currentMonthYear = date('m-Y');
        if (array_key_exists($currentMonthYear, $this->availableMonths)) {
            $this->selectedMonthYear = $currentMonthYear;
        } else {
            $this->selectedMonthYear = array_key_first($this->availableMonths);
        }
    }

    public function loadClasses()
    {
        $this->generateAvailableMonths();
        
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

    public function updatedSelectedMonthYear()
    {
        $this->loadDashboardData();
    }

    public function loadDashboardData()
    {
        if (!$this->selectedClassId || !$this->selectedAcademicYearId || !$this->selectedMonthYear) {
            $this->students     = collect();
            $this->monthlyStats = [];
            $this->alerts       = [];
            $this->todayStats   = [];
            return;
        }
        
        $parts = explode('-', $this->selectedMonthYear);
        if (count($parts) !== 2) return;
        
        $month = $parts[0];
        $year = (int)$parts[1];

        $service = app(PresensiRekapService::class);
        $result  = $service->getMonthlyCalendarData(
            $this->selectedAcademicYearId,
            $this->selectedClassId,
            $month,
            $year
        );

        $this->students     = $result['students'];
        $this->monthlyStats = $result['monthlyStats'];
        $this->todayStats   = $result['todayStats'];
        $this->alerts       = $result['alerts'];
        $this->daysInMonth  = $result['daysInMonth'];
        $this->todayDate    = $result['todayDate'];
    }

    public $isInputDateHoliday = false;

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

        $kalenderService = app(\App\Services\KalenderSekolahService::class);
        $this->isInputDateHoliday = !$kalenderService->isHariSekolah(Carbon::parse($this->inputDate), $this->selectedClassId);

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

        if ($this->isInputDateHoliday) {
            $this->dispatch('notify', [
                'type'    => 'error',
                'message' => 'Tidak dapat menyimpan absensi pada hari libur!',
            ]);
            return;
        }

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
                    'late_minutes'    => ($data['status'] === 'telat') ? ($data['late_minutes'] ?: 0) : 0,
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
