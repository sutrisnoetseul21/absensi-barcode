<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\TahunAjaran;
use App\Models\Kelas;
use App\Models\EnrollmentSiswa;
use App\Models\Presensi;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

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
        $this->monthsList = [];

        if (!$this->selectedAcademicYearId) {
            return;
        }

        $ay = TahunAjaran::find($this->selectedAcademicYearId);
        if (!$ay) {
            return;
        }

        // Setup 12 bulan (Juli start_year sampai Juni end_year)
        $startYear = $ay->start_year;
        $endYear = $ay->end_year;

        // Jika start_year / end_year kosong, fallback ke tahun dari name / created_at
        if (!$startYear || !$endYear) {
            $startYear = date('Y') - 1;
            $endYear = date('Y');
        }

        $monthsStructure = [
            ['month' => '07', 'year' => $startYear, 'label' => 'Juli'],
            ['month' => '08', 'year' => $startYear, 'label' => 'Agustus'],
            ['month' => '09', 'year' => $startYear, 'label' => 'September'],
            ['month' => '10', 'year' => $startYear, 'label' => 'Oktober'],
            ['month' => '11', 'year' => $startYear, 'label' => 'November'],
            ['month' => '12', 'year' => $startYear, 'label' => 'Desember'],
            ['month' => '01', 'year' => $endYear, 'label' => 'Januari'],
            ['month' => '02', 'year' => $endYear, 'label' => 'Februari'],
            ['month' => '03', 'year' => $endYear, 'label' => 'Maret'],
            ['month' => '04', 'year' => $endYear, 'label' => 'April'],
            ['month' => '05', 'year' => $endYear, 'label' => 'Mei'],
            ['month' => '06', 'year' => $endYear, 'label' => 'Juni'],
        ];

        $this->monthsList = $monthsStructure;

        // Ambil jumlah siswa per kelas untuk tahun ajaran ini
        $studentCounts = EnrollmentSiswa::where('academic_year_id', $this->selectedAcademicYearId)
            ->where('status', 'aktif')
            ->select('class_id', DB::raw('count(*) as total'))
            ->groupBy('class_id')
            ->pluck('total', 'class_id')
            ->toArray();

        // Ambil seluruh kelas
        $classes = Kelas::orderBy('name', 'asc')->get();

        // Ambil data rekap absensi agregat bulanan
        // Query group by class_id, year, month, status
        $attendances = Presensi::where('academic_year_id', $this->selectedAcademicYearId)
            ->selectRaw('class_id, YEAR(date) as year, MONTH(date) as month, status, count(*) as count')
            ->groupBy('class_id', 'year', 'month', 'status')
            ->get();

        foreach ($classes as $kelas) {
            $classReport = [
                'id' => $kelas->id,
                'name' => $kelas->name,
                'student_count' => $studentCounts[$kelas->id] ?? 0,
                'months' => []
            ];

            foreach ($monthsStructure as $m) {
                $monthNum = (int)$m['month'];
                $yearNum = (int)$m['year'];

                $monthAtts = $attendances->where('class_id', $kelas->id)
                    ->where('year', $yearNum)
                    ->where('month', $monthNum);

                // Hadir diakumulasikan dari status 'hadir' + 'telat'
                $hadir = $monthAtts->whereIn('status', ['hadir', 'telat'])->sum('count');
                $sakit = $monthAtts->where('status', 'sakit')->sum('count');
                $izin = $monthAtts->where('status', 'izin')->sum('count');
                $alpa = $monthAtts->where('status', 'alpa')->sum('count');

                $key = "{$yearNum}-{$m['month']}";
                $classReport['months'][$key] = [
                    'hadir' => $hadir,
                    'sakit' => $sakit,
                    'izin' => $izin,
                    'alpa' => $alpa
                ];
            }

            $this->classesData[] = $classReport;
        }
    }
}
