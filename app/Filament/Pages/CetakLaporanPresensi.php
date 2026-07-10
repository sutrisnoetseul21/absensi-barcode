<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Notifications\Notification;
use App\Models\Kelas;
use App\Models\TahunAjaran;
use App\Models\Presensi;
use App\Models\EnrollmentSiswa;
use App\Models\PengaturanSekolah;
use Carbon\Carbon;

class CetakLaporanPresensi extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-printer';
    protected string $view = 'filament.pages.cetak-laporan-presensi';
    protected static string|\UnitEnum|null $navigationGroup = 'Laporan';
    protected static ?string $title = 'Cetak Laporan Presensi';
    protected static ?string $navigationLabel = 'Cetak Laporan Presensi';
    protected static ?int $navigationSort = 3;

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

    public function mount(): void
    {
        $this->academicYears = TahunAjaran::orderBy('start_year', 'desc')->get();
        $activeYear = TahunAjaran::where('status', 'aktif')->first() ?? $this->academicYears->first();
        if ($activeYear) {
            $this->selectedAcademicYearId = $activeYear->id;
        }

        $this->classes = Kelas::orderBy('name', 'asc')->get();
        if ($this->classes->isNotEmpty()) {
            $this->selectedClassId = $this->classes->first()->id;
        }

        // Default bulanan = bulan ini
        $this->bulan = date('m');
        $this->tahunBulanan = date('Y');

        // Default semester berdasarkan bulan sekarang
        $this->semester = (int)date('m') >= 7 ? 'ganjil' : 'genap';
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

    /**
     * Ambil data laporan (list siswa + statistik kehadiran).
     */
    public function getLaporanData(): array
    {
        if (!$this->selectedClassId || !$this->selectedAcademicYearId) {
            return [];
        }

        $range = $this->getDateRange();
        if (!$range['start'] || !$range['end']) return [];

        $enrollments = EnrollmentSiswa::with('siswa')
            ->where('class_id', $this->selectedClassId)
            ->where('academic_year_id', $this->selectedAcademicYearId)
            ->where('status', 'aktif')
            ->get()
            ->sortBy(fn($e) => $e->siswa->name ?? '');

        $presensiData = Presensi::where('class_id', $this->selectedClassId)
            ->where('academic_year_id', $this->selectedAcademicYearId)
            ->whereBetween('date', [$range['start'], $range['end']])
            ->get()
            ->groupBy('student_id');

        $data = [];
        $no = 1;
        foreach ($enrollments as $enrollment) {
            $siswa = $enrollment->siswa;
            if (!$siswa) continue;

            $atts = $presensiData->get($siswa->id, collect());

            $data[] = [
                'no'            => $no++,
                'nisn'          => $siswa->nisn,
                'name'          => $siswa->name,
                'hadir'         => $atts->where('status', 'hadir')->count(),
                'telat'         => $atts->where('status', 'telat')->count(),
                'izin'          => $atts->where('status', 'izin')->count(),
                'sakit'         => $atts->where('status', 'sakit')->count(),
                'alpa'          => $atts->where('status', 'alpa')->count(),
                'late_minutes'  => $atts->sum('late_minutes'),
            ];
        }

        return $data;
    }

    public function downloadExcel()
    {
        if (!$this->selectedClassId || !$this->selectedAcademicYearId) {
            Notification::make()->title('Gagal')->body('Pilih kelas dan tahun ajaran terlebih dahulu.')->danger()->send();
            return;
        }

        $range = $this->getDateRange();
        $className = Kelas::find($this->selectedClassId)?->name ?? 'Kelas';
        $fileName = 'Laporan_Presensi_' . $className . '_' . str_replace(' ', '_', $range['label']) . '.xlsx';

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\LaporanPresensiRangeExport(
                $this->selectedClassId,
                $this->selectedAcademicYearId,
                $range['start'],
                $range['end'],
                $range['label']
            ),
            $fileName
        );
    }

    public function downloadPdf()
    {
        if (!$this->selectedClassId || !$this->selectedAcademicYearId) {
            Notification::make()->title('Gagal')->body('Pilih kelas dan tahun ajaran terlebih dahulu.')->danger()->send();
            return;
        }

        $range      = $this->getDateRange();
        $laporanData = $this->getLaporanData();
        $kelas      = Kelas::find($this->selectedClassId);
        $sekolah    = PengaturanSekolah::current();

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.laporan-presensi-range', [
            'laporanData'   => $laporanData,
            'periodeLabel'  => $range['label'],
            'kelas'         => $kelas,
            'sekolah'       => $sekolah,
            'generatedAt'   => now()->locale('id')->translatedFormat('l, d F Y H:i'),
        ])->setPaper('a4', 'landscape');

        $className = $kelas?->name ?? 'Kelas';
        $fileName  = 'Laporan_Presensi_' . $className . '_' . str_replace(' ', '_', $range['label']) . '.pdf';

        return response()->streamDownload(
            fn() => print($pdf->output()),
            $fileName
        );
    }
}
