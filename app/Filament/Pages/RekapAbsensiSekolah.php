<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\TahunAjaran;
use App\Services\PresensiRekapService;

class RekapAbsensiSekolah extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-presentation-chart-bar';
    protected string $view = 'filament.pages.rekap-absensi-sekolah';
    protected static string|\UnitEnum|null $navigationGroup = 'Laporan';
    protected static ?string $title = 'Rekap Absensi Sekolah';
    protected static ?string $navigationLabel = 'Rekap Absensi Sekolah';

    public $academicYears = [];
    public $selectedAcademicYearId;
    public $classesData = [];
    public $monthsList = [];

    public function mount(): void
    {
        $this->academicYears = TahunAjaran::orderBy('start_year', 'desc')->get();

        $activeYear = TahunAjaran::where('status', 'aktif')->first() ?? $this->academicYears->first();
        if ($activeYear) {
            $this->selectedAcademicYearId = $activeYear->id;
        }

        $this->loadReportData();
    }

    public function updatedSelectedAcademicYearId(): void
    {
        $this->loadReportData();
    }

    public function loadReportData(): void
    {
        $this->classesData = [];
        $this->monthsList  = [];

        if (!$this->selectedAcademicYearId) return;

        $result = app(PresensiRekapService::class)
            ->getYearlySchoolData($this->selectedAcademicYearId);

        $this->classesData = $result['classesData'];
        $this->monthsList  = $result['monthsList'];
    }
}
