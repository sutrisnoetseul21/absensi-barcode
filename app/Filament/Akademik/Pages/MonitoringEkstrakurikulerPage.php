<?php

namespace App\Filament\Akademik\Pages;

use App\Exports\EkstrakurikulerRekapExport;
use App\Filament\Traits\HasSimplePageRoleAccess;
use App\Models\Ekstrakurikuler;
use App\Models\PengaturanSekolah;
use App\Models\TahunAjaran;
use App\Services\EkstrakurikulerRekapService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Filament\Pages\Page;
use Maatwebsite\Excel\Facades\Excel;

class MonitoringEkstrakurikulerPage extends Page
{
    use HasSimplePageRoleAccess;

    protected static function getModuleRolePrefix(): string
    {
        return 'akademik';
    }

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-presentation-chart-bar';
    protected static ?string $navigationLabel = 'Monitoring Ekstrakurikuler';
    protected static ?string $title = 'Monitoring Ekstrakurikuler, Pertemuan & Kehadiran';
    protected static ?string $slug = 'ekstrakurikuler/monitoring';
    protected static string|\UnitEnum|null $navigationGroup = 'Ekstrakurikuler';
    protected static ?int $navigationSort = 2;

    protected string $view = 'filament.akademik.pages.monitoring-ekstrakurikuler';

    public $academicYears = [];
    public ?string $selectedAcademicYearId = null;
    public ?string $startDate = null;
    public ?string $endDate = null;
    public ?string $detailEkskulId = null;

    public array $overview = ['rows' => [], 'totals' => ['total_ekskul' => 0, 'total_anggota' => 0, 'total_pertemuan' => 0, 'avg_pct' => 0]];
    public ?array $detailData = null;

    public function mount(): void
    {
        $this->academicYears = TahunAjaran::orderBy('start_year', 'desc')->get();
        $this->selectedAcademicYearId = PengaturanSekolah::current()?->academic_year_id_active
            ?? TahunAjaran::where('status', 'aktif')->value('id')
            ?? $this->academicYears->first()?->id;

        $this->loadOverview();
    }

    public function updatedSelectedAcademicYearId(): void
    {
        $this->detailEkskulId = null;
        $this->detailData = null;
        $this->loadOverview();
    }

    public function updatedStartDate(): void
    {
        $this->loadOverview();
        if ($this->detailEkskulId) {
            $this->inspectEkskul($this->detailEkskulId);
        }
    }

    public function updatedEndDate(): void
    {
        $this->loadOverview();
        if ($this->detailEkskulId) {
            $this->inspectEkskul($this->detailEkskulId);
        }
    }

    public function resetDateFilter(): void
    {
        $this->startDate = null;
        $this->endDate = null;
        $this->loadOverview();
        if ($this->detailEkskulId) {
            $this->inspectEkskul($this->detailEkskulId);
        }
    }

    public function loadOverview(): void
    {
        if (!$this->selectedAcademicYearId) {
            $this->overview = ['rows' => [], 'totals' => ['total_ekskul' => 0, 'total_anggota' => 0, 'total_pertemuan' => 0, 'avg_pct' => 0]];
            return;
        }

        $service = app(EkstrakurikulerRekapService::class);
        $this->overview = $service->getMonitoringOverview(
            $this->selectedAcademicYearId,
            $this->startDate ?: null,
            $this->endDate ?: null
        );
    }

    public function inspectEkskul(string $ekskulId): void
    {
        $ekskul = Ekstrakurikuler::find($ekskulId);
        if (!$ekskul) {
            $this->detailEkskulId = null;
            $this->detailData = null;
            return;
        }

        $this->detailEkskulId = $ekskulId;
        $service = app(EkstrakurikulerRekapService::class);
        $this->detailData = $service->buildEkskulReportData(
            $ekskul,
            $this->startDate ?: null,
            $this->endDate ?: null
        );
    }

    public function closeDetail(): void
    {
        $this->detailEkskulId = null;
        $this->detailData = null;
    }

    private function buildPeriodeLabel(): string
    {
        if ($this->startDate && $this->endDate) {
            return Carbon::parse($this->startDate)->format('d/m/Y') . ' s.d ' . Carbon::parse($this->endDate)->format('d/m/Y');
        }
        if ($this->startDate) {
            return 'Mulai ' . Carbon::parse($this->startDate)->format('d/m/Y');
        }
        if ($this->endDate) {
            return 'Sampai ' . Carbon::parse($this->endDate)->format('d/m/Y');
        }

        return 'Semua Pertemuan';
    }

    public function downloadExcel(string $ekskulId)
    {
        $ekskul = Ekstrakurikuler::findOrFail($ekskulId);
        $reportData = app(EkstrakurikulerRekapService::class)->buildEkskulReportData(
            $ekskul,
            $this->startDate ?: null,
            $this->endDate ?: null
        );

        $safeName = str_replace([' ', '/', '\\'], '_', $ekskul->nama_ekskul);
        return Excel::download(
            new EkstrakurikulerRekapExport($reportData, $this->buildPeriodeLabel()),
            "Laporan_Ekstrakurikuler_{$safeName}.xlsx"
        );
    }

    public function downloadPdf(string $ekskulId)
    {
        $ekskul = Ekstrakurikuler::findOrFail($ekskulId);
        $reportData = app(EkstrakurikulerRekapService::class)->buildEkskulReportData(
            $ekskul,
            $this->startDate ?: null,
            $this->endDate ?: null
        );
        $sekolah = PengaturanSekolah::current();

        $pdf = Pdf::loadView('pdf.laporan-ekstrakurikuler', [
            'ekskul'       => $reportData['ekskul'],
            'meetings'     => $reportData['meetings'],
            'members'      => $reportData['members'],
            'summary'      => $reportData['summary'],
            'periodeLabel' => $this->buildPeriodeLabel(),
            'sekolah'      => $sekolah,
        ])->setPaper('a4', 'landscape');

        $safeName = str_replace([' ', '/', '\\'], '_', $ekskul->nama_ekskul);
        return response()->streamDownload(
            fn () => print($pdf->output()),
            "Laporan_Ekstrakurikuler_{$safeName}.pdf"
        );
    }
}

