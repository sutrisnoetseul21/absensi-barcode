<?php

namespace App\Livewire\PortalPresensi\Ekstrakurikuler;

use App\Exports\EkstrakurikulerRekapExport;
use App\Models\Ekstrakurikuler;
use App\Models\PengaturanSekolah;
use App\Models\TahunAjaran;
use App\Services\EkstrakurikulerRekapService;
use Barryvdh\DomPDF\Facade\Pdf;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;

#[Layout('components.layouts.portal')]
class LaporanEkstrakurikuler extends Component
{
    public $academicYears = [];
    public ?string $selectedAcademicYearId = null;

    public $ekskuls = [];
    public ?string $selectedEkskulId = null;

    public array $reportMeetings = [];
    public array $reportMembers = [];
    public array $reportSummary = [];

    public function mount(): void
    {
        abort_if(
            !(PengaturanSekolah::current()?->enable_ekstrakurikuler ?? true),
            403,
            'Fitur Ekstrakurikuler sedang dinonaktifkan.'
        );

        $this->academicYears = TahunAjaran::orderBy('start_year', 'desc')->get();
        $activeYear = TahunAjaran::where('status', 'aktif')->first()
            ?? (PengaturanSekolah::current()?->academic_year_id_active
                ? TahunAjaran::find(PengaturanSekolah::current()->academic_year_id_active)
                : null)
            ?? $this->academicYears->first();

        $this->selectedAcademicYearId = $activeYear?->id;

        $requestedEkskulId = request()->query('ekskul_id');
        if ($requestedEkskulId) {
            $reqEks = Ekstrakurikuler::find($requestedEkskulId);
            if ($reqEks) {
                $this->selectedAcademicYearId = $reqEks->academic_year_id;
                $this->selectedEkskulId = $reqEks->id;
            }
        }

        $this->loadEkskuls();
    }

    public function updatedSelectedAcademicYearId(): void
    {
        $this->selectedEkskulId = null;
        $this->loadEkskuls();
    }

    public function updatedSelectedEkskulId(): void
    {
        $this->loadReport();
    }

    protected function loadEkskuls(): void
    {
        if (!$this->selectedAcademicYearId) {
            $this->ekskuls = collect();
            $this->resetReport();
            return;
        }

        $this->ekskuls = Ekstrakurikuler::with(['pembinas', 'tahunAjaran'])
            ->where('academic_year_id', $this->selectedAcademicYearId)
            ->orderBy('nama_ekskul')
            ->get();

        if ($this->ekskuls->isNotEmpty()) {
            $exists = $this->selectedEkskulId && $this->ekskuls->contains('id', $this->selectedEkskulId);
            if (!$exists) {
                $this->selectedEkskulId = $this->ekskuls->first()->id;
            }
            $this->loadReport();
        } else {
            $this->selectedEkskulId = null;
            $this->resetReport();
        }
    }

    protected function resetReport(): void
    {
        $this->reportMeetings = [];
        $this->reportMembers = [];
        $this->reportSummary = [];
    }

    public function loadReport(): void
    {
        $ekskul = $this->getSelectedEkskul();
        if (!$ekskul) {
            $this->resetReport();
            return;
        }

        $reportData = app(EkstrakurikulerRekapService::class)->buildEkskulReportData($ekskul);
        $this->reportMeetings = $reportData['meetings'];
        $this->reportMembers = $reportData['members'];
        $this->reportSummary = $reportData['summary'];
    }

    protected function getSelectedEkskul(): ?Ekstrakurikuler
    {
        if (!$this->selectedEkskulId) return null;

        return collect($this->ekskuls)->firstWhere('id', $this->selectedEkskulId)
            ?? Ekstrakurikuler::with(['pembinas', 'tahunAjaran'])->find($this->selectedEkskulId);
    }

    public function downloadExcel()
    {
        $ekskul = $this->getSelectedEkskul();
        if (!$ekskul) return;

        $reportData = app(EkstrakurikulerRekapService::class)->buildEkskulReportData($ekskul);
        $safeName = str_replace([' ', '/', '\\'], '_', $ekskul->nama_ekskul);

        return Excel::download(
            new EkstrakurikulerRekapExport($reportData, 'Semua Pertemuan'),
            "Laporan_Ekstrakurikuler_{$safeName}.xlsx"
        );
    }

    public function downloadPdf()
    {
        $ekskul = $this->getSelectedEkskul();
        if (!$ekskul) return;

        $reportData = app(EkstrakurikulerRekapService::class)->buildEkskulReportData($ekskul);
        $sekolah = PengaturanSekolah::current();

        $pdf = Pdf::loadView('pdf.laporan-ekstrakurikuler', [
            'ekskul'       => $reportData['ekskul'],
            'meetings'     => $reportData['meetings'],
            'members'      => $reportData['members'],
            'summary'      => $reportData['summary'],
            'periodeLabel' => 'Semua Pertemuan',
            'sekolah'      => $sekolah,
        ])->setPaper('a4', 'landscape');

        $safeName = str_replace([' ', '/', '\\'], '_', $ekskul->nama_ekskul);
        return response()->streamDownload(
            fn () => print($pdf->output()),
            "Laporan_Ekstrakurikuler_{$safeName}.pdf"
        );
    }

    public function render()
    {
        return view('livewire.portal-presensi.ekstrakurikuler.laporan-ekstrakurikuler', [
            'selectedEkskul' => $this->getSelectedEkskul(),
        ])->title('Rekap & Laporan Ekstrakurikuler');
    }
}

