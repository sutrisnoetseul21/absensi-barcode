<?php

namespace App\Livewire\PortalPresensi;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\TahunAjaran;
use App\Models\PengaturanSekolah;
use App\Services\PresensiRekapService;
use App\Exports\RekapAbsensiSekolahExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

#[Layout('components.layouts.portal')]
class RekapAbsensiSekolah extends Component
{
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

    public function exportExcel()
    {
        if (!$this->selectedAcademicYearId) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Pilih Tahun Ajaran terlebih dahulu.'
            ]);
            return;
        }

        if (empty($this->classesData)) {
            $this->loadReportData();
        }

        $ay = TahunAjaran::find($this->selectedAcademicYearId);
        $ayName = $ay ? str_replace(['/', '\\'], '-', $ay->name) : 'TA';
        $fileName = "Rekap_Presensi_Sekolah_{$ayName}.xlsx";

        return Excel::download(
            new RekapAbsensiSekolahExport($this->selectedAcademicYearId, $this->classesData, $this->monthsList),
            $fileName
        );
    }

    public function exportPdf()
    {
        if (!$this->selectedAcademicYearId) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Pilih Tahun Ajaran terlebih dahulu.'
            ]);
            return;
        }

        if (empty($this->classesData)) {
            $this->loadReportData();
        }

        $ay = TahunAjaran::find($this->selectedAcademicYearId);
        $sekolah = PengaturanSekolah::current();

        $pdf = Pdf::loadView('pdf.rekap-absensi-sekolah', [
            'tahunAjaran' => $ay,
            'sekolah'     => $sekolah,
            'classesData' => $this->classesData,
            'monthsList'  => $this->monthsList,
            'generatedAt' => now()->locale('id')->translatedFormat('l, d F Y H:i'),
        ])->setPaper('a4', 'landscape');

        $ayName = $ay ? str_replace(['/', '\\'], '-', $ay->name) : 'TA';
        $fileName = "Rekap_Presensi_Sekolah_{$ayName}.pdf";

        return response()->streamDownload(
            fn() => print($pdf->output()),
            $fileName
        );
    }
}
