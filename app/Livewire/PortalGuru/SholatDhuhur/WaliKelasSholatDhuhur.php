<?php

namespace App\Livewire\PortalGuru\SholatDhuhur;

use App\Models\EnrollmentSiswa;
use App\Models\Kelas;
use App\Models\PengaturanSekolah;
use App\Models\Presensi;
use App\Models\PresensiSholatDhuhur;
use App\Models\TahunAjaran;
use App\Services\PresensiSholatDhuhurService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.portal')]
class WaliKelasSholatDhuhur extends Component
{
    public $classes = [];
    public $selectedClassId;
    public $selectedMonthYear;
    public $availableMonths = [];
    public $academicYears = [];
    public $selectedAcademicYearId;

    public $students = [];
    public $monthlyStats = [];
    public $classMonthlyStats = [];
    public $todayStats = [];
    public $daysInMonth = 0;
    public $todayDate;

    // Modal Input Harian
    public $showInputModal = false;
    public $inputDate;
    public $isInputDateHoliday = false;
    public $inputDateHolidayDesc = '';
    public $inputStudents = [];

    // Modal Cetak Laporan
    public $showCetakModal = false;
    public $cetakJenis = 'bulanan'; // 'bulanan' | 'semester'
    public $cetakBulanYear = '';
    public $cetakSemester = 'ganjil';

    public function mount()
    {
        $user = Auth::user();
        if (! $user || (! $user->isWaliKelasAktif() && ! $user->hasAnyRole(['super_admin', 'admin_presensi']))) {
            abort(403, 'Akses ditolak: Menu ini khusus Wali Kelas dan Administrator.');
        }

        $this->academicYears = TahunAjaran::where('status', 'aktif')->orderBy('start_year', 'desc')->get();
        $activeYear = TahunAjaran::where('status', 'aktif')->first() ?? $this->academicYears->first();
        if ($activeYear) {
            $this->selectedAcademicYearId = $activeYear->id;
        }

        $this->loadClasses();
    }

    private function generateAvailableMonths()
    {
        $this->availableMonths = [];
        if (! $this->selectedAcademicYearId) return;

        $ay = TahunAjaran::find($this->selectedAcademicYearId);
        if ($ay) {
            $sy = $ay->start_year;
            $ey = $ay->end_year;

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

        $currentMonthYear = date('m-Y');
        if (array_key_exists($currentMonthYear, $this->availableMonths)) {
            $this->selectedMonthYear = $currentMonthYear;
        } else {
            $this->selectedMonthYear = array_key_first($this->availableMonths);
        }
    }

    public function loadClasses()
    {
        $this->generateAvailableMonths();

        if (! $this->selectedAcademicYearId) {
            $this->classes = [];
            $this->selectedClassId = null;
            return;
        }

        $user = Auth::user();
        $isAdminMode = $user->hasAnyRole(['super_admin', 'admin_presensi']);
        $hasBypass   = $user->can('portal_guru:akses_semua_kelas');

        if (! $isAdminMode && ! $hasBypass) {
            $actor = $user->teacher;
            $this->classes = Kelas::where(function ($q) use ($actor) {
                $q->whereHas('kelasAjarans', function ($query) use ($actor) {
                    $query->where('academic_year_id', $this->selectedAcademicYearId)
                          ->where('teacher_id', $actor?->id);
                })->orWhereHas('guruKelasPantau', function ($query) use ($actor) {
                    $query->where('academic_year_id', $this->selectedAcademicYearId)
                          ->where('teacher_id', $actor?->id);
                });
            })->orderBy('name', 'asc')->get();
        } else {
            $this->classes = Kelas::whereHas('kelasAjarans', function ($query) {
                $query->where('academic_year_id', $this->selectedAcademicYearId);
            })->orderBy('name', 'asc')->get();
        }

        if ($this->classes->isNotEmpty()) {
            if (! collect($this->classes)->contains('id', $this->selectedClassId)) {
                $this->selectedClassId = collect($this->classes)->first()->id;
            }
        } else {
            $this->selectedClassId = null;
            $this->todayStats = [];
        }

        $this->loadDashboardData();
    }

    public function updatedSelectedAcademicYearId()
    {
        $this->loadClasses();
    }

    public function updatedSelectedClassId()
    {
        $this->loadDashboardData();
    }

    public function updatedSelectedMonthYear()
    {
        $this->loadDashboardData();
    }

    public function loadDashboardData()
    {
        if (! $this->selectedClassId || ! $this->selectedAcademicYearId || ! $this->selectedMonthYear) {
            $this->students          = collect();
            $this->monthlyStats      = [];
            $this->classMonthlyStats = [];
            $this->todayStats        = [];
            return;
        }

        $parts = explode('-', $this->selectedMonthYear);
        if (count($parts) !== 2) return;

        $month = $parts[0];
        $year = (int)$parts[1];

        $service = app(PresensiSholatDhuhurService::class);
        $result = $service->getMonthlyMatrixData(
            $this->selectedAcademicYearId,
            $this->selectedClassId,
            $month,
            $year
        );

        $this->students          = $result['students'];
        $this->monthlyStats      = $result['monthlyStats'];
        $this->classMonthlyStats = $result['classMonthlyStats'];
        $this->todayStats        = $result['todayStats'];
        $this->daysInMonth       = $result['daysInMonth'];
        $this->todayDate         = $result['todayDate'];
    }

    // ==========================================
    // MODAL INPUT PRESENSI SHOLAT DHUHUR
    // ==========================================

    public function openInputModal()
    {
        $this->inputDate = $this->todayDate ?? Carbon::now('Asia/Jakarta')->toDateString();
        $this->loadStudentsForInput();
        $this->showInputModal = true;
    }

    public function updatedInputDate()
    {
        $this->loadStudentsForInput();
    }

    public function loadStudentsForInput()
    {
        if (! $this->selectedClassId || ! $this->selectedAcademicYearId || ! $this->inputDate) return;

        $sholatService = app(PresensiSholatDhuhurService::class);
        $dateObj = Carbon::parse($this->inputDate);
        $this->isInputDateHoliday = ! $sholatService->isHariSholatDhuhur($dateObj, $this->selectedClassId);
        $this->inputDateHolidayDesc = $sholatService->getHolidayDescription($dateObj, $this->selectedClassId) ?? '';

        // Ambil data sholat dhuhur yang sudah tersimpan untuk tanggal ini
        $existingSholat = PresensiSholatDhuhur::where('academic_year_id', $this->selectedAcademicYearId)
            ->where('class_id', $this->selectedClassId)
            ->whereDate('date', $this->inputDate)
            ->get()->keyBy('student_id');

        // Ambil presensi pagi hari ini untuk sinkronisasi otomatis
        $morningAtts = Presensi::where('academic_year_id', $this->selectedAcademicYearId)
            ->where('class_id', $this->selectedClassId)
            ->whereDate('date', $this->inputDate)
            ->get()->keyBy('student_id');

        $list = [];
        foreach ($this->students as $student) {
            $sholat = $existingSholat->get($student->id);
            $morning = $morningAtts->get($student->id);

            if ($sholat) {
                $status = $sholat->status;
                $ket = $sholat->keterangan;
                $isHalangan = str_contains((string)$ket, 'Halangan') || str_contains((string)$ket, 'Haid');
                $isPagiAbsent = false;
            } else {
                // SINKRONISASI PRESENSI PAGI:
                // Jika siswa alpa di pagi hari -> otomatis tidak hadir di sholat
                // Jika siswa sakit/izin di pagi hari -> otomatis ijin di sholat
                if ($morning) {
                    if ($morning->status === 'alpa') {
                        $status = 'tidak_hadir';
                        $ket = 'Alpa di presensi pagi';
                        $isPagiAbsent = true;
                    } elseif (in_array($morning->status, ['izin', 'sakit'])) {
                        $status = 'ijin';
                        $ket = ucfirst($morning->status) . ' di presensi pagi';
                        $isPagiAbsent = true;
                    } else {
                        $status = 'hadir';
                        $ket = '';
                        $isPagiAbsent = false;
                    }
                } else {
                    $status = 'hadir';
                    $ket = '';
                    $isPagiAbsent = false;
                }
                $isHalangan = false;
            }

            $list[] = [
                'id'             => $student->id,
                'name'           => $student->name,
                'nisn'           => $student->nisn,
                'gender'         => $student->gender,
                'avatar_url'     => $student->avatar_url,
                'status'         => $status,
                'keterangan'     => $ket,
                'is_halangan'    => $isHalangan,
                'is_pagi_absent' => $isPagiAbsent,
            ];
        }

        $this->inputStudents = $list;
    }

    public function setAllStatus(string $status)
    {
        foreach ($this->inputStudents as $idx => $student) {
            // Jika siswa berhalangan/haid atau sakit pagi, pertahankan kecuali diubah manual
            $this->inputStudents[$idx]['status'] = $status;
            if ($status === 'hadir') {
                $this->inputStudents[$idx]['is_halangan'] = false;
            }
        }
    }

    public function toggleHalangan(int $index)
    {
        if (! isset($this->inputStudents[$index])) return;

        $current = $this->inputStudents[$index]['is_halangan'] ?? false;
        $newVal = ! $current;
        $this->inputStudents[$index]['is_halangan'] = $newVal;

        if ($newVal) {
            $this->inputStudents[$index]['status'] = 'ijin';
            $this->inputStudents[$index]['keterangan'] = 'Halangan / Haid';
        } else {
            $this->inputStudents[$index]['status'] = 'hadir';
            if ($this->inputStudents[$index]['keterangan'] === 'Halangan / Haid') {
                $this->inputStudents[$index]['keterangan'] = '';
            }
        }
    }

    public function tarikDariPresensiPagi()
    {
        $this->loadStudentsForInput();
        $this->dispatch('notify', [
            'type'    => 'info',
            'message' => 'Status berhasil diselaraskan dengan presensi pagi.',
        ]);
    }

    public function saveInput()
    {
        if (! $this->selectedClassId || ! $this->selectedAcademicYearId || ! $this->inputDate) return;

        $teacherId = Auth::user()->teacher?->id;

        // Ambil enrollment id aktif untuk setiap siswa
        $enrollmentMap = EnrollmentSiswa::where('class_id', $this->selectedClassId)
            ->where('academic_year_id', $this->selectedAcademicYearId)
            ->where('status', 'aktif')
            ->pluck('id', 'student_id')
            ->toArray();

        foreach ($this->inputStudents as $stData) {
            $enrId = $enrollmentMap[$stData['id']] ?? null;

            PresensiSholatDhuhur::updateOrCreate(
                [
                    'student_id' => $stData['id'],
                    'date'       => $this->inputDate,
                ],
                [
                    'academic_year_id'    => $this->selectedAcademicYearId,
                    'class_id'            => $this->selectedClassId,
                    'enrollment_id'       => $enrId,
                    'status'              => $stData['status'],
                    'keterangan'          => $stData['keterangan'] ?: ($stData['is_halangan'] ? 'Halangan / Haid' : null),
                    'input_by_teacher_id' => $teacherId,
                ]
            );
        }

        $this->showInputModal = false;
        $this->loadDashboardData();

        $this->dispatch('notify', [
            'type'    => 'success',
            'message' => 'Presensi Sholat Dhuhur tanggal ' . Carbon::parse($this->inputDate)->translatedFormat('d F Y') . ' berhasil disimpan!',
        ]);
    }

    // ==========================================
    // MODAL CETAK LAPORAN
    // ==========================================

    public function openCetakModal()
    {
        $this->cetakBulanYear = $this->selectedMonthYear ?? date('m-Y');
        $this->cetakSemester = (int)date('m') >= 7 ? 'ganjil' : 'genap';
        $this->cetakJenis = 'bulanan';
        $this->showCetakModal = true;
    }

    private function getCetakDateRange(): array
    {
        $ay = TahunAjaran::find($this->selectedAcademicYearId);
        $sy = $ay ? $ay->start_year : (int)date('Y');
        $ey = $ay ? $ay->end_year : ((int)date('Y') + 1);

        if ($this->cetakJenis === 'bulanan') {
            $parts = explode('-', $this->cetakBulanYear);
            $month = $parts[0] ?? date('m');
            $year = $parts[1] ?? date('Y');
            $start = Carbon::createFromDate($year, $month, 1)->startOfMonth()->toDateString();
            $end   = Carbon::createFromDate($year, $month, 1)->endOfMonth()->toDateString();
            $label = Carbon::createFromDate($year, $month, 1)->translatedFormat('F Y');
            return ['start' => $start, 'end' => $end, 'label' => $label];
        } else {
            if ($this->cetakSemester === 'ganjil') {
                $start = Carbon::createFromDate($sy, 7, 1)->startOfMonth()->toDateString();
                $end   = Carbon::createFromDate($sy, 12, 31)->endOfMonth()->toDateString();
                $label = 'Semester Ganjil ' . $sy . '/' . $ey;
            } else {
                $start = Carbon::createFromDate($ey, 1, 1)->startOfMonth()->toDateString();
                $end   = Carbon::createFromDate($ey, 6, 30)->endOfMonth()->toDateString();
                $label = 'Semester Genap ' . $sy . '/' . $ey;
            }
            return ['start' => $start, 'end' => $end, 'label' => $label];
        }
    }

    public function downloadCetakPdf()
    {
        if (! $this->selectedClassId || ! $this->selectedAcademicYearId) return;

        $range   = $this->getCetakDateRange();
        $kelas   = Kelas::find($this->selectedClassId);
        $sekolah = PengaturanSekolah::current();

        $className = $kelas?->name ?? 'Kelas';
        $safeClassName = str_replace(['/', '\\'], '-', $className);
        $safeLabel = str_replace([' ', '/', '\\'], ['_', '-', '-'], $range['label']);
        $fileName  = 'Laporan_Sholat_Dhuhur_' . $safeClassName . '_' . $safeLabel . '.pdf';

        $service = app(PresensiSholatDhuhurService::class);

        if ($this->cetakJenis === 'bulanan') {
            $parts = explode('-', $this->cetakBulanYear);
            $month = $parts[0] ?? date('m');
            $year = (int)($parts[1] ?? date('Y'));

            $result = $service->getMonthlyMatrixData(
                $this->selectedAcademicYearId,
                $this->selectedClassId,
                $month,
                $year
            );

            $pdf = Pdf::loadView('pdf.laporan-sholat-dhuhur-matrix', [
                'students'          => $result['students'],
                'monthlyStats'      => $result['monthlyStats'],
                'classMonthlyStats' => $result['classMonthlyStats'],
                'daysInMonth'       => $result['daysInMonth'],
                'periodeLabel'      => $range['label'],
                'kelas'             => $kelas,
                'sekolah'           => $sekolah,
                'waliKelas'         => Auth::user()->teacher,
                'generatedAt'       => now()->locale('id')->translatedFormat('l, d F Y H:i'),
            ])->setPaper('a4', 'landscape');

            return response()->streamDownload(
                fn () => print($pdf->output()),
                $fileName
            );
        }

        // Semester
        $result = $service->getSemesterYearlyData(
            $this->selectedAcademicYearId,
            $this->selectedClassId,
            $range['start'],
            $range['end']
        );

        $pdf = Pdf::loadView('pdf.laporan-sholat-dhuhur-range', [
            'studentsData' => $result['studentsData'],
            'monthsList'   => $result['monthsList'],
            'periodeLabel' => $range['label'],
            'kelas'        => $kelas,
            'sekolah'      => $sekolah,
            'waliKelas'    => Auth::user()->teacher,
            'generatedAt'  => now()->locale('id')->translatedFormat('l, d F Y H:i'),
        ])->setPaper('a4', 'landscape');

        return response()->streamDownload(
            fn () => print($pdf->output()),
            $fileName
        );
    }

    public function render()
    {
        return view('livewire.portal-guru.sholat-dhuhur.wali-kelas-sholat-dhuhur');
    }
}
