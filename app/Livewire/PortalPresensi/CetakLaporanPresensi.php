<?php

namespace App\Livewire\PortalPresensi;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Kelas;
use App\Models\TahunAjaran;
use App\Models\Presensi;
use App\Models\EnrollmentSiswa;
use App\Models\PengaturanSekolah;
use Carbon\Carbon;
use App\Services\PresensiRekapService;

class CetakLaporanPresensi extends Component
{
    // Filter state
    public $academicYears = [];
    public $selectedAcademicYearId;
    public $classes = [];
    public $selectedClassId;

    // Jenis laporan: bulanan, semester, tahunan
    public string $jenisLaporan = 'bulanan';

    // Parameter bulanan
    public string $bulan = '';
    public string $tahunBulanan = '';

    // Parameter semester
    public string $semester = 'ganjil'; // ganjil | genap

    // Parameter tahunan (menggunakan selectedAcademicYearId)

    // Data Matrix Bulanan
    public $students = [];
    public $monthlyStats = [];
    public $daysInMonth = 0;
    public $todayDate;
    public $alerts = [];

    // Data Matrix Semester/Tahunan
    public $semesterStudentsData = [];
    public $semesterMonthsList = [];

    // Data Matrix Rekap Sekolah (Semua Kelas)
    public $schoolClassesData = [];
    public $schoolMonthsList = [];

    public bool $enableCheckout = true;
    public bool $includeCheckout = true;
    public string $reportFormat = 'singkatan'; // singkatan | lengkap | waktu

    public function mount(): void
    {
        $this->enableCheckout = (bool) (PengaturanSekolah::current()?->enable_checkout ?? true);
        $this->includeCheckout = $this->enableCheckout;
        $this->academicYears = TahunAjaran::orderBy('start_year', 'desc')->get();
        $activeYear = TahunAjaran::where('status', 'aktif')->first() ?? $this->academicYears->first();
        if ($activeYear) {
            $this->selectedAcademicYearId = $activeYear->id;
        }

        $this->classes = Kelas::orderBy('name', 'asc')->get();
        $this->selectedClassId = null;

        // Default bulanan = bulan ini
        $this->bulan = date('m');
        $this->tahunBulanan = date('Y');

        // Default semester berdasarkan bulan sekarang
        $this->semester = (int)date('m') >= 7 ? 'ganjil' : 'genap';

        $requestedJenis = request()->query('jenis');
        if (in_array($requestedJenis, ['bulanan', 'semester', 'tahunan', 'sekolah'], true)) {
            $this->jenisLaporan = $requestedJenis;
        }

        $this->refreshData();
    }

    public function updatedSelectedAcademicYearId(): void { $this->refreshData(); }
    public function updatedSelectedClassId(): void { $this->refreshData(); }
    public function updatedJenisLaporan(): void { $this->refreshData(); }
    public function updatedBulan(): void { $this->refreshData(); }
    public function updatedTahunBulanan(): void { $this->refreshData(); }
    public function updatedSemester(): void { $this->refreshData(); }

    public function refreshData(): void
    {
        if ($this->jenisLaporan === 'bulanan') {
            $this->getMatrixData();
        } elseif ($this->jenisLaporan === 'sekolah') {
            $this->getSchoolData();
        } else {
            $this->getSemesterYearlyData();
        }
    }

    /**
     * Hitung rentang tanggal berdasarkan jenis laporan.
     */
    public function getDateRange(): array
    {
        $ay = TahunAjaran::find($this->selectedAcademicYearId);
        if (!$ay) {
            return ['start' => null, 'end' => null, 'label' => ''];
        }

        if ($this->jenisLaporan === 'bulanan') {
            $year = (int)$this->tahunBulanan;
            $month = (int)$this->bulan;
            $start = Carbon::create($year, $month, 1)->startOfMonth();
            $end   = Carbon::create($year, $month, 1)->endOfMonth();
            $monthNames = [
                1=>'Januari', 2=>'Februari', 3=>'Maret', 4=>'April',
                5=>'Mei', 6=>'Juni', 7=>'Juli', 8=>'Agustus',
                9=>'September', 10=>'Oktober', 11=>'November', 12=>'Desember',
            ];
            $label = 'Bulan ' . ($monthNames[$month] ?? $month) . ' ' . $year;

        } elseif ($this->jenisLaporan === 'semester') {
            if ($this->semester === 'ganjil') {
                // Semester Ganjil: Juli - Desember (start_year)
                $start = Carbon::create($ay->start_year, 7, 1)->startOfMonth();
                $end   = Carbon::create($ay->start_year, 12, 31)->endOfMonth();
                $label = 'Semester Ganjil TA ' . $ay->name;
            } else {
                // Semester Genap: Januari - Juni (end_year)
                $start = Carbon::create($ay->end_year, 1, 1)->startOfMonth();
                $end   = Carbon::create($ay->end_year, 6, 30)->endOfMonth();
                $label = 'Semester Genap TA ' . $ay->name;
            }
        } elseif ($this->jenisLaporan === 'sekolah') {
            $start = Carbon::create($ay->start_year, 7, 1)->startOfMonth();
            $end   = Carbon::create($ay->end_year, 6, 30)->endOfMonth();
            $label = 'Rekap Sekolah TA ' . $ay->name;
        } else {
            // Tahunan: Juli start_year - Juni end_year
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

    public function getMatrixData(): void
    {
        if ($this->jenisLaporan !== 'bulanan' || !$this->selectedClassId || !$this->selectedAcademicYearId) {
            $this->students = collect();
            $this->monthlyStats = [];
            $this->daysInMonth = 0;
            return;
        }

        $service = app(PresensiRekapService::class);
        $result = $service->getMonthlyCalendarData(
            $this->selectedAcademicYearId,
            $this->selectedClassId,
            $this->bulan,
            (int)$this->tahunBulanan
        );

        $this->students = $result['students'];
        $this->monthlyStats = $result['monthlyStats'];
        $this->daysInMonth = $result['daysInMonth'];
        $this->todayDate = $result['todayDate'];
        $this->alerts = $result['alerts'] ?? [];
    }

    public function getSemesterYearlyData(): void
    {
        if (in_array($this->jenisLaporan, ['bulanan', 'sekolah'], true) || !$this->selectedClassId || !$this->selectedAcademicYearId) {
            $this->semesterStudentsData = [];
            $this->semesterMonthsList = [];
            return;
        }

        $range = $this->getDateRange();
        if (!$range['start'] || !$range['end']) {
            $this->semesterStudentsData = [];
            $this->semesterMonthsList = [];
            return;
        }

        $service = app(PresensiRekapService::class);
        $result = $service->getStudentSemesterYearlyData(
            $this->selectedAcademicYearId,
            $this->selectedClassId,
            $range['start'],
            $range['end']
        );

        $this->semesterStudentsData = $result['studentsData'];
        $this->semesterMonthsList = $result['monthsList'];
    }

    public function getSchoolData(): void
    {
        $this->schoolClassesData = [];
        $this->schoolMonthsList = [];

        if ($this->jenisLaporan !== 'sekolah' || !$this->selectedAcademicYearId) {
            return;
        }

        $result = app(PresensiRekapService::class)
            ->getYearlySchoolData($this->selectedAcademicYearId);

        $this->schoolClassesData = $result['classesData'];
        $this->schoolMonthsList = $result['monthsList'];
    }

    public function downloadExcel()
    {
        if (!$this->selectedAcademicYearId) {
            session()->flash('error', 'Pilih tahun ajaran terlebih dahulu.');
            return;
        }

        if ($this->jenisLaporan === 'sekolah') {
            if (empty($this->schoolClassesData)) {
                $this->getSchoolData();
            }

            $ay = TahunAjaran::find($this->selectedAcademicYearId);
            $ayName = $ay ? str_replace(['/', '\\'], '-', $ay->name) : 'TA';
            $fileName = "Rekap_Presensi_Sekolah_{$ayName}.xlsx";

            return \Maatwebsite\Excel\Facades\Excel::download(
                new \App\Exports\RekapAbsensiSekolahExport(
                    $this->selectedAcademicYearId,
                    $this->schoolClassesData,
                    $this->schoolMonthsList
                ),
                $fileName
            );
        }

        if (!$this->selectedClassId) {
            session()->flash('error', 'Pilih kelas dan tahun ajaran terlebih dahulu.');
            return;
        }

        $range = $this->getDateRange();
        $className = Kelas::find($this->selectedClassId)?->name ?? 'Kelas';
        $safeClassName = str_replace(['/', '\\'], '-', $className);
        $safeLabel = str_replace([' ', '/', '\\'], ['_', '-', '-'], $range['label']);
        $fileName = 'Laporan_Presensi_' . $safeClassName . '_' . $safeLabel . '.xlsx';

        if ($this->jenisLaporan === 'bulanan') {
            return \Maatwebsite\Excel\Facades\Excel::download(
                new \App\Exports\PresensiMatrixExport(
                    $this->selectedClassId,
                    $this->selectedAcademicYearId,
                    $this->bulan,
                    $this->tahunBulanan,
                    $range['label'],
                    $this->includeCheckout,
                    $this->reportFormat
                ),
                $fileName
            );
        }

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\LaporanPresensiRangeExport(
                $this->selectedClassId,
                $this->selectedAcademicYearId,
                $range['start'],
                $range['end'],
                $range['label'],
                $this->includeCheckout
            ),
            $fileName
        );
    }

    public function downloadPdf()
    {
        if (!$this->selectedAcademicYearId) {
            session()->flash('error', 'Pilih tahun ajaran terlebih dahulu.');
            return;
        }

        $sekolah = PengaturanSekolah::current();

        if ($this->jenisLaporan === 'sekolah') {
            if (empty($this->schoolClassesData)) {
                $this->getSchoolData();
            }

            $ay = TahunAjaran::find($this->selectedAcademicYearId);
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.rekap-absensi-sekolah', [
                'tahunAjaran' => $ay,
                'sekolah'     => $sekolah,
                'classesData' => $this->schoolClassesData,
                'monthsList'  => $this->schoolMonthsList,
                'generatedAt' => now()->locale('id')->translatedFormat('l, d F Y H:i'),
            ])->setPaper('a4', 'landscape');

            $ayName = $ay ? str_replace(['/', '\\'], '-', $ay->name) : 'TA';
            $fileName = "Rekap_Presensi_Sekolah_{$ayName}.pdf";

            return response()->streamDownload(
                fn() => print($pdf->output()),
                $fileName
            );
        }

        if (!$this->selectedClassId) {
            session()->flash('error', 'Pilih kelas dan tahun ajaran terlebih dahulu.');
            return;
        }

        $range      = $this->getDateRange();
        $kelas      = Kelas::find($this->selectedClassId);
        $className = $kelas?->name ?? 'Kelas';
        $safeClassName = str_replace(['/', '\\'], '-', $className);
        $safeLabel = str_replace([' ', '/', '\\'], ['_', '-', '-'], $range['label']);
        $fileName  = 'Laporan_Presensi_' . $safeClassName . '_' . $safeLabel . '.pdf';

        if ($this->jenisLaporan === 'bulanan') {
            $this->getMatrixData();

            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.laporan-presensi-matrix', [
                'students'        => $this->students,
                'monthlyStats'    => $this->monthlyStats,
                'daysInMonth'     => $this->daysInMonth,
                'periodeLabel'    => $range['label'],
                'kelas'           => $kelas,
                'sekolah'         => $sekolah,
                'includeCheckout' => $this->includeCheckout,
                'reportFormat'    => $this->reportFormat,
                'generatedAt'     => now()->locale('id')->translatedFormat('l, d F Y H:i'),
            ])->setPaper('a4', 'landscape');

            return response()->streamDownload(
                fn() => print($pdf->output()),
                $fileName
            );
        }

        $this->getSemesterYearlyData();

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.laporan-presensi-range', [
            'studentsData'    => $this->semesterStudentsData,
            'monthsList'      => $this->semesterMonthsList,
            'jenisLaporan'    => $this->jenisLaporan,
            'periodeLabel'    => $range['label'],
            'kelas'           => $kelas,
            'sekolah'         => $sekolah,
            'includeCheckout' => $this->includeCheckout,
            'generatedAt'     => now()->locale('id')->translatedFormat('l, d F Y H:i'),
        ])->setPaper('a4', 'landscape');

        return response()->streamDownload(
            fn() => print($pdf->output()),
            $fileName
        );
    }

    #[Layout('components.layouts.portal')]
    public function render()
    {
        return view('livewire.portal-presensi.cetak-laporan-presensi')->title('Rekap dan Cetak Laporan Presensi - Portal Presensi');
    }
}
