<?php

namespace App\Livewire\PortalSiswa;

use App\Models\Presensi;
use App\Models\PresensiSholatDhuhur;
use App\Services\PresensiSholatDhuhurService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.portal')]
class SiswaSholatDhuhur extends Component
{
    public $student;
    public $enrollment;
    public $selectedMonthYear;
    public $availableMonths = [];

    public $daysInMonth = 0;
    public $startOfMonthOffset = 0;
    public $todayDate;

    public $monthlyStats = [
        'hadir'       => 0,
        'ijin'        => 0,
        'tidak_hadir' => 0,
        'libur'       => 0,
    ];
    public $attendanceData = [];
    public $attendancePercentage = 0;
    public $effectiveDays = 0;

    public function mount()
    {
        $this->student = Auth::user()->student;
        if (! $this->student) {
            abort(403, 'Akses ditolak: Data siswa tidak ditemukan.');
        }

        $this->enrollment = $this->student->enrollmentAktif()->with(['kelas', 'tahunAjaran'])->first();
        $this->generateAvailableMonths();

        $currentMonthYear = date('m-Y');
        if (array_key_exists($currentMonthYear, $this->availableMonths)) {
            $this->selectedMonthYear = $currentMonthYear;
        } else {
            $this->selectedMonthYear = array_key_first($this->availableMonths) ?? date('m-Y');
        }

        $this->loadData();
    }

    private function generateAvailableMonths()
    {
        $this->availableMonths = [];
        if ($this->enrollment && $this->enrollment->tahunAjaran) {
            $sy = $this->enrollment->tahunAjaran->start_year;
            $ey = $this->enrollment->tahunAjaran->end_year;

            $monthNames = [
                1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
            ];

            // Juli - Desember (Start Year)
            for ($m = 7; $m <= 12; $m++) {
                $key = sprintf('%02d-%d', $m, $sy);
                $this->availableMonths[$key] = $monthNames[$m] . ' ' . $sy;
            }
            // Januari - Juni (End Year)
            for ($m = 1; $m <= 6; $m++) {
                $key = sprintf('%02d-%d', $m, $ey);
                $this->availableMonths[$key] = $monthNames[$m] . ' ' . $ey;
            }
        }
    }

    public function updatedSelectedMonthYear()
    {
        $this->loadData();
    }

    public function loadData()
    {
        if (! $this->enrollment || ! $this->selectedMonthYear) return;

        $parts = explode('-', $this->selectedMonthYear);
        if (count($parts) !== 2) return;

        $month = (int)$parts[0];
        $year  = (int)$parts[1];

        $startOfMonth = Carbon::createFromDate($year, $month, 1);
        $this->daysInMonth = $startOfMonth->daysInMonth;
        // Offset: 0 = Minggu, 1 = Senin, dst.
        $this->startOfMonthOffset = $startOfMonth->dayOfWeek;
        $this->todayDate = Carbon::now('Asia/Jakarta')->toDateString();

        $academicYearId = $this->enrollment->academic_year_id;
        $classId        = $this->enrollment->class_id;

        $sholatService = app(PresensiSholatDhuhurService::class);

        // Ambil data sholat dhuhur bulan ini untuk siswa ini
        $records = PresensiSholatDhuhur::where('student_id', $this->student->id)
            ->where('academic_year_id', $academicYearId)
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->get()
            ->keyBy(fn ($item) => Carbon::parse($item->date)->day);

        // Ambil data presensi pagi bulan ini untuk sinkronisasi otomatis jika belum ada record sholat
        $morningRecords = Presensi::where('student_id', $this->student->id)
            ->where('academic_year_id', $academicYearId)
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->get()
            ->keyBy(fn ($item) => Carbon::parse($item->date)->day);

        $this->attendanceData = [];
        $stats = [
            'hadir'       => 0,
            'ijin'        => 0,
            'tidak_hadir' => 0,
            'libur'       => 0,
        ];
        $effectiveDays = 0;

        for ($d = 1; $d <= $this->daysInMonth; $d++) {
            $dateObj = Carbon::createFromDate($year, $month, $d);
            $dateStr = $dateObj->toDateString();
            $isEffective = $sholatService->isHariSholatDhuhur($dateObj, $classId);

            if (! $isEffective) {
                $desc = $sholatService->getHolidayDescription($dateObj, $classId);
                $status = 'libur';
                $keterangan = $desc ?: ($dateObj->isFriday() ? 'Hari Sholat Jumat' : 'Hari Libur');
                $stats['libur']++;
            } else {
                $effectiveDays++;
                $rec = $records->get($d);

                if ($rec) {
                    $status = $rec->status; // 'hadir', 'ijin', 'tidak_hadir'
                    $keterangan = $rec->keterangan;
                } else {
                    // Cek presensi pagi jika sudah lewat atau hari ini
                    $morning = $morningRecords->get($d);
                    if ($morning && $morning->status === 'alpa') {
                        $status = 'tidak_hadir';
                        $keterangan = 'Alpa (Sinkron Presensi Pagi)';
                    } elseif ($morning && in_array($morning->status, ['izin', 'sakit'])) {
                        $status = 'ijin';
                        $keterangan = ucfirst($morning->status) . ' (Sinkron Presensi Pagi)';
                    } elseif ($dateStr <= $this->todayDate) {
                        $status = 'tidak_hadir';
                        $keterangan = 'Belum Ada Presensi';
                    } else {
                        $status = 'belum';
                        $keterangan = 'Mendatang';
                    }
                }

                if (in_array($status, ['hadir', 'ijin', 'tidak_hadir'])) {
                    $stats[$status]++;
                }
            }

            $this->attendanceData[$d] = [
                'day'         => $d,
                'date'        => $dateStr,
                'status'      => $status,
                'keterangan'  => $keterangan ?? '',
                'day_name'    => $dateObj->translatedFormat('l'),
            ];
        }

        $this->monthlyStats = $stats;
        $this->effectiveDays = $effectiveDays;
        $this->attendancePercentage = $effectiveDays > 0
            ? round(($stats['hadir'] / $effectiveDays) * 100, 1)
            : 0;
    }

    public function render()
    {
        return view('livewire.portal-siswa.siswa-sholat-dhuhur');
    }
}
