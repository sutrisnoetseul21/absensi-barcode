<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Kelas;
use App\Models\TahunAjaran;
use App\Models\Presensi;
use App\Services\PresensiRekapService;
use App\Models\PengaturanSekolah;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Exports\PresensiMatrixExport;
use App\Exports\LaporanPresensiRangeExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

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

    // Cetak Laporan Modals
    public $showCetakModal = false;
    public $cetakJenis = 'bulanan';
    public $cetakBulanYear = '';
    public $cetakSemester = 'ganjil';

    public function mount()
    {
        $this->academicYears  = TahunAjaran::where('status', 'aktif')->orderBy('start_year', 'desc')->get();

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
                'message' => 'Tidak dapat menyimpan presensi pada hari libur!',
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
            'message' => 'Presensi tanggal ' . $this->inputDate . ' berhasil disimpan!',
        ]);
    }

    public function openCetakModal()
    {
        if (!$this->selectedClassId || !$this->selectedAcademicYearId) {
            $this->dispatch('notify', [
                'type'    => 'error',
                'message' => 'Silakan pilih kelas dan tahun ajaran terlebih dahulu.',
            ]);
            return;
        }

        $this->cetakBulanYear = $this->selectedMonthYear ?? date('m-Y');
        $this->cetakSemester = (int)date('m') >= 7 ? 'ganjil' : 'genap';
        $this->cetakJenis = 'bulanan';

        $this->showCetakModal = true;
    }

    private function getCetakDateRange()
    {
        $ay = TahunAjaran::find($this->selectedAcademicYearId);
        if (!$ay) {
            return ['start' => null, 'end' => null, 'label' => ''];
        }

        if ($this->cetakJenis === 'bulanan') {
            $parts = explode('-', $this->cetakBulanYear);
            $month = (int)($parts[0] ?? date('m'));
            $year = (int)($parts[1] ?? date('Y'));
            
            $start = Carbon::create($year, $month, 1)->startOfMonth();
            $end   = Carbon::create($year, $month, 1)->endOfMonth();
            $monthNames = [
                1=>'Januari', 2=>'Februari', 3=>'Maret', 4=>'April',
                5=>'Mei', 6=>'Juni', 7=>'Juli', 8=>'Agustus',
                9=>'September', 10=>'Oktober', 11=>'November', 12=>'Desember',
            ];
            $label = 'Bulan ' . ($monthNames[$month] ?? $month) . ' ' . $year;
        } elseif ($this->cetakJenis === 'semester') {
            if ($this->cetakSemester === 'ganjil') {
                $start = Carbon::create($ay->start_year, 7, 1)->startOfMonth();
                $end   = Carbon::create($ay->start_year, 12, 31)->endOfMonth();
                $label = 'Semester Ganjil TA ' . $ay->name;
            } else {
                $start = Carbon::create($ay->end_year, 1, 1)->startOfMonth();
                $end   = Carbon::create($ay->end_year, 6, 30)->endOfMonth();
                $label = 'Semester Genap TA ' . $ay->name;
            }
        } else {
            $start = Carbon::create($ay->start_year, 7, 1)->startOfMonth();
            $end   = Carbon::create($ay->end_year, 6, 30)->endOfMonth();
            $label = 'Tahun Ajaran ' . $ay->name;
        }

        return [
            'start' => $start->toDateString(),
            'end'   => $end->toDateString(),
            'label' => $label,
        ];
    }

    public function downloadCetakExcel()
    {
        if (!$this->selectedClassId || !$this->selectedAcademicYearId) return;

        $range = $this->getCetakDateRange();
        $className = Kelas::find($this->selectedClassId)?->name ?? 'Kelas';
        $safeClassName = str_replace(['/', '\\'], '-', $className);
        $safeLabel = str_replace([' ', '/', '\\'], ['_', '-', '-'], $range['label']);
        $fileName = 'Laporan_Presensi_' . $safeClassName . '_' . $safeLabel . '.xlsx';

        if ($this->cetakJenis === 'bulanan') {
            $parts = explode('-', $this->cetakBulanYear);
            $month = $parts[0] ?? date('m');
            $year = (int)($parts[1] ?? date('Y'));
            
            return Excel::download(
                new PresensiMatrixExport(
                    $this->selectedClassId,
                    $this->selectedAcademicYearId,
                    $month,
                    $year,
                    $range['label']
                ),
                $fileName
            );
        }

        return Excel::download(
            new LaporanPresensiRangeExport(
                $this->selectedClassId,
                $this->selectedAcademicYearId,
                $range['start'],
                $range['end'],
                $range['label']
            ),
            $fileName
        );
    }

    public function downloadCetakPdf()
    {
        if (!$this->selectedClassId || !$this->selectedAcademicYearId) return;

        $range   = $this->getCetakDateRange();
        $kelas   = Kelas::find($this->selectedClassId);
        $sekolah = PengaturanSekolah::current();
        
        $className = $kelas?->name ?? 'Kelas';
        $safeClassName = str_replace(['/', '\\'], '-', $className);
        $safeLabel = str_replace([' ', '/', '\\'], ['_', '-', '-'], $range['label']);
        $fileName  = 'Laporan_Presensi_' . $safeClassName . '_' . $safeLabel . '.pdf';

        if ($this->cetakJenis === 'bulanan') {
            $parts = explode('-', $this->cetakBulanYear);
            $month = $parts[0] ?? date('m');
            $year = (int)($parts[1] ?? date('Y'));
            
            $service = app(PresensiRekapService::class);
            $result = $service->getMonthlyCalendarData(
                $this->selectedAcademicYearId,
                $this->selectedClassId,
                $month,
                $year
            );
            
            $pdf = Pdf::loadView('pdf.laporan-presensi-matrix', [
                'students'      => $result['students'],
                'monthlyStats'  => $result['monthlyStats'],
                'daysInMonth'   => $result['daysInMonth'],
                'periodeLabel'  => $range['label'],
                'kelas'         => $kelas,
                'sekolah'       => $sekolah,
                'generatedAt'   => now()->locale('id')->translatedFormat('l, d F Y H:i'),
            ])->setPaper('a4', 'landscape');

            return response()->streamDownload(
                fn() => print($pdf->output()),
                $fileName
            );
        }

        $service = app(PresensiRekapService::class);
        $result = $service->getStudentSemesterYearlyData(
            $this->selectedAcademicYearId,
            $this->selectedClassId,
            $range['start'],
            $range['end']
        );

        $pdf = Pdf::loadView('pdf.laporan-presensi-range', [
            'studentsData'  => $result['studentsData'],
            'monthsList'    => $result['monthsList'],
            'jenisLaporan'  => $this->cetakJenis,
            'periodeLabel'  => $range['label'],
            'kelas'         => $kelas,
            'sekolah'       => $sekolah,
            'generatedAt'   => now()->locale('id')->translatedFormat('l, d F Y H:i'),
        ])->setPaper('a4', 'landscape');

        return response()->streamDownload(
            fn() => print($pdf->output()),
            $fileName
        );
    }

    public function render()
    {
        return view('livewire.wali-kelas-dashboard');
    }
}
