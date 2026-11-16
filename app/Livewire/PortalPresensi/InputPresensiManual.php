<?php

namespace App\Livewire\PortalPresensi;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Kelas;
use App\Models\TahunAjaran;
use App\Models\Presensi;
use App\Models\EnrollmentSiswa;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class InputPresensiManual extends Component
{
    // Filters
    public $academicYears   = [];
    public $selectedAcademicYearId;
    public $classes         = [];
    public $selectedClassId;
    public $inputDate;
    public $isInputDateHoliday = false;

    // Data
    public $students        = [];
    public $inputStudents   = [];
    public $bulkStatusDatang = '';
    public $bulkStatusPulang = '';
    public bool $hasSubmittedFilter = false;
    public bool $enableCheckout = true;
    public string $checkinHm = '07:00';
    public int $lateThresholdMinutes = 15;
    public string $lateCutoffHm = '07:15';

    public function mount(): void
    {
        $settings = \App\Models\PengaturanSekolah::current();
        $this->enableCheckout = $settings?->enable_checkout ?? true;
        $eval = Presensi::evaluateArrivalTime(null, null, $settings);
        $this->checkinHm = $eval['checkin_hm'];
        $this->lateThresholdMinutes = $eval['threshold'];
        $this->lateCutoffHm = $eval['cutoff_hm'];
        $this->inputDate      = null;
        $this->selectedClassId = null;
        $this->academicYears  = TahunAjaran::orderBy('start_year', 'desc')->get();

        $activeYear = TahunAjaran::where('status', 'aktif')->first() ?? $this->academicYears->first();
        if ($activeYear) {
            $this->selectedAcademicYearId = $activeYear->id;
        }

        $this->loadClasses();
    }

    public function loadClasses(): void
    {
        if (!$this->selectedAcademicYearId) {
            $this->classes        = collect();
            $this->selectedClassId = null;
            return;
        }

        $this->classes = Kelas::orderBy('name', 'asc')->get();
    }

    public function filterData(): void
    {
        if (!$this->selectedClassId || !$this->inputDate) {
            session()->flash('warning', 'Silakan pilih Kelas dan Tanggal terlebih dahulu sebelum memproses.');
            return;
        }

        $this->hasSubmittedFilter = true;
        $this->loadStudentsForInput();
    }

    public function updatedSelectedAcademicYearId(): void
    {
        $this->hasSubmittedFilter = false;
        $this->loadClasses();
    }

    public function updatedSelectedClassId(): void
    {
        $this->hasSubmittedFilter = false;
    }

    public function updatedInputDate(): void
    {
        $this->hasSubmittedFilter = false;
    }

    private function syncArrivalEvaluation(int $index): void
    {
        $status = $this->inputStudents[$index]['status'] ?? '';
        $scanTime = $this->inputStudents[$index]['scan_time'] ?? '';
        $eval = Presensi::evaluateArrivalTime($status, $scanTime, null, $this->inputDate);

        $this->inputStudents[$index]['late_minutes'] = $eval['late_minutes'];
        $this->inputStudents[$index]['arrival_alarm'] = $eval['alarm'];
        $this->inputStudents[$index]['alarm_type'] = $eval['alarm_type'];
    }

    public function updated($property, $value): void
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

    public function loadStudentsForInput(): void
    {
        if (!$this->selectedClassId || !$this->selectedAcademicYearId || !$this->inputDate) {
            $this->students = collect();
            $this->inputStudents = [];
            $this->isInputDateHoliday = false;
            return;
        }

        $kalenderService = app(\App\Services\KalenderSekolahService::class);
        $this->isInputDateHoliday = !$kalenderService->isHariSekolah(Carbon::parse($this->inputDate), $this->selectedClassId);

        // Ambil siswa di kelas
        $this->students = \App\Models\Siswa::whereHas('enrollments', function($q) {
            $q->where('class_id', $this->selectedClassId)
              ->where('academic_year_id', $this->selectedAcademicYearId)
              ->where('status', 'aktif');
        })->orderBy('name', 'asc')->get();

        $attendances = Presensi::with(['inputManualOleh', 'manualInputUser'])
            ->where('academic_year_id', $this->selectedAcademicYearId)
            ->where('class_id', $this->selectedClassId)
            ->where('date', $this->inputDate)
            ->get()->keyBy('student_id');

        $settings = \App\Models\PengaturanSekolah::current();
        $ruleInfo = Presensi::evaluateArrivalTime(null, null, $settings);
        $this->checkinHm = $ruleInfo['checkin_hm'];
        $this->lateThresholdMinutes = $ruleInfo['threshold'];
        $this->lateCutoffHm = $ruleInfo['cutoff_hm'];

        $list = [];
        foreach ($this->students as $student) {
            $att = $attendances->get($student->id);
            $status = $att ? $att->status : '';
            $scanTime = ($att && $att->scan_time) ? substr((string) $att->scan_time, 0, 5) : '';
            $eval = Presensi::evaluateArrivalTime($status, $scanTime, $settings, $this->inputDate);

            $list[] = [
                'id'              => $student->id,
                'name'            => $student->name,
                'status'          => $status,
                'status_pulang'   => $att ? $att->status_pulang : '',
                'scan_time'       => $scanTime,
                'scan_out_time'   => ($att && $att->scan_out_time) ? substr((string) $att->scan_out_time, 0, 5) : '',
                'late_minutes'    => $status === 'telat' ? ($eval['late_minutes'] ?: ($att?->late_minutes ?? 0)) : 0,
                'arrival_alarm'   => $eval['alarm'],
                'alarm_type'      => $eval['alarm_type'],
                'is_manual_input' => $att ? $att->is_manual_input : null,
                'manual_audit'    => $att ? $att->getManualAuditInfo() : null,
                'note'            => $att ? $att->note : null,
            ];
        }
        $this->bulkStatusDatang = '';
        $this->bulkStatusPulang = '';
        $this->inputStudents = $list;
    }

    public function updatedBulkStatusDatang($value): void
    {
        $this->applyBulkStatusDatang($value);
    }

    public function applyBulkStatusDatang($status): void
    {
        if (empty($status)) return;

        $settings = \App\Models\PengaturanSekolah::current();
        $defaultTime = in_array($status, ['hadir', 'telat'], true)
            ? Presensi::defaultManualScanTime($status, $settings)
            : '';

        foreach ($this->inputStudents as $index => $student) {
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

    public function updatedBulkStatusPulang($value): void
    {
        $this->applyBulkStatusPulang($value);
    }

    public function applyBulkStatusPulang($status): void
    {
        if (empty($status)) return;

        $nowWib = Carbon::now('Asia/Jakarta')->format('H:i');
        foreach ($this->inputStudents as $index => $student) {
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

    public function saveManualInput(): void
    {
        if (!$this->selectedClassId || !$this->selectedAcademicYearId || !$this->inputDate) return;
        
        if ($this->isInputDateHoliday) {
            session()->flash('error', 'Tidak dapat menyimpan presensi pada hari libur!');
            return;
        }

        $settings = \App\Models\PengaturanSekolah::current();
        $alarmMessages = [];
        foreach ($this->inputStudents as $index => $data) {
            if (!in_array($data['status'] ?? '', ['hadir', 'telat'], true)) {
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
            session()->flash('error', 'Penyimpanan dibatalkan karena jam kehadiran tidak sesuai pengaturan sekolah — ' . implode(' | ', $alarmMessages));
            return;
        }

        $savedCount = 0;
        foreach ($this->inputStudents as $index => $data) {
            if (empty($data['status']) || empty($data['id'])) continue;
            
            $studentId = $data['id'];

            $enrollment = EnrollmentSiswa::where('student_id', $studentId)
                ->where('academic_year_id', $this->selectedAcademicYearId)
                ->where('status', 'aktif')
                ->first();

            $existing = Presensi::where('student_id', $studentId)
                ->where('date', $this->inputDate)
                ->first();

            $newLate = ($data['status'] === 'telat') ? ($data['late_minutes'] ?: 0) : 0;
            $newStatusPulang = empty($data['status_pulang']) ? null : $data['status_pulang'];

            // Jika status datang adalah izin, sakit, atau alpa, status pulang mengikuti & jam scan harus kosong
            if (in_array($data['status'], ['izin', 'sakit', 'alpa'])) {
                $newStatusPulang = $data['status'];
                $newScanTime = null;
                $newScanOutTime = null;
            } else {
                $nowTime = Carbon::now('Asia/Jakarta')->format('H:i:00');
                $newScanTime = Presensi::normalizeTimeForStorage($data['scan_time'] ?? null)
                    ?? ($existing && $existing->scan_time ? $existing->scan_time : $nowTime);
                $newScanOutTime = ($newStatusPulang === 'pulang')
                    ? (Presensi::normalizeTimeForStorage($data['scan_out_time'] ?? null)
                        ?? ($existing && $existing->scan_out_time ? $existing->scan_out_time : $nowTime))
                    : null;
            }

            $oldScanHm = ($existing && $existing->scan_time) ? substr((string) $existing->scan_time, 0, 5) : null;
            $newScanHm = $newScanTime ? substr($newScanTime, 0, 5) : null;
            $oldOutHm = ($existing && $existing->scan_out_time) ? substr((string) $existing->scan_out_time, 0, 5) : null;
            $newOutHm = $newScanOutTime ? substr($newScanOutTime, 0, 5) : null;

            // Jika tidak ada perubahan data, lewati
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

            $actor = Auth::guard('web')->user();
            $note = Presensi::buildManualAuditNote(
                $existing,
                'Admin',
                $actor ? $actor->name : 'Sistem',
                $actor?->email,
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
                    'enrollment_id'        => $enrollment?->id,
                    'status'               => $data['status'],
                    'status_pulang'        => $newStatusPulang,
                    'late_minutes'         => $newLate,
                    'is_manual_input'      => true,
                    'manual_input_by_id'   => Auth::id(),
                    'manual_input_by_type' => \App\Models\User::class,
                    'manual_input_at'      => Carbon::now('Asia/Jakarta'),
                    'manual_input_user_id' => Auth::id(),
                    'note'                 => $note,
                    'scan_time'            => $newScanTime,
                    'scan_out_time'        => $newScanOutTime,
                ]
            );
            $savedCount++;
        }

        $this->loadStudentsForInput();
        $formattedDate = Carbon::parse($this->inputDate)->locale('id')->translatedFormat('l, d F Y');
        session()->flash('success', "{$savedCount} siswa berhasil diperbarui untuk tanggal {$formattedDate}.");
    }

    #[Layout('components.layouts.portal')]
    public function render()
    {
        return view('livewire.portal-presensi.input-presensi-manual')->title('Input Presensi Manual - Portal Presensi');
    }
}
