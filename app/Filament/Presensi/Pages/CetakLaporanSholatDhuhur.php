<?php

namespace App\Filament\Presensi\Pages;

use Filament\Pages\Page;
use App\Filament\Traits\HasSimplePageRoleAccess;
use Filament\Notifications\Notification;
use App\Models\Kelas;
use App\Models\TahunAjaran;
use App\Models\PengaturanSekolah;
use App\Services\PresensiSholatDhuhurService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class CetakLaporanSholatDhuhur extends Page
{
    use HasSimplePageRoleAccess;

    public static function canAccess(): bool
    {
        return (bool) (PengaturanSekolah::current()->enable_sholat_dhuhur ?? true);
    }

    protected static function getModuleRolePrefix(): string
    {
        return 'presensi';
    }

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-printer';
    protected string $view = 'filament.pages.cetak-laporan-sholat-dhuhur';
    protected static string|\UnitEnum|null $navigationGroup = 'Presensi';
    protected static ?string $title = 'Cetak Laporan Sholat Dhuhur';
    protected static ?string $navigationLabel = 'Cetak Laporan Sholat Dhuhur';
    protected static ?int $navigationSort = 5;

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

    // Data Matrix Bulanan
    public $students = [];
    public $monthlyStats = [];
    public $classMonthlyStats = [];
    public $daysInMonth = 0;
    public $todayDate;

    // Data Matrix Semester/Tahunan
    public $semesterStudentsData = [];
    public $semesterMonthsList = [];

    public function mount(): void
    {
        $this->academicYears = TahunAjaran::orderBy('start_year', 'desc')->get();
        $activeYear = TahunAjaran::where('status', 'aktif')->first() ?? $this->academicYears->first();
        if ($activeYear) {
            $this->selectedAcademicYearId = $activeYear->id;
        }

        $this->classes = Kelas::orderBy('name', 'asc')->get();
        $this->selectedClassId = $this->classes->first()?->id ?? null;

        // Default bulanan = bulan ini
        $this->bulan = date('m');
        $this->tahunBulanan = date('Y');

        // Default semester berdasarkan bulan sekarang
        $this->semester = (int)date('m') >= 7 ? 'ganjil' : 'genap';

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
        } else {
            $this->getSemesterYearlyData();
        }
    }

    public function getDateRange(): array
    {
        $ay = TahunAjaran::find($this->selectedAcademicYearId);
        $sy = $ay ? $ay->start_year : (int)date('Y');
        $ey = $ay ? $ay->end_year : ((int)date('Y') + 1);

        if ($this->jenisLaporan === 'bulanan') {
            $m = str_pad($this->bulan, 2, '0', STR_PAD_LEFT);
            $y = $this->tahunBulanan ?: (int)date('Y');
            $start = "{$y}-{$m}-01";
            $end   = Carbon::createFromDate($y, (int)$m, 1)->endOfMonth()->toDateString();
            $monthNames = [
                '01'=>'Januari','02'=>'Februari','03'=>'Maret','04'=>'April','05'=>'Mei','06'=>'Juni',
                '07'=>'Juli','08'=>'Agustus','09'=>'September','10'=>'Oktober','11'=>'November','12'=>'Desember'
            ];
            $label = ($monthNames[$m] ?? $m) . " {$y}";
            return ['start' => $start, 'end' => $end, 'label' => $label];
        }

        if ($this->jenisLaporan === 'semester') {
            if ($this->semester === 'ganjil') {
                return [
                    'start' => "{$sy}-07-01",
                    'end'   => "{$sy}-12-31",
                    'label' => "Semester Ganjil ({$sy}/{$ey})",
                ];
            } else {
                $endFeb = Carbon::createFromDate($ey, 6, 1)->endOfMonth()->toDateString();
                return [
                    'start' => "{$ey}-01-01",
                    'end'   => $endFeb,
                    'label' => "Semester Genap ({$sy}/{$ey})",
                ];
            }
        }

        return [
            'start' => "{$sy}-07-01",
            'end'   => "{$ey}-06-30",
            'label' => "Tahun Ajaran {$sy}/{$ey}",
        ];
    }

    public function getMatrixData(): void
    {
        if (! $this->selectedClassId || ! $this->selectedAcademicYearId) {
            $this->students = [];
            $this->monthlyStats = [];
            $this->classMonthlyStats = [];
            $this->daysInMonth = 0;
            return;
        }

        $service = app(PresensiSholatDhuhurService::class);
        $result = $service->getMonthlyMatrixData(
            $this->selectedAcademicYearId,
            $this->selectedClassId,
            $this->bulan,
            (int)$this->tahunBulanan
        );

        $this->students          = $result['students'];
        $this->monthlyStats      = $result['monthlyStats'];
        $this->classMonthlyStats = $result['classMonthlyStats'];
        $this->daysInMonth       = $result['daysInMonth'];
        $this->todayDate         = $result['todayDate'];
    }

    public function getSemesterYearlyData(): void
    {
        if (! $this->selectedClassId || ! $this->selectedAcademicYearId) {
            $this->semesterStudentsData = [];
            $this->semesterMonthsList = [];
            return;
        }

        $range = $this->getDateRange();
        $service = app(PresensiSholatDhuhurService::class);
        $result = $service->getSemesterYearlyData(
            $this->selectedAcademicYearId,
            $this->selectedClassId,
            $range['start'],
            $range['end']
        );

        $this->semesterStudentsData = $result['studentsData'];
        $this->semesterMonthsList   = $result['monthsList'];
    }

    public function downloadPdf()
    {
        if (! $this->selectedClassId) {
            Notification::make()->title('Pilih kelas terlebih dahulu.')->danger()->send();
            return;
        }

        $kelas = Kelas::find($this->selectedClassId);
        $sekolah = PengaturanSekolah::current();
        $tahunAjaran = TahunAjaran::find($this->selectedAcademicYearId);
        $waliKelas = $kelas?->waliKelas($this->selectedAcademicYearId);
        $range = $this->getDateRange();
        $generatedAt = Carbon::now('Asia/Jakarta')->translatedFormat('d F Y H:i') . ' WIB';

        if ($this->jenisLaporan === 'bulanan') {
            $this->getMatrixData();

            $pdf = Pdf::loadView('pdf.laporan-sholat-dhuhur-matrix', [
                'students'          => $this->students,
                'monthlyStats'      => $this->monthlyStats,
                'classMonthlyStats' => $this->classMonthlyStats,
                'daysInMonth'       => $this->daysInMonth,
                'kelas'             => $kelas,
                'sekolah'           => $sekolah,
                'tahunAjaran'       => $tahunAjaran,
                'waliKelas'         => $waliKelas,
                'periodeLabel'      => $range['label'],
                'generatedAt'       => $generatedAt,
            ])->setPaper('a4', 'landscape');

            $filename = 'laporan-sholat-dhuhur-' . str_replace(' ', '-', strtolower($kelas->name)) . '-' . $this->bulan . '-' . $this->tahunBulanan . '.pdf';
            return response()->streamDownload(fn () => print($pdf->output()), $filename);
        } else {
            $this->getSemesterYearlyData();

            $pdf = Pdf::loadView('pdf.laporan-sholat-dhuhur-range', [
                'studentsData' => $this->semesterStudentsData,
                'monthsList'   => $this->semesterMonthsList,
                'kelas'        => $kelas,
                'sekolah'      => $sekolah,
                'tahunAjaran'  => $tahunAjaran,
                'waliKelas'    => $waliKelas,
                'periodeLabel' => $range['label'],
                'generatedAt'  => $generatedAt,
            ])->setPaper('a4', 'landscape');

            $filename = 'rekap-sholat-dhuhur-' . str_replace(' ', '-', strtolower($kelas->name)) . '-' . $this->jenisLaporan . '.pdf';
            return response()->streamDownload(fn () => print($pdf->output()), $filename);
        }
    }

    public function downloadExcel()
    {
        if (! $this->selectedClassId) {
            Notification::make()->title('Pilih kelas terlebih dahulu.')->danger()->send();
            return;
        }

        $kelas = Kelas::find($this->selectedClassId);
        $range = $this->getDateRange();
        $filename = 'laporan-sholat-dhuhur-' . str_replace(' ', '-', strtolower($kelas->name)) . '-' . date('Ymd_His') . '.csv';

        if ($this->jenisLaporan === 'bulanan') {
            $this->getMatrixData();
            $headers = [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            ];

            $callback = function () {
                $file = fopen('php://output', 'w');
                // UTF-8 BOM
                fputs($file, "\xEF\xBB\xBF");

                // Header baris
                $row1 = ['No', 'NISN', 'Nama Siswa', 'JK'];
                for ($d = 1; $d <= $this->daysInMonth; $d++) {
                    $row1[] = (string)$d;
                }
                $row1 = array_merge($row1, ['Total Hadir', 'Total Ijin', 'Total Tidak Hadir']);
                fputcsv($file, $row1);

                $no = 1;
                foreach ($this->students as $student) {
                    $stat = $this->monthlyStats[$student->id] ?? [];
                    $row = [
                        $no++,
                        $student->nisn ?? '-',
                        $student->name,
                        $student->gender ?? '-',
                    ];
                    for ($d = 1; $d <= $this->daysInMonth; $d++) {
                        $row[] = $stat['daily'][$d] ?? '-';
                    }
                    $row[] = $stat['hadir'] ?? 0;
                    $row[] = $stat['ijin'] ?? 0;
                    $row[] = $stat['tidak_hadir'] ?? 0;
                    fputcsv($file, $row);
                }
                fclose($file);
            };

            return response()->stream($callback, 200, $headers);
        } else {
            $this->getSemesterYearlyData();
            $headers = [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            ];

            $callback = function () {
                $file = fopen('php://output', 'w');
                fputs($file, "\xEF\xBB\xBF");

                $rowHeader1 = ['No', 'NISN', 'Nama Siswa', 'JK'];
                foreach ($this->semesterMonthsList as $m) {
                    $rowHeader1[] = $m['name'] . ' (H)';
                    $rowHeader1[] = $m['name'] . ' (I)';
                    $rowHeader1[] = $m['name'] . ' (A)';
                }
                $rowHeader1 = array_merge($rowHeader1, ['Kumulatif H', 'Kumulatif I', 'Kumulatif A', '% Kepatuhan']);
                fputcsv($file, $rowHeader1);

                $no = 1;
                foreach ($this->semesterStudentsData as $st) {
                    $totH = $st['total']['hadir'] ?? 0;
                    $totI = $st['total']['ijin'] ?? 0;
                    $totA = $st['total']['tidak_hadir'] ?? 0;
                    $grand = $totH + $totI + $totA;
                    $pct = $grand > 0 ? round(($totH / $grand) * 100, 1) . '%' : '0%';

                    $row = [
                        $no++,
                        $st['nisn'] ?? '-',
                        $st['name'],
                        $st['gender'] ?? '-',
                    ];
                    foreach ($this->semesterMonthsList as $m) {
                        $mStats = $st['months'][$m['key']] ?? [];
                        $row[] = $mStats['hadir'] ?? 0;
                        $row[] = $mStats['ijin'] ?? 0;
                        $row[] = $mStats['tidak_hadir'] ?? 0;
                    }
                    $row[] = $totH;
                    $row[] = $totI;
                    $row[] = $totA;
                    $row[] = $pct;
                    fputcsv($file, $row);
                }
                fclose($file);
            };

            return response()->stream($callback, 200, $headers);
        }
    }
}
