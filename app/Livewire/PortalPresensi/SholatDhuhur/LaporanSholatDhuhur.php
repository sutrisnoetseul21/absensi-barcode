<?php

namespace App\Livewire\PortalPresensi\SholatDhuhur;

use App\Exports\RekapSholatDhuhurHarianExport;
use App\Models\Guru;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\Pengajaran;
use App\Models\PengaturanSekolah;
use App\Models\TahunAjaran;
use App\Services\PresensiSholatDhuhurService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;

#[Layout('components.layouts.portal')]
class LaporanSholatDhuhur extends Component
{
    public $academicYears = [];
    public $selectedAcademicYearId;
    public $classes = [];
    public $selectedClassId;

    public string $jenisLaporan = 'bulanan'; // 'bulanan' | 'semester' | 'tahunan' | 'sekolah'

    // Bulanan
    public string $bulan = '';
    public string $tahunBulanan = '';

    // Semester
    public string $semester = 'ganjil';

    // Harian Sekolah
    public string $tanggalSekolah = '';

    // Data Matrix Bulanan
    public $students = [];
    public $monthlyStats = [];
    public $classMonthlyStats = [];
    public $daysInMonth = 0;
    public $todayDate;

    // Data Semester / Tahunan
    public $semesterStudentsData = [];
    public $semesterMonthsList = [];

    // Data Harian Sekolah
    public array $schoolSummary = [];

    public function mount(): void
    {
        abort_if(!(PengaturanSekolah::current()->enable_sholat_dhuhur ?? true), 403, 'Fitur Sholat Dhuhur sedang dinonaktifkan.');

        $this->academicYears = TahunAjaran::orderBy('start_year', 'desc')->get();
        $activeYear = TahunAjaran::where('status', 'aktif')->first() ?? $this->academicYears->first();
        if ($activeYear) {
            $this->selectedAcademicYearId = $activeYear->id;
        }

        $this->classes = Kelas::orderBy('name', 'asc')->get();
        $this->selectedClassId = $this->classes->first()?->id ?? null;

        $this->bulan = date('m');
        $this->tahunBulanan = date('Y');
        $this->semester = (int) date('m') >= 7 ? 'ganjil' : 'genap';
        $this->tanggalSekolah = Carbon::today('Asia/Jakarta')->toDateString();

        $this->refreshData();
    }

    public function updatedSelectedAcademicYearId(): void { $this->refreshData(); }
    public function updatedSelectedClassId(): void { $this->refreshData(); }
    public function updatedJenisLaporan(): void { $this->refreshData(); }
    public function updatedBulan(): void { $this->refreshData(); }
    public function updatedTahunBulanan(): void { $this->refreshData(); }
    public function updatedSemester(): void { $this->refreshData(); }
    public function updatedTanggalSekolah(): void { $this->refreshData(); }

    public function refreshData(): void
    {
        if ($this->jenisLaporan === 'bulanan') {
            $this->getMatrixData();
        } elseif ($this->jenisLaporan === 'sekolah') {
            $this->getSchoolDailyData();
        } else {
            $this->getSemesterYearlyData();
        }
    }

    public function getDateRange(): array
    {
        $ay = TahunAjaran::find($this->selectedAcademicYearId);
        $sy = $ay ? $ay->start_year : (int) date('Y');
        $ey = $ay ? $ay->end_year : ((int) date('Y') + 1);

        if ($this->jenisLaporan === 'bulanan') {
            $m = str_pad($this->bulan, 2, '0', STR_PAD_LEFT);
            $y = $this->tahunBulanan ?: (int) date('Y');
            $start = "{$y}-{$m}-01";
            $end = Carbon::createFromDate($y, (int) $m, 1)->endOfMonth()->toDateString();
            $monthNames = [
                '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April', '05' => 'Mei', '06' => 'Juni',
                '07' => 'Juli', '08' => 'Agustus', '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember',
            ];
            return ['start' => $start, 'end' => $end, 'label' => ($monthNames[$m] ?? $m) . " {$y}"];
        }

        if ($this->jenisLaporan === 'sekolah') {
            $target = $this->tanggalSekolah ?: Carbon::today('Asia/Jakarta')->toDateString();
            $label = Carbon::parse($target)->locale('id')->translatedFormat('l, d F Y');
            return ['start' => $target, 'end' => $target, 'label' => $label];
        }

        if ($this->jenisLaporan === 'semester') {
            if ($this->semester === 'ganjil') {
                return ['start' => "{$sy}-07-01", 'end' => "{$sy}-12-31", 'label' => "Semester Ganjil ({$sy}/{$ey})"];
            }
            $endJun = Carbon::createFromDate($ey, 6, 1)->endOfMonth()->toDateString();
            return ['start' => "{$ey}-01-01", 'end' => $endJun, 'label' => "Semester Genap ({$sy}/{$ey})"];
        }

        return ['start' => "{$sy}-07-01", 'end' => "{$ey}-06-30", 'label' => "Tahun Ajaran {$sy}/{$ey}"];
    }

    public function getMatrixData(): void
    {
        if (!$this->selectedClassId || !$this->selectedAcademicYearId) {
            $this->students = [];
            $this->monthlyStats = [];
            $this->classMonthlyStats = [];
            $this->daysInMonth = 0;
            return;
        }

        $result = app(PresensiSholatDhuhurService::class)->getMonthlyMatrixData(
            $this->selectedAcademicYearId,
            $this->selectedClassId,
            $this->bulan,
            (int) $this->tahunBulanan
        );

        $this->students = $result['students'];
        $this->monthlyStats = $result['monthlyStats'];
        $this->classMonthlyStats = $result['classMonthlyStats'];
        $this->daysInMonth = $result['daysInMonth'];
        $this->todayDate = $result['todayDate'];
    }

    public function getSemesterYearlyData(): void
    {
        if (!$this->selectedClassId || !$this->selectedAcademicYearId) {
            $this->semesterStudentsData = [];
            $this->semesterMonthsList = [];
            return;
        }

        $range = $this->getDateRange();
        $result = app(PresensiSholatDhuhurService::class)->getSemesterYearlyData(
            $this->selectedAcademicYearId,
            $this->selectedClassId,
            $range['start'],
            $range['end']
        );

        $this->semesterStudentsData = $result['studentsData'];
        $this->semesterMonthsList = $result['monthsList'];
    }

    public function getSchoolDailyData(): void
    {
        if (!$this->selectedAcademicYearId) {
            $this->schoolSummary = [];
            return;
        }

        $date = $this->tanggalSekolah ?: Carbon::today('Asia/Jakarta')->toDateString();
        $this->schoolSummary = app(PresensiSholatDhuhurService::class)
            ->getDailySchoolSummaryData($this->selectedAcademicYearId, $date);
    }

    protected function getDefaultKoordinator(): ?Guru
    {
        $mapelPaiIds = MataPelajaran::where('nama_mapel', 'like', '%Agama Islam%')
            ->orWhere('kode_mapel', 'PAIBP')
            ->pluck('id');

        $paiTeacherId = Pengajaran::whereIn('mata_pelajaran_id', $mapelPaiIds)->value('teacher_id');
        return $paiTeacherId ? Guru::find($paiTeacherId) : Guru::orderBy('name')->first();
    }

    public function downloadPdf()
    {
        if (!$this->selectedAcademicYearId) {
            session()->flash('error', 'Pilih tahun ajaran terlebih dahulu.');
            return;
        }

        $sekolah = PengaturanSekolah::current();
        $tahunAjaran = TahunAjaran::find($this->selectedAcademicYearId);
        $koordinator = $this->getDefaultKoordinator();
        $generatedAt = Carbon::now('Asia/Jakarta')->locale('id')->translatedFormat('l, d F Y H:i') . ' WIB';

        if ($this->jenisLaporan === 'sekolah') {
            $this->getSchoolDailyData();
            $summary = $this->schoolSummary;
            $date = $summary['date'] ?? ($this->tanggalSekolah ?: Carbon::today('Asia/Jakarta')->toDateString());
            $safeDate = str_replace('-', '_', $date);

            $pdf = Pdf::loadView('pdf.laporan-sholat-dhuhur-rekap-harian', [
                'stats'                 => $summary['stats'] ?? [],
                'classesSummary'        => $summary['classesSummary'] ?? [],
                'gradeGroups'           => $summary['gradeGroups'] ?? [],
                'sekolah'               => $sekolah,
                'koordinator'           => $koordinator,
                'tahunAjaran'           => $tahunAjaran,
                'selectedDateFormatted' => $summary['date_formatted'] ?? Carbon::parse($date)->locale('id')->translatedFormat('l, d F Y'),
                'date'                  => $date,
                'isHoliday'             => $summary['is_holiday'] ?? false,
                'holidayDesc'           => $summary['holiday_desc'] ?? null,
                'unattendedStudents'    => $summary['unattendedStudents'] ?? [],
                'allStudents'           => $summary['allStudents'] ?? [],
                'generatedAt'           => $generatedAt,
            ])->setPaper('a4', 'landscape');

            return response()->streamDownload(fn () => print($pdf->output()), "Rekap_Sholat_Dhuhur_Sekolah_{$safeDate}.pdf");
        }

        if (!$this->selectedClassId) {
            session()->flash('error', 'Pilih kelas terlebih dahulu.');
            return;
        }

        $kelas = Kelas::find($this->selectedClassId);
        $waliKelas = $kelas?->waliKelas($this->selectedAcademicYearId);
        $range = $this->getDateRange();
        $safeClass = str_replace([' ', '/', '\\'], '-', strtolower($kelas?->name ?? 'kelas'));

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

            return response()->streamDownload(fn () => print($pdf->output()), "laporan-sholat-dhuhur-{$safeClass}-{$this->bulan}-{$this->tahunBulanan}.pdf");
        }

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

        return response()->streamDownload(fn () => print($pdf->output()), "rekap-sholat-dhuhur-{$safeClass}-{$this->jenisLaporan}.pdf");
    }

    public function downloadExcel()
    {
        if (!$this->selectedAcademicYearId) {
            session()->flash('error', 'Pilih tahun ajaran terlebih dahulu.');
            return;
        }

        if ($this->jenisLaporan === 'sekolah') {
            $this->getSchoolDailyData();
            $summary = $this->schoolSummary;
            $date = $summary['date'] ?? ($this->tanggalSekolah ?: Carbon::today('Asia/Jakarta')->toDateString());
            $safeDate = str_replace('-', '_', $date);

            return Excel::download(
                new RekapSholatDhuhurHarianExport(
                    $date,
                    $this->selectedAcademicYearId,
                    $summary['stats'] ?? [],
                    $summary['classesSummary'] ?? [],
                    $summary['unattendedStudents'] ?? [],
                    $summary['allStudents'] ?? [],
                    $this->getDefaultKoordinator()
                ),
                "Rekap_Sholat_Dhuhur_Sekolah_{$safeDate}.xlsx"
            );
        }

        if (!$this->selectedClassId) {
            session()->flash('error', 'Pilih kelas terlebih dahulu.');
            return;
        }

        $kelas = Kelas::find($this->selectedClassId);
        $safeClass = str_replace([' ', '/', '\\'], '-', strtolower($kelas?->name ?? 'kelas'));
        $filename = "laporan-sholat-dhuhur-{$safeClass}-" . date('Ymd_His') . '.csv';

        if ($this->jenisLaporan === 'bulanan') {
            $this->getMatrixData();
            $headers = [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            ];

            return response()->stream(function () {
                $file = fopen('php://output', 'w');
                fputs($file, "\xEF\xBB\xBF");

                $row1 = ['No', 'NISN', 'Nama Siswa', 'JK'];
                for ($d = 1; $d <= $this->daysInMonth; $d++) {
                    $row1[] = (string) $d;
                }
                fputcsv($file, array_merge($row1, ['Total Hadir', 'Total Ijin', 'Total Tidak Hadir']));

                $no = 1;
                foreach ($this->students as $student) {
                    $stat = $this->monthlyStats[$student->id] ?? [];
                    $row = [$no++, $student->nisn ?? '-', $student->name, $student->gender ?? '-'];
                    for ($d = 1; $d <= $this->daysInMonth; $d++) {
                        $row[] = $stat['daily'][$d] ?? '-';
                    }
                    $row[] = $stat['hadir'] ?? 0;
                    $row[] = $stat['ijin'] ?? 0;
                    $row[] = $stat['tidak_hadir'] ?? 0;
                    fputcsv($file, $row);
                }
                fclose($file);
            }, 200, $headers);
        }

        $this->getSemesterYearlyData();
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        return response()->stream(function () {
            $file = fopen('php://output', 'w');
            fputs($file, "\xEF\xBB\xBF");

            $rowHeader1 = ['No', 'NISN', 'Nama Siswa', 'JK'];
            foreach ($this->semesterMonthsList as $m) {
                $rowHeader1[] = $m['name'] . ' (H)';
                $rowHeader1[] = $m['name'] . ' (I)';
                $rowHeader1[] = $m['name'] . ' (A)';
            }
            fputcsv($file, array_merge($rowHeader1, ['Kumulatif H', 'Kumulatif I', 'Kumulatif A', '% Kepatuhan']));

            $no = 1;
            foreach ($this->semesterStudentsData as $st) {
                $totH = $st['total']['hadir'] ?? 0;
                $totI = $st['total']['ijin'] ?? 0;
                $totA = $st['total']['tidak_hadir'] ?? 0;
                $grand = $totH + $totI + $totA;
                $pct = $grand > 0 ? round(($totH / $grand) * 100, 1) . '%' : '0%';

                $row = [$no++, $st['nisn'] ?? '-', $st['name'], $st['gender'] ?? '-'];
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
        }, 200, $headers);
    }

    public function render()
    {
        return view('livewire.portal-presensi.sholat-dhuhur.laporan-sholat-dhuhur')
            ->title('Rekap dan Cetak Laporan Sholat Dhuhur - Portal Presensi');
    }
}

