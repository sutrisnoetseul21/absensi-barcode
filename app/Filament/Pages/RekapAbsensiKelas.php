<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Notifications\Notification;
use App\Models\Kelas;
use App\Models\TahunAjaran;
use App\Models\Presensi;
use App\Models\EnrollmentSiswa;
use App\Services\PresensiRekapService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class RekapAbsensiKelas extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-text';
    protected string $view = 'filament.pages.rekap-absensi-kelas';
    protected static string|\UnitEnum|null $navigationGroup = 'Laporan';
    protected static ?string $title = 'Rekap Absensi Kelas';
    protected static ?string $navigationLabel = 'Rekap Absensi Kelas';

    // Filters
    public $academicYears   = [];
    public $selectedAcademicYearId;
    public $classes         = [];
    public $selectedClassId;
    public $selectedMonth;

    // Data
    public $students        = [];
    public $monthlyStats    = [];
    public $alerts          = [];
    public $todayStats      = [];
    public $daysInMonth     = 0;
    public $todayDate;

    // Modal Input Manual
    public $showInputModal  = false;
    public $inputDate;
    public $inputStudents   = [];

    public function mount(): void
    {
        $this->selectedMonth  = date('m');
        $this->academicYears  = TahunAjaran::orderBy('start_year', 'desc')->get();

        $activeYear = TahunAjaran::where('status', 'aktif')->first() ?? $this->academicYears->first();
        if ($activeYear) {
            $this->selectedAcademicYearId = $activeYear->id;
        }

        $this->loadClasses();
    }

    public function loadClasses(): void
    {
        if (!$this->selectedAcademicYearId) {
            $this->classes        = collect();
            $this->selectedClassId = null;
            return;
        }

        // Admin melihat semua kelas
        $this->classes = Kelas::orderBy('name', 'asc')->get();

        if ($this->classes->isNotEmpty()) {
            if (!$this->classes->contains('id', $this->selectedClassId)) {
                $this->selectedClassId = $this->classes->first()->id;
            }
        } else {
            $this->selectedClassId = null;
        }

        $this->loadData();
    }

    public function updatedSelectedAcademicYearId(): void
    {
        $this->loadClasses();
    }

    public function updatedSelectedClassId(): void
    {
        $this->loadData();
    }

    public function updatedSelectedMonth(): void
    {
        $this->loadData();
    }

    public function loadData(): void
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

    public function exportExcel()
    {
        if (!$this->selectedClassId || !$this->selectedAcademicYearId || !$this->selectedMonth) {
            Notification::make()->title('Gagal Export')->body('Pilih Tahun Ajaran, Kelas, dan Bulan terlebih dahulu.')->danger()->send();
            return;
        }

        $className = Kelas::find($this->selectedClassId)?->name ?? 'Kelas';
        $year = date('Y');
        
        // Coba deduksi tahun dari bulan (jika bulan > 6 biasanya tahun awal ajaran, jika <= 6 tahun akhir ajaran)
        $tahunAjaran = TahunAjaran::find($this->selectedAcademicYearId);
        if ($tahunAjaran) {
            $year = (int)$this->selectedMonth >= 7 ? $tahunAjaran->start_year : $tahunAjaran->end_year;
        }

        $fileName = "Rekap_Presensi_{$className}_{$year}_{$this->selectedMonth}.xlsx";
        
        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\PresensiMatrixExport(
                $this->selectedClassId, 
                $this->selectedAcademicYearId, 
                $this->selectedMonth, 
                (string)$year
            ), 
            $fileName
        );
    }

    // === Modal Input Manual ===

    public function openInputModal(): void
    {
        $this->inputDate = $this->todayDate ?? Carbon::now('Asia/Jakarta')->toDateString();
        $this->loadStudentsForInput();
        $this->showInputModal = true;
    }

    public function closeInputModal(): void
    {
        $this->showInputModal = false;
    }

    public function updatedInputDate(): void
    {
        $this->loadStudentsForInput();
    }

    public function loadStudentsForInput(): void
    {
        if (!$this->selectedClassId || !$this->selectedAcademicYearId || !$this->inputDate) return;

        $attendances = Presensi::where('academic_year_id', $this->selectedAcademicYearId)
            ->where('class_id', $this->selectedClassId)
            ->where('date', $this->inputDate)
            ->get()->keyBy('student_id');

        $list = [];
        foreach ($this->students as $student) {
            $att = $attendances->get($student->id);
            $list[$student->id] = [
                'id'           => $student->id,
                'name'         => $student->name,
                'status'       => $att ? $att->status : '',
                'late_minutes' => $att ? $att->late_minutes : null,
            ];
        }
        $this->inputStudents = $list;
    }

    public function saveManualInput(): void
    {
        if (!$this->selectedClassId || !$this->selectedAcademicYearId || !$this->inputDate) return;

        $savedCount = 0;
        foreach ($this->inputStudents as $studentId => $data) {
            if (empty($data['status'])) continue;

            $enrollment = EnrollmentSiswa::where('student_id', $studentId)
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
                    'enrollment_id'        => $enrollment?->id,
                    'status'               => $data['status'],
                    'late_minutes'         => ($data['status'] === 'telat') ? ($data['late_minutes'] ?: 0) : 0,
                    'is_manual_input'      => true,
                    'manual_input_by_id'   => Auth::id(),
                    'manual_input_by_type' => \App\Models\User::class,
                ]
            );
            $savedCount++;
        }

        $this->showInputModal = false;
        $this->loadData();

        Notification::make()
            ->title('Absensi berhasil disimpan')
            ->body("{$savedCount} siswa diperbarui untuk tanggal {$this->inputDate}.")
            ->success()
            ->send();
    }
}
