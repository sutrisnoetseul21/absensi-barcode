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
    protected static ?string $title = 'Rekap Presensi Kelas';
    protected static ?string $navigationLabel = 'Rekap Presensi Kelas';

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

}
