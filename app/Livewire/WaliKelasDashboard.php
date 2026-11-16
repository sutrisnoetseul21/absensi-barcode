<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Kelas;
use App\Models\TahunAjaran;
use App\Models\Presensi;
use App\Services\PresensiRekapService;
use App\Models\PengaturanSekolah;
use App\Models\PresensiNotificationSetting;
use App\Services\WhatsAppGatewayService;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Exports\PresensiMatrixExport;
use App\Exports\LaporanPresensiRangeExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.portal')]
class WaliKelasDashboard extends Component
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
    public $alerts = [];
    public $todayStats = [];
    public $todayAttendances = [];

    public $daysInMonth = 0;
    public $todayDate;

    // Manual Input Modals
    public $showInputModal = false;
    public $inputDate;
    public $inputStudents = [];
    public $bulkStatusDatang = '';
    public $bulkStatusPulang = '';
    public bool $enableCheckout = true;

    // Cetak Laporan Modals
    public $showCetakModal = false;
    public $cetakJenis = 'bulanan';
    public $cetakBulanYear = '';
    public $cetakSemester = 'ganjil';
    public bool $cetakIncludeCheckout = false;
    public string $cetakReportFormat = 'singkatan'; // singkatan | lengkap | waktu
    public string $checkinHm = '07:00';
    public int $lateThresholdMinutes = 15;
    public string $lateCutoffHm = '07:15';

    public function mount()
    {
        $settings = PengaturanSekolah::current();
        $this->enableCheckout = $settings?->enable_checkout ?? true;
        $eval = Presensi::evaluateArrivalTime(null, null, $settings);
        $this->checkinHm = $eval['checkin_hm'];
        $this->lateThresholdMinutes = $eval['threshold'];
        $this->lateCutoffHm = $eval['cutoff_hm'];
        $user = Auth::user();
        if (!$user || !$user->isWaliKelasAktif()) {
            abort(403, 'Akses ditolak: Anda tidak memiliki penugasan kelas binaan aktif.');
        }

        $this->academicYears  = TahunAjaran::where('status', 'aktif')->orderBy('start_year', 'desc')->get();

        $activeYear = TahunAjaran::where('status', 'aktif')->first() ?? $this->academicYears->first();
        if ($activeYear) {
            $this->selectedAcademicYearId = $activeYear->id;
        }

        $this->loadClasses();
    }
    
    private function generateAvailableMonths()
    {
        $this->availableMonths = [];
        if (!$this->selectedAcademicYearId) return;
        
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
        
        if (!$this->selectedAcademicYearId) {
            $this->classes        = [];
            $this->selectedClassId = null;
            return;
        }

        if (!Auth::user()->hasAnyRole(['super_admin', 'admin_presensi'])) {
            if (Auth::user()->hasRole('wali_kelas') && Auth::user()->teacher === null) {
                abort(403, 'Akses Ditolak: Data profil guru Anda belum ditautkan oleh Admin.');
            }
        }

        $isAdminMode = Auth::user()->hasAnyRole(['super_admin', 'admin_presensi']);
        $hasBypass   = Auth::user()->can('portal_guru:akses_semua_kelas');

        if (!$isAdminMode && !$hasBypass) {
            $actor = Auth::user()->teacher;
            $this->classes = Kelas::where(function ($q) use ($actor) {
                $q->whereHas('kelasAjarans', function ($query) use ($actor) {
                    $query->where('academic_year_id', $this->selectedAcademicYearId)
                          ->where('teacher_id', $actor->id);
                })->orWhereHas('guruKelasPantau', function ($query) use ($actor) {
                    $query->where('academic_year_id', $this->selectedAcademicYearId)
                          ->where('teacher_id', $actor->id);
                });
            })->orderBy('name', 'asc')->get();
        } else {
            $this->classes = Kelas::whereHas('kelasAjarans', function ($query) {
                $query->where('academic_year_id', $this->selectedAcademicYearId);
            })->orderBy('name', 'asc')->get();
        }

        if ($this->classes->isNotEmpty()) {
            if (!collect($this->classes)->contains('id', $this->selectedClassId)) {
                $this->selectedClassId = collect($this->classes)->first()->id;
            }
        } else {
            $this->selectedClassId = null;
            $this->todayStats      = [];
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
        if (!Auth::user()->hasAnyRole(['super_admin', 'admin_presensi'])) {
            if (Auth::user()->hasRole('wali_kelas') && Auth::user()->teacher === null) {
                abort(403, 'Akses Ditolak: Data profil guru tidak lengkap.');
            }
        }

        $isAdminMode = Auth::user()->hasAnyRole(['super_admin', 'admin_presensi']);
        $hasBypass   = Auth::user()->can('portal_guru:akses_semua_kelas');
        
        if (!$isAdminMode && !$hasBypass) {
            if (!collect($this->classes)->contains('id', $this->selectedClassId)) {
                abort(403, 'Unauthorized action. Anda tidak memiliki akses ke kelas ini.');
            }
        }
        $this->loadDashboardData();
    }

    public function updatedSelectedMonthYear()
    {
        $this->loadDashboardData();
    }

    private function syncArrivalEvaluation(int $index): void
    {
        if (!empty($this->inputStudents[$index]['is_locked'])) {
            return;
        }
        $status = $this->inputStudents[$index]['status'] ?? '';
        $scanTime = $this->inputStudents[$index]['scan_time'] ?? '';
        $eval = Presensi::evaluateArrivalTime($status, $scanTime, null, $this->inputDate);

        $this->inputStudents[$index]['late_minutes'] = $eval['late_minutes'];
        $this->inputStudents[$index]['arrival_alarm'] = $eval['alarm'];
        $this->inputStudents[$index]['alarm_type'] = $eval['alarm_type'];
    }

    public function updated($property, $value)
    {
        if (str_starts_with($property, 'inputStudents.')) {
            $parts = explode('.', $property);
            if (count($parts) === 3) {
                $index = (int) $parts[1];
                $field = $parts[2];

                if ($field === 'status') {
                    if (in_array($value, ['hadir', 'telat'], true)) {
                        if (empty($this->inputStudents[$index]['scan_time'])) {
                            $this->inputStudents[$index]['scan_time'] = Presensi::defaultManualScanTime($value);
                        }
                        $this->syncArrivalEvaluation($index);
                    } elseif (in_array($value, ['izin', 'sakit', 'alpa'], true)) {
                        $this->inputStudents[$index]['status_pulang'] = $value;
                        $this->inputStudents[$index]['scan_time'] = '';
                        $this->inputStudents[$index]['scan_out_time'] = '';
                        $this->syncArrivalEvaluation($index);
                    } else {
                        $this->inputStudents[$index]['scan_time'] = '';
                        $this->inputStudents[$index]['scan_out_time'] = '';
                        $this->syncArrivalEvaluation($index);
                    }
                } elseif ($field === 'scan_time') {
                    $this->syncArrivalEvaluation($index);
                } elseif ($field === 'status_pulang') {
                    if ($value === 'pulang') {
                        if (empty($this->inputStudents[$index]['scan_out_time'])) {
                            $this->inputStudents[$index]['scan_out_time'] = Carbon::now('Asia/Jakarta')->format('H:i');
                        }
                    } else {
                        $this->inputStudents[$index]['scan_out_time'] = '';
                    }
                }
            }
        }
    }

    public function loadDashboardData()
    {
        if (!$this->selectedClassId || !$this->selectedAcademicYearId || !$this->selectedMonthYear) {
            $this->students          = collect();
            $this->monthlyStats      = [];
            $this->classMonthlyStats = [];
            $this->alerts            = [];
            $this->todayStats        = [];
            $this->todayAttendances  = [];
            return;
        }
        
        $parts = explode('-', $this->selectedMonthYear);
        if (count($parts) !== 2) return;
        
        $month = $parts[0];
        $year = (int)$parts[1];

        $service = app(PresensiRekapService::class);
        $result  = $service->getMonthlyCalendarData(
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
        $this->alerts            = $result['alerts'];
        $this->daysInMonth       = $result['daysInMonth'];
        $this->todayDate         = $result['todayDate'];
    }

    public $isInputDateHoliday = false;

    public function openInputModal()
    {
        $this->bulkStatusDatang = '';
        $this->bulkStatusPulang = '';
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
        if (!$this->selectedClassId || !$this->selectedAcademicYearId || !$this->inputDate) return;

        $kalenderService = app(\App\Services\KalenderSekolahService::class);
        $this->isInputDateHoliday = !$kalenderService->isHariSekolah(Carbon::parse($this->inputDate), $this->selectedClassId);

        $attendances = Presensi::with(['inputManualOleh', 'manualInputUser'])
            ->where('academic_year_id', $this->selectedAcademicYearId)
            ->where('class_id', $this->selectedClassId)
            ->where('date', $this->inputDate)
            ->get()->keyBy('student_id');

        $settings = PengaturanSekolah::current();
        $ruleInfo = Presensi::evaluateArrivalTime(null, null, $settings);
        $this->checkinHm = $ruleInfo['checkin_hm'];
        $this->lateThresholdMinutes = $ruleInfo['threshold'];
        $this->lateCutoffHm = $ruleInfo['cutoff_hm'];

        $list = [];
        foreach ($this->students as $student) {
            $att            = $attendances->get($student->id);
            // Kunci HANYA jika siswa absen langsung lewat scan kiosk (status hadir/telat, ada scan_time, dan bukan input manual)
            $isLocked       = ($att && $att->is_manual_input === false && !empty($att->scan_time) && in_array($att->status, ['hadir', 'telat']));
            $status         = $att ? $att->status : '';
            $scanTime       = ($att && $att->scan_time) ? substr((string) $att->scan_time, 0, 5) : '';
            $eval           = $isLocked ? null : Presensi::evaluateArrivalTime($status, $scanTime, $settings, $this->inputDate);

            $list[] = [
                'id'              => $student->id,
                'name'            => $student->name,
                'status'          => $status,
                'status_pulang'   => $att ? $att->status_pulang : '',
                'scan_time'       => $scanTime,
                'scan_out_time'   => ($att && $att->scan_out_time) ? substr((string) $att->scan_out_time, 0, 5) : '',
                'late_minutes'    => $isLocked ? ($att->late_minutes ?? 0) : ($status === 'telat' ? ($eval['late_minutes'] ?: ($att?->late_minutes ?? 0)) : 0),
                'arrival_alarm'   => $eval['alarm'] ?? null,
                'alarm_type'      => $eval['alarm_type'] ?? null,
                'is_manual_input' => $att ? $att->is_manual_input : null,
                'manual_audit'    => $att ? $att->getManualAuditInfo() : null,
                'note'            => $att ? $att->note : null,
                'is_locked'       => $isLocked,
            ];
        }
        $this->bulkStatusDatang = '';
        $this->bulkStatusPulang = '';
        $this->inputStudents = $list;
    }

    public function updatedBulkStatusDatang($value)
    {
        $this->applyBulkStatusDatang($value);
    }

    public function applyBulkStatusDatang($status)
    {
        if (empty($status)) return;

        $settings = PengaturanSekolah::current();
        $defaultTime = in_array($status, ['hadir', 'telat'], true)
            ? Presensi::defaultManualScanTime($status, $settings)
            : '';

        foreach ($this->inputStudents as $index => $student) {
            // Lewati siswa yang terkunci (hanya yang sudah absen langsung lewat portal-presensi/scan)
            if (!empty($student['is_locked'])) {
                continue;
            }

            $this->inputStudents[$index]['status'] = $status;
            if (in_array($status, ['izin', 'sakit', 'alpa'])) {
                $this->inputStudents[$index]['status_pulang'] = $status;
                $this->inputStudents[$index]['scan_time'] = '';
                $this->inputStudents[$index]['scan_out_time'] = '';
            } elseif (in_array($status, ['hadir', 'telat'], true) && empty($this->inputStudents[$index]['scan_time'])) {
                $this->inputStudents[$index]['scan_time'] = $defaultTime;
            }
            $this->syncArrivalEvaluation($index);
        }
    }

    public function updatedBulkStatusPulang($value)
    {
        $this->applyBulkStatusPulang($value);
    }

    public function applyBulkStatusPulang($status)
    {
        if (empty($status)) return;

        $nowWib = Carbon::now('Asia/Jakarta')->format('H:i');
        foreach ($this->inputStudents as $index => $student) {
            // Abaikan siswa yang status kedatangannya izin/sakit/alpa
            $currentStatus = $student['status'] ?? '';
            if (in_array($currentStatus, ['izin', 'sakit', 'alpa'])) {
                continue;
            }

            $this->inputStudents[$index]['status_pulang'] = $status;
            if ($status === 'pulang') {
                if (empty($this->inputStudents[$index]['scan_out_time'])) {
                    $this->inputStudents[$index]['scan_out_time'] = $nowWib;
                }
            } else {
                $this->inputStudents[$index]['scan_out_time'] = '';
            }
        }
    }

    public function saveManualInput()
    {
        if (!$this->selectedClassId || !$this->selectedAcademicYearId || !$this->inputDate) return;

        if ($this->isInputDateHoliday) {
            $this->dispatch('notify', [
                'type'    => 'error',
                'message' => 'Tidak dapat menyimpan presensi pada hari libur!',
            ]);
            return;
        }

        $settings = PengaturanSekolah::current();
        $alarmMessages = [];
        foreach ($this->inputStudents as $index => $data) {
            if (!empty($data['is_locked']) || !in_array($data['status'] ?? '', ['hadir', 'telat'], true)) {
                continue;
            }
            $eval = Presensi::evaluateArrivalTime($data['status'], $data['scan_time'] ?? '', $settings, $this->inputDate);
            $this->inputStudents[$index]['late_minutes'] = $eval['late_minutes'];
            $this->inputStudents[$index]['arrival_alarm'] = $eval['alarm'];
            $this->inputStudents[$index]['alarm_type'] = $eval['alarm_type'];
            if ($eval['alarm']) {
                $alarmMessages[] = ($data['name'] ?? 'Siswa') . ': ' . $eval['alarm'];
            }
        }

        if (!empty($alarmMessages)) {
            $this->dispatch('notify', [
                'type'    => 'error',
                'message' => 'Penyimpanan dibatalkan: ' . implode(' | ', $alarmMessages),
            ]);
            return;
        }

        foreach ($this->inputStudents as $index => $data) {
            if (empty($data['status']) || empty($data['id'])) continue;
            
            $studentId = $data['id'];


            // Cari enrollment_id
            $enrollment = \App\Models\EnrollmentSiswa::where('student_id', $studentId)
                ->where('academic_year_id', $this->selectedAcademicYearId)
                ->where('status', 'aktif')
                ->first();

            $existing = Presensi::where('student_id', $studentId)
                ->where('date', $this->inputDate)
                ->first();

            $isGuru = Auth::user()->hasRole('wali_kelas') && Auth::user()->teacher !== null;
            $newStatusPulang = empty($data['status_pulang']) ? null : $data['status_pulang'];

            // Jika status datang adalah izin, sakit, atau alpa, maka status pulang mengikuti & jam scan kosong
            if (in_array($data['status'], ['izin', 'sakit', 'alpa'])) {
                $newStatusPulang = $data['status'];
            }

            // Cek apakah data ini adalah hasil scan langsung lewat portal-presensi/scan (kiosk)
            $isKioskScan = ($existing && $existing->is_manual_input === false && !empty($existing->scan_time) && in_array($existing->status, ['hadir', 'telat']));
            $nowTime = Carbon::now('Asia/Jakarta')->format('H:i:00');

            if ($isKioskScan) {
                // Pertahankan status datang, keterlambatan, & jam scan datang asli dari mesin scanner kiosk
                $newLate = $existing->late_minutes;
                $data['status'] = $existing->status;
                $newScanTime = $existing->scan_time;
                $newScanOutTime = ($newStatusPulang === 'pulang')
                    ? (Presensi::normalizeTimeForStorage($data['scan_out_time'] ?? null)
                        ?? ($existing->scan_out_time ?: $nowTime))
                    : null;

                $oldOutHm = $existing->scan_out_time ? substr((string) $existing->scan_out_time, 0, 5) : null;
                $newOutHm = $newScanOutTime ? substr($newScanOutTime, 0, 5) : null;

                // Jika status pulang & jam pulang juga tidak berubah, lewati langsung secara hening
                if ($existing->status_pulang === $newStatusPulang && $oldOutHm === $newOutHm) {
                    continue;
                }
            } else {
                $newLate = ($data['status'] === 'telat') ? ($data['late_minutes'] ?: 0) : 0;
                if (in_array($data['status'], ['izin', 'sakit', 'alpa'])) {
                    $newScanTime = null;
                    $newScanOutTime = null;
                } else {
                    $newScanTime = Presensi::normalizeTimeForStorage($data['scan_time'] ?? null)
                        ?? ($existing && $existing->scan_time ? $existing->scan_time : $nowTime);
                    $newScanOutTime = ($newStatusPulang === 'pulang')
                        ? (Presensi::normalizeTimeForStorage($data['scan_out_time'] ?? null)
                            ?? ($existing && $existing->scan_out_time ? $existing->scan_out_time : $nowTime))
                        : null;
                }
            }

            $oldScanHm = ($existing && $existing->scan_time) ? substr((string) $existing->scan_time, 0, 5) : null;
            $newScanHm = $newScanTime ? substr($newScanTime, 0, 5) : null;
            $oldOutHm = ($existing && $existing->scan_out_time) ? substr((string) $existing->scan_out_time, 0, 5) : null;
            $newOutHm = $newScanOutTime ? substr($newScanOutTime, 0, 5) : null;

            // Jika data sudah ada dan tidak ada perubahan sama sekali, lewati penyimpanan agar tidak merusak data otomatis
            if (
                $existing &&
                $existing->status === $data['status'] &&
                $existing->late_minutes == $newLate &&
                $existing->status_pulang === $newStatusPulang &&
                $oldScanHm === $newScanHm &&
                $oldOutHm === $newOutHm
            ) {
                continue;
            }

            // Blokir Guru jika mengedit data kedatangan hasil scan langsung kiosk (hanya Admin yang boleh)
            // Tapi Bolehkan guru mengisi/mengubah status & jam pulang
            if ($isKioskScan && $isGuru) {
                if ($existing->status !== $data['status'] || $existing->late_minutes != $newLate) {
                    $this->dispatch('notify', [
                        'type'    => 'error',
                        'message' => "Gagal mengubah: Status kedatangan sudah terekam langsung oleh scanner presensi. Hubungi Admin untuk mengubah kehadiran datang.",
                    ]);
                    continue;
                }
            }

            $loginUser = Auth::user();
            $actor = $isGuru ? $loginUser->teacher : $loginUser;
            $actorType = $isGuru ? 'Guru' : 'Admin';
            $note = Presensi::buildManualAuditNote(
                $existing,
                $actorType,
                $actor ? $actor->name : 'Sistem',
                $loginUser?->email,
                $data['status'],
                (int) $newLate,
                $newStatusPulang,
                $newScanTime,
                $newScanOutTime
            );

            Presensi::updateOrCreate(
                [
                    'student_id'       => $studentId,
                    'class_id'         => $this->selectedClassId,
                    'academic_year_id' => $this->selectedAcademicYearId,
                    'date'             => $this->inputDate,
                ],
                [
                    'enrollment_id'   => $enrollment?->id,
                    'status'          => $data['status'],
                    'status_pulang'   => $newStatusPulang,
                    'late_minutes'    => $newLate,
                    // Jika sebelumnya adalah scan langsung kiosk dan guru hanya mengubah status_pulang, pertahankan is_manual_input = false.
                    // Jika diedit dari alpa otomatis / belum absen / manual, tandai is_manual_input = true.
                    'is_manual_input' => $isKioskScan ? false : true,
                    'manual_input_by_id'   => $actor->id,
                    'manual_input_by_type' => $isGuru ? \App\Models\Guru::class : \App\Models\User::class,
                    'manual_input_at'      => Carbon::now('Asia/Jakarta'),
                    'manual_input_user_id' => $loginUser?->id,
                    'note'            => $note,
                    'scan_time'       => $newScanTime,
                    'scan_out_time'   => $newScanOutTime,
                ]
            );
        }

        $this->showInputModal = false;
        $this->loadDashboardData();

        $this->dispatch('notify', [
            'type'    => 'success',
            'message' => 'Presensi tanggal ' . $this->inputDate . ' berhasil disimpan!',
        ]);
    }

    public function openCetakModal()
    {
        if (!$this->selectedClassId || !$this->selectedAcademicYearId) {
            $this->dispatch('notify', [
                'type'    => 'error',
                'message' => 'Silakan pilih kelas dan tahun ajaran terlebih dahulu.',
            ]);
            return;
        }

        $this->cetakBulanYear = $this->selectedMonthYear ?? date('m-Y');
        $this->cetakSemester = (int)date('m') >= 7 ? 'ganjil' : 'genap';
        $this->cetakJenis = 'bulanan';
        $this->cetakIncludeCheckout = $this->enableCheckout;

        $this->showCetakModal = true;
    }

    private function getCetakDateRange()
    {
        $ay = TahunAjaran::find($this->selectedAcademicYearId);
        if (!$ay) {
            return ['start' => null, 'end' => null, 'label' => ''];
        }

        if ($this->cetakJenis === 'bulanan') {
            $parts = explode('-', $this->cetakBulanYear);
            $month = (int)($parts[0] ?? date('m'));
            $year = (int)($parts[1] ?? date('Y'));
            
            $start = Carbon::create($year, $month, 1)->startOfMonth();
            $end   = Carbon::create($year, $month, 1)->endOfMonth();
            $monthNames = [
                1=>'Januari', 2=>'Februari', 3=>'Maret', 4=>'April',
                5=>'Mei', 6=>'Juni', 7=>'Juli', 8=>'Agustus',
                9=>'September', 10=>'Oktober', 11=>'November', 12=>'Desember',
            ];
            $label = 'Bulan ' . ($monthNames[$month] ?? $month) . ' ' . $year;
        } elseif ($this->cetakJenis === 'semester') {
            if ($this->cetakSemester === 'ganjil') {
                $start = Carbon::create($ay->start_year, 7, 1)->startOfMonth();
                $end   = Carbon::create($ay->start_year, 12, 31)->endOfMonth();
                $label = 'Semester Ganjil TA ' . $ay->name;
            } else {
                $start = Carbon::create($ay->end_year, 1, 1)->startOfMonth();
                $end   = Carbon::create($ay->end_year, 6, 30)->endOfMonth();
                $label = 'Semester Genap TA ' . $ay->name;
            }
        } else {
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

    public function downloadCetakExcel()
    {
        if (!$this->selectedClassId || !$this->selectedAcademicYearId) return;

        $range = $this->getCetakDateRange();
        $className = Kelas::find($this->selectedClassId)?->name ?? 'Kelas';
        $safeClassName = str_replace(['/', '\\'], '-', $className);
        $safeLabel = str_replace([' ', '/', '\\'], ['_', '-', '-'], $range['label']);
        $fileName = 'Laporan_Presensi_' . $safeClassName . '_' . $safeLabel . '.xlsx';

        $includeCheckout = $this->enableCheckout && $this->cetakIncludeCheckout;

        if ($this->cetakJenis === 'bulanan') {
            $parts = explode('-', $this->cetakBulanYear);
            $month = $parts[0] ?? date('m');
            $year = (int)($parts[1] ?? date('Y'));
            
            return Excel::download(
                new PresensiMatrixExport(
                    $this->selectedClassId,
                    $this->selectedAcademicYearId,
                    $month,
                    $year,
                    $range['label'],
                    $includeCheckout,
                    $this->cetakReportFormat
                ),
                $fileName
            );
        }

        return Excel::download(
            new LaporanPresensiRangeExport(
                $this->selectedClassId,
                $this->selectedAcademicYearId,
                $range['start'],
                $range['end'],
                $range['label'],
                $includeCheckout
            ),
            $fileName
        );
    }

    public function downloadCetakPdf()
    {
        if (!$this->selectedClassId || !$this->selectedAcademicYearId) return;

        $range   = $this->getCetakDateRange();
        $kelas   = Kelas::find($this->selectedClassId);
        $sekolah = PengaturanSekolah::current();
        $includeCheckout = $this->enableCheckout && $this->cetakIncludeCheckout;
        
        $className = $kelas?->name ?? 'Kelas';
        $safeClassName = str_replace(['/', '\\'], '-', $className);
        $safeLabel = str_replace([' ', '/', '\\'], ['_', '-', '-'], $range['label']);
        $fileName  = 'Laporan_Presensi_' . $safeClassName . '_' . $safeLabel . '.pdf';

        if ($this->cetakJenis === 'bulanan') {
            $parts = explode('-', $this->cetakBulanYear);
            $month = $parts[0] ?? date('m');
            $year = (int)($parts[1] ?? date('Y'));
            
            $service = app(PresensiRekapService::class);
            $result = $service->getMonthlyCalendarData(
                $this->selectedAcademicYearId,
                $this->selectedClassId,
                $month,
                $year
            );
            
            $pdf = Pdf::loadView('pdf.laporan-presensi-matrix', [
                'students'        => $result['students'],
                'monthlyStats'    => $result['monthlyStats'],
                'daysInMonth'     => $result['daysInMonth'],
                'periodeLabel'    => $range['label'],
                'kelas'           => $kelas,
                'sekolah'         => $sekolah,
                'includeCheckout' => $includeCheckout,
                'reportFormat'    => $this->cetakReportFormat,
                'generatedAt'     => now()->locale('id')->translatedFormat('l, d F Y H:i'),
            ])->setPaper('a4', 'landscape');

            return response()->streamDownload(
                fn() => print($pdf->output()),
                $fileName
            );
        }

        $service = app(PresensiRekapService::class);
        $result = $service->getStudentSemesterYearlyData(
            $this->selectedAcademicYearId,
            $this->selectedClassId,
            $range['start'],
            $range['end']
        );

        $pdf = Pdf::loadView('pdf.laporan-presensi-range', [
            'studentsData'    => $result['studentsData'],
            'monthsList'      => $result['monthsList'],
            'jenisLaporan'    => $this->cetakJenis,
            'periodeLabel'    => $range['label'],
            'kelas'           => $kelas,
            'sekolah'         => $sekolah,
            'includeCheckout' => $includeCheckout,
            'generatedAt'     => now()->locale('id')->translatedFormat('l, d F Y H:i'),
        ])->setPaper('a4', 'landscape');

        return response()->streamDownload(
            fn() => print($pdf->output()),
            $fileName
        );
    }

    public function sendWaDirect(string $studentId, string $targetPhone, string $messageText, string $type = 'belum'): void
    {
        $notifSetting = PresensiNotificationSetting::where('status_presensi', $type)->first();
        if ($notifSetting && !$notifSetting->is_active) {
            $this->dispatch('notify', [
                'type'    => 'warning',
                'message' => 'Fitur notifikasi WhatsApp untuk kategori ini dinonaktifkan oleh Administrator.',
            ]);
            return;
        }

        $kalenderService = app(\App\Services\KalenderSekolahService::class);
        if (!$kalenderService->isHariSekolah(now('Asia/Jakarta'), $this->selectedClassId)) {
            $this->dispatch('notify', [
                'type'    => 'warning',
                'message' => 'Hari ini adalah hari libur sekolah. Pengiriman notifikasi WA dinonaktifkan.',
            ]);
            return;
        }

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
                'message' => 'Gagal mengirim pesan WhatsApp via sistem gateway. Silakan periksa gateway WhatsApp.',
            ]);
        }
    }

    public function render()
    {
        $isNotifBelumActive = (bool) (PresensiNotificationSetting::where('status_presensi', 'belum')->value('is_active') ?? false);
        $isNotifTelatActive = (bool) (PresensiNotificationSetting::where('status_presensi', 'telat')->value('is_active') ?? false);

        return view('livewire.wali-kelas-dashboard', [
            'isNotifBelumActive' => $isNotifBelumActive,
            'isNotifTelatActive' => $isNotifTelatActive,
        ]);
    }
}
