<?php

namespace App\Livewire\PortalGuru\SholatDhuhur;

use App\Exports\RekapSholatDhuhurHarianExport;
use App\Models\EnrollmentSiswa;
use App\Models\Guru;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\Pengajaran;
use App\Models\PengaturanSekolah;
use App\Models\Presensi;
use App\Models\PresensiNotificationSetting;
use App\Models\PresensiSholatDhuhur;
use App\Models\TahunAjaran;
use App\Services\PresensiSholatDhuhurService;
use App\Services\WhatsAppGatewayService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;

#[Layout('components.layouts.portal')]
class WaliKelasSholatDhuhur extends Component
{
    public $classes = [];
    public $selectedClassId;
    public $selectedMonthYear;
    public $availableMonths = [];
    public $academicYears = [];
    public $selectedAcademicYearId;

    // Koordinator Sholat Dhuhur
    public $selectedKoordinatorId;
    public $koordinators = [];

    public $students = [];
    public $monthlyStats = [];
    public $classMonthlyStats = [];
    public $todayStats = [];
    public $todayAttendances = [];
    public $daysInMonth = 0;
    public $todayDate;
    public $selectedDailyDate;

    // Modal Input Harian
    public $showInputModal = false;
    public $inputDate;
    public $isInputDateHoliday = false;
    public $inputDateHolidayDesc = '';
    public $inputStudents = [];
    public $bulkStatus = '';

    // Modal Cetak Laporan
    public $showCetakModal = false;
    public $cetakJenis = 'bulanan'; // 'bulanan' | 'semester'
    public $cetakBulanYear = '';
    public $cetakSemester = 'ganjil';

    public function mount()
    {
        abort_if(!(\App\Models\PengaturanSekolah::current()->enable_sholat_dhuhur ?? true), 403, 'Fitur Sholat Dhuhur sedang dinonaktifkan.');

        $user = Auth::user();
        if (! $user || (! $user->isWaliKelasAktif() && ! $user->hasAnyRole(['super_admin', 'admin_presensi']))) {
            abort(403, 'Akses ditolak: Menu ini khusus Wali Kelas dan Administrator.');
        }

        $this->academicYears = TahunAjaran::where('status', 'aktif')->orderBy('start_year', 'desc')->get();
        $activeYear = TahunAjaran::where('status', 'aktif')->first() ?? $this->academicYears->first();
        if ($activeYear) {
            $this->selectedAcademicYearId = $activeYear->id;
        }

        $this->selectedDailyDate = Carbon::today('Asia/Jakarta')->toDateString();

        $this->loadKoordinators();
        $this->loadClasses();
    }

    public function loadKoordinators()
    {
        $mapelPaiIds = MataPelajaran::where('nama_mapel', 'like', '%Agama Islam%')
            ->orWhere('kode_mapel', 'PAIBP')
            ->pluck('id');

        $paiTeacherIds = Pengajaran::whereIn('mata_pelajaran_id', $mapelPaiIds)
            ->distinct()
            ->pluck('teacher_id')
            ->filter()
            ->toArray();

        $pai = Guru::whereIn('id', $paiTeacherIds)->orderBy('name')->get(['id', 'name', 'nip']);
        $others = Guru::whereNotIn('id', $paiTeacherIds)->orderBy('name')->get(['id', 'name', 'nip']);

        $this->koordinators = [
            'pai'    => $pai->toArray(),
            'others' => $others->toArray(),
        ];

        if (! $this->selectedKoordinatorId) {
            $defaultPai = $pai->first(fn($g) => stripos($g->name, 'ASIS') !== false) ?? $pai->first();
            $this->selectedKoordinatorId = $defaultPai ? $defaultPai->id : ($others->first()?->id ?? null);
        }
    }

    public function getSelectedKoordinator(): ?Guru
    {
        if ($this->selectedKoordinatorId) {
            $found = Guru::find($this->selectedKoordinatorId);
            if ($found) {
                return $found;
            }
        }

        return Guru::where('name', 'like', '%ASIS%')->first() ?? Guru::first();
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
            $this->todayAttendances = [];
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
            $this->todayAttendances  = [];
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
        $this->todayAttendances  = $result['todayAttendances'] ?? [];
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

        $list = [];
        foreach ($this->students as $student) {
            $sholat = $existingSholat->get($student->id);

            // Cek apakah siswa Muslim — jika religion kosong, dianggap Islam
            $religion = strtolower(trim($student->religion ?? ''));
            $isNonMuslim = !empty($student->religion) && $religion !== 'islam';

            if ($isNonMuslim) {
                // Siswa non-Muslim: tidak bisa diinput, tampilkan sebagai read-only
                $list[] = [
                    'id'             => $student->id,
                    'name'           => $student->name,
                    'nisn'           => $student->nisn,
                    'gender'         => $student->gender,
                    'religion'       => $student->religion,
                    'avatar_url'     => $student->avatar_url,
                    'status'         => 'non_muslim',
                    'keterangan'     => 'Non-Muslim (' . $student->religion . ') — tidak wajib sholat dhuhur',
                    'is_halangan'    => false,
                    'is_pagi_absent' => false,
                    'is_non_muslim'  => true,
                ];
                continue;
            }

            if ($sholat) {
                $status = $sholat->status;
                $ket = $sholat->keterangan;
                $isHalangan = str_contains((string)$ket, 'Halangan') || str_contains((string)$ket, 'Haid');
                if ($isHalangan) {
                    $status = 'haid';
                }
            } else {
                // Jangan otomatis set 'hadir' jika belum ada data presensi sholat tersimpan!
                $status = '';
                $ket = '';
                $isHalangan = false;
            }

            $list[] = [
                'id'             => $student->id,
                'name'           => $student->name,
                'nisn'           => $student->nisn,
                'gender'         => $student->gender,
                'religion'       => $student->religion,
                'avatar_url'     => $student->avatar_url,
                'status'         => $status,
                'keterangan'     => $ket,
                'is_halangan'    => $isHalangan,
                'is_pagi_absent' => false,
                'is_non_muslim'  => false,
            ];
        }


        $this->inputStudents = $list;
        $this->bulkStatus = '';
    }

    public function updatedBulkStatus($value)
    {
        $this->applyBulkStatus($value);
    }

    public function applyBulkStatus($status)
    {
        if (empty($status) || $this->isInputDateHoliday) return;

        foreach ($this->inputStudents as $idx => $student) {
            $this->inputStudents[$idx]['status'] = $status;
            if ($status === 'hadir') {
                $this->inputStudents[$idx]['is_halangan'] = false;
                if (($this->inputStudents[$idx]['keterangan'] ?? '') === 'Halangan / Haid') {
                    $this->inputStudents[$idx]['keterangan'] = '';
                }
            } elseif ($status === 'tidak_hadir') {
                $this->inputStudents[$idx]['is_halangan'] = false;
                if (($this->inputStudents[$idx]['keterangan'] ?? '') === 'Halangan / Haid') {
                    $this->inputStudents[$idx]['keterangan'] = '';
                }
            }
        }
    }

    public function updatedInputStudents($value, $key)
    {
        if ($this->isInputDateHoliday) return;

        $parts = explode('.', $key);
        if (count($parts) === 2 && $parts[1] === 'status') {
            $index = (int)$parts[0];
            if ($value === 'haid') {
                $this->inputStudents[$index]['is_halangan'] = true;
                $this->inputStudents[$index]['keterangan'] = 'Halangan / Haid';
            } else {
                $this->inputStudents[$index]['is_halangan'] = false;
                if (($this->inputStudents[$index]['keterangan'] ?? '') === 'Halangan / Haid') {
                    $this->inputStudents[$index]['keterangan'] = '';
                }
            }
        }
    }

    public function setAllStatus(string $status)
    {
        if ($this->isInputDateHoliday) return;
        $this->bulkStatus = $status;
        $this->applyBulkStatus($status);
    }

    public function toggleHalangan(int $index)
    {
        if ($this->isInputDateHoliday || ! isset($this->inputStudents[$index])) return;

        $current = ($this->inputStudents[$index]['status'] ?? '') === 'haid';
        if (! $current) {
            $this->inputStudents[$index]['status'] = 'haid';
            $this->inputStudents[$index]['is_halangan'] = true;
            $this->inputStudents[$index]['keterangan'] = 'Halangan / Haid';
        } else {
            $this->inputStudents[$index]['status'] = 'hadir';
            $this->inputStudents[$index]['is_halangan'] = false;
            if (($this->inputStudents[$index]['keterangan'] ?? '') === 'Halangan / Haid') {
                $this->inputStudents[$index]['keterangan'] = '';
            }
        }
    }

    public function sinkronPresensiPagiSemuaKelas()
    {
        $targetDate = $this->todayDate ?? Carbon::today('Asia/Jakarta')->toDateString();
        $teacherId = Auth::user()->teacher?->id;

        $service = app(PresensiSholatDhuhurService::class);
        $result = $service->syncAllClassesFromMorning($targetDate, $teacherId);

        $this->loadDashboardData();

        if ($result['status'] === 'success') {
            $this->dispatch('notify', [
                'type'    => 'success',
                'message' => $result['message'],
            ]);
        } elseif ($result['status'] === 'holiday') {
            $this->dispatch('notify', [
                'type'    => 'info',
                'message' => $result['message'],
            ]);
        } else {
            $this->dispatch('notify', [
                'type'    => 'error',
                'message' => $result['message'],
            ]);
        }
    }

    public function tarikDariPresensiPagi()
    {
        if (! $this->selectedClassId || ! $this->selectedAcademicYearId || ! $this->inputDate) return;

        $sholatService = app(PresensiSholatDhuhurService::class);
        $dateObj = Carbon::parse($this->inputDate);
        if ($this->isInputDateHoliday || ! $sholatService->isHariSholatDhuhur($dateObj, $this->selectedClassId)) {
            $desc = $sholatService->getHolidayDescription($dateObj, $this->selectedClassId) ?? ($this->inputDateHolidayDesc ?: 'Libur');
            $this->isInputDateHoliday = true;
            $this->inputDateHolidayDesc = $desc;
            $this->dispatch('notify', [
                'type'    => 'error',
                'message' => 'Tidak dapat menyinkronkan presensi sholat dhuhur pada hari libur (' . $desc . ')!',
            ]);
            return;
        }

        $morningAtts = Presensi::where('academic_year_id', $this->selectedAcademicYearId)
            ->where('class_id', $this->selectedClassId)
            ->whereDate('date', $this->inputDate)
            ->whereIn('status', ['alpa', 'izin', 'sakit'])
            ->get()->keyBy('student_id');

        if ($morningAtts->isEmpty()) {
            $this->dispatch('notify', [
                'type'    => 'info',
                'message' => 'Tidak ditemukan data Alpa, Izin, atau Sakit di presensi pagi untuk kelas ini pada tanggal ' . Carbon::parse($this->inputDate)->translatedFormat('d F Y') . '.',
            ]);
            return;
        }

        $syncCount = 0;
        foreach ($this->inputStudents as $idx => $st) {
            // Lewati siswa non-Muslim
            if (!empty($st['is_non_muslim'])) continue;

            $morning = $morningAtts->get($st['id']);
            if ($morning) {
                if ($morning->status === 'alpa') {
                    $this->inputStudents[$idx]['status'] = 'tidak_hadir';
                    $this->inputStudents[$idx]['keterangan'] = 'Alpa di presensi pagi';
                    $this->inputStudents[$idx]['is_halangan'] = false;
                    $this->inputStudents[$idx]['is_pagi_absent'] = true;
                    $syncCount++;
                } elseif (in_array($morning->status, ['izin', 'sakit'])) {
                    $this->inputStudents[$idx]['status'] = 'ijin';
                    $this->inputStudents[$idx]['keterangan'] = ucfirst($morning->status) . ' di presensi pagi';
                    $this->inputStudents[$idx]['is_halangan'] = false;
                    $this->inputStudents[$idx]['is_pagi_absent'] = true;
                    $syncCount++;
                }
            }
        }

        $this->dispatch('notify', [
            'type'    => 'success',
            'message' => "Berhasil menyelaraskan {$syncCount} data ketidakhadiran (Alpa/Izin/Sakit) dari presensi pagi.",
        ]);
    }

    public function saveInput()
    {
        if (! $this->selectedClassId || ! $this->selectedAcademicYearId || ! $this->inputDate) return;

        $sholatService = app(PresensiSholatDhuhurService::class);
        $dateObj = Carbon::parse($this->inputDate);
        if ($this->isInputDateHoliday || ! $sholatService->isHariSholatDhuhur($dateObj, $this->selectedClassId)) {
            $desc = $sholatService->getHolidayDescription($dateObj, $this->selectedClassId) ?? ($this->inputDateHolidayDesc ?: 'Libur');
            $this->isInputDateHoliday = true;
            $this->inputDateHolidayDesc = $desc;
            $this->dispatch('notify', [
                'type'    => 'error',
                'message' => 'Tidak dapat menyimpan presensi sholat dhuhur pada hari libur (' . $desc . ')!',
            ]);
            return;
        }

        $teacherId = Auth::user()->teacher?->id;

        // Ambil enrollment id aktif untuk setiap siswa
        $enrollmentMap = EnrollmentSiswa::where('class_id', $this->selectedClassId)
            ->where('academic_year_id', $this->selectedAcademicYearId)
            ->where('status', 'aktif')
            ->pluck('id', 'student_id')
            ->toArray();

        $savedCount = 0;
        foreach ($this->inputStudents as $stData) {
            // Skip siswa non-Muslim — tidak disimpan presensi sholat
            if (!empty($stData['is_non_muslim'])) continue;

            if (empty($stData['status']) || empty($stData['id'])) continue;

            $enrId = $enrollmentMap[$stData['id']] ?? null;
            $rawStatus = $stData['status'];
            $isHalangan = ($rawStatus === 'haid') || !empty($stData['is_halangan']);
            $dbStatus = ($rawStatus === 'haid') ? 'ijin' : $rawStatus;

            PresensiSholatDhuhur::updateOrCreate(
                [
                    'student_id' => $stData['id'],
                    'date'       => $this->inputDate,
                ],
                [
                    'academic_year_id'    => $this->selectedAcademicYearId,
                    'class_id'            => $this->selectedClassId,
                    'enrollment_id'       => $enrId,
                    'status'              => $dbStatus,
                    'keterangan'          => $stData['keterangan'] ?: ($isHalangan ? 'Halangan / Haid' : null),
                    'input_by_teacher_id' => $teacherId,
                ]
            );
            $savedCount++;
        }

        if ($savedCount === 0) {
            $this->dispatch('notify', [
                'type'    => 'warning',
                'message' => 'Pilih status kehadiran siswa terlebih dahulu atau gunakan Set Massal.',
            ]);
            return;
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

    public function downloadPdf()
    {
        return $this->downloadCetakPdf();
    }

    public function downloadCetakPdf()
    {
        if (! $this->selectedClassId || ! $this->selectedAcademicYearId) return;

        $range       = $this->getCetakDateRange();
        $kelas       = Kelas::find($this->selectedClassId);
        $sekolah     = PengaturanSekolah::current();
        $koordinator = $this->getSelectedKoordinator();

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
                'waliKelas'         => Auth::user()->teacher ?? $kelas?->waliKelas($this->selectedAcademicYearId),
                'koordinator'       => $koordinator,
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
            'waliKelas'    => Auth::user()->teacher ?? $kelas?->waliKelas($this->selectedAcademicYearId),
            'koordinator'  => $koordinator,
            'generatedAt'  => now()->locale('id')->translatedFormat('l, d F Y H:i'),
        ])->setPaper('a4', 'landscape');

        return response()->streamDownload(
            fn () => print($pdf->output()),
            $fileName
        );
    }

    // ==========================================
    // EKSPOR LAPORAN HARIAN SHOLAT DHUHUR
    // (Rekap Kelas PDF, Export Excel, Rekap Harian PDF)
    // ==========================================

    public function downloadRekapKelasPdf()
    {
        $targetDate = $this->selectedDailyDate ?: ($this->todayDate ?? Carbon::today('Asia/Jakarta')->toDateString());
        $ayId = $this->selectedAcademicYearId;
        $sholatService = app(PresensiSholatDhuhurService::class);
        $summary = $sholatService->getDailySchoolSummaryData($ayId, $targetDate);

        $sekolah = PengaturanSekolah::current();
        $koordinator = $this->getSelectedKoordinator();
        $dateFormatted = Carbon::parse($targetDate)->locale('id')->translatedFormat('l, d F Y');

        $pdf = Pdf::loadView('pdf.laporan-sholat-dhuhur-rekap-per-kelas', [
            'stats'                 => $summary['stats'],
            'classesSummary'        => $summary['classesSummary'],
            'sekolah'               => $sekolah,
            'koordinator'           => $koordinator,
            'tahunAjaran'           => $summary['academic_year'],
            'selectedDateFormatted' => $dateFormatted,
            'date'                  => $targetDate,
            'isHoliday'             => $summary['is_holiday'],
            'holidayDesc'           => $summary['holiday_desc'],
            'unattendedStudents'    => $summary['unattendedStudents'],
            'allStudents'           => $summary['allStudents'],
            'generatedAt'           => now('Asia/Jakarta')->locale('id')->translatedFormat('l, d F Y H:i') . ' WIB',
        ])->setPaper('a4', 'landscape');

        $safeDate = str_replace('-', '_', $targetDate);
        $fileName = "Rekap_Sholat_Dhuhur_Per_Kelas_{$safeDate}.pdf";

        return response()->streamDownload(
            fn () => print($pdf->output()),
            $fileName
        );
    }

    public function exportExcel()
    {
        $targetDate = $this->selectedDailyDate ?: ($this->todayDate ?? Carbon::today('Asia/Jakarta')->toDateString());
        $ayId = $this->selectedAcademicYearId;
        $sholatService = app(PresensiSholatDhuhurService::class);
        $summary = $sholatService->getDailySchoolSummaryData($ayId, $targetDate);
        $koordinator = $this->getSelectedKoordinator();

        $safeDate = str_replace('-', '_', $targetDate);
        $fileName = "Rekap_Sholat_Dhuhur_Harian_{$safeDate}.xlsx";

        return Excel::download(
            new RekapSholatDhuhurHarianExport(
                $targetDate,
                $ayId,
                $summary['stats'],
                $summary['classesSummary'],
                $summary['unattendedStudents'],
                $summary['allStudents'],
                $koordinator
            ),
            $fileName
        );
    }

    public function downloadRekapHarianPdf()
    {
        $targetDate = $this->selectedDailyDate ?: ($this->todayDate ?? Carbon::today('Asia/Jakarta')->toDateString());
        $ayId = $this->selectedAcademicYearId;
        $sholatService = app(PresensiSholatDhuhurService::class);
        $summary = $sholatService->getDailySchoolSummaryData($ayId, $targetDate);

        $sekolah = PengaturanSekolah::current();
        $koordinator = $this->getSelectedKoordinator();
        $dateFormatted = Carbon::parse($targetDate)->locale('id')->translatedFormat('l, d F Y');

        $pdf = Pdf::loadView('pdf.laporan-sholat-dhuhur-rekap-harian', [
            'stats'                 => $summary['stats'],
            'classesSummary'        => $summary['classesSummary'],
            'gradeGroups'           => $summary['gradeGroups'],
            'sekolah'               => $sekolah,
            'koordinator'           => $koordinator,
            'tahunAjaran'           => $summary['academic_year'],
            'selectedDateFormatted' => $dateFormatted,
            'date'                  => $targetDate,
            'isHoliday'             => $summary['is_holiday'],
            'holidayDesc'           => $summary['holiday_desc'],
            'unattendedStudents'    => $summary['unattendedStudents'],
            'allStudents'           => $summary['allStudents'],
            'generatedAt'           => now('Asia/Jakarta')->locale('id')->translatedFormat('l, d F Y H:i') . ' WIB',
        ])->setPaper('a4', 'landscape');

        $safeDate = str_replace('-', '_', $targetDate);
        $fileName = "Rekap_Presensi_Sholat_Dhuhur_Harian_{$safeDate}.pdf";

        return response()->streamDownload(
            fn () => print($pdf->output()),
            $fileName
        );
    }

    public function sendWaDirect(string $studentId, string $targetPhone, string $messageText, string $type = 'sholat_dhuhur'): void
    {
        $cleanPhone = preg_replace('/[^0-9]/', '', $targetPhone);
        if (empty($cleanPhone)) {
            $this->dispatch('notify', [
                'type'    => 'error',
                'message' => 'Nomor WhatsApp orang tua tidak valid atau masih kosong.',
            ]);
            return;
        }

        if (str_starts_with($cleanPhone, '0')) {
            $cleanPhone = '62' . substr($cleanPhone, 1);
        } elseif (str_starts_with($cleanPhone, '8')) {
            $cleanPhone = '62' . $cleanPhone;
        }

        $waService = app(WhatsAppGatewayService::class);
        $sent = $waService->sendMessage(
            toNumber: $cleanPhone,
            message: $messageText,
            relatedType: 'presensi_' . $type,
            relatedId: $studentId,
            recipientType: 'ortu',
            logId: null,
            ignoreDelay: true
        );

        if ($sent) {
            $this->dispatch('notify', [
                'type'    => 'success',
                'message' => "Pesan WhatsApp berhasil dikirim ke nomor {$cleanPhone}!",
            ]);
            $this->dispatch('wa-modal-close');
        } else {
            $this->dispatch('notify', [
                'type'    => 'error',
                'message' => 'Gagal mengirim via sistem gateway WhatsApp. Anda dapat menggunakan tombol "Buka di WhatsApp (Web / HP)" sebagai alternatif.',
            ]);
        }
    }

    public function render()
    {
        return view('livewire.portal-guru.sholat-dhuhur.wali-kelas-sholat-dhuhur');
    }
}
