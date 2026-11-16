<?php

namespace App\Livewire\PortalPresensi\SholatDhuhur;

use App\Models\EnrollmentSiswa;
use App\Models\Kelas;
use App\Models\PengaturanSekolah;
use App\Models\Presensi;
use App\Models\PresensiSholatDhuhur;
use App\Models\TahunAjaran;
use App\Services\PresensiSholatDhuhurService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.portal')]
class InputManualSholatDhuhur extends Component
{
    public $academicYears = [];
    public $selectedAcademicYearId;
    public $classes = [];
    public $selectedClassId;
    public $inputDate;

    public bool $isInputDateHoliday = false;
    public string $inputDateHolidayDesc = '';
    public array $inputStudents = [];
    public string $bulkStatus = '';
    public bool $hasSubmittedFilter = false;

    public function mount(): void
    {
        abort_if(!(PengaturanSekolah::current()->enable_sholat_dhuhur ?? true), 403, 'Fitur Sholat Dhuhur sedang dinonaktifkan.');

        $this->inputDate = Carbon::today('Asia/Jakarta')->toDateString();
        $this->academicYears = TahunAjaran::orderBy('start_year', 'desc')->get();

        $activeYear = TahunAjaran::where('status', 'aktif')->first() ?? $this->academicYears->first();
        if ($activeYear) {
            $this->selectedAcademicYearId = $activeYear->id;
        }

        $this->loadClasses();

        $requestedClassId = request()->query('class_id');
        if ($requestedClassId && $this->classes->contains('id', $requestedClassId)) {
            $this->selectedClassId = $requestedClassId;
            $this->filterData();
        }
    }

    public function loadClasses(): void
    {
        if (!$this->selectedAcademicYearId) {
            $this->classes = collect();
            $this->selectedClassId = null;
            return;
        }

        $this->classes = Kelas::orderBy('name', 'asc')->get();
    }

    public function updatedSelectedAcademicYearId(): void
    {
        $this->hasSubmittedFilter = false;
        $this->inputStudents = [];
        $this->loadClasses();
    }

    public function updatedSelectedClassId(): void
    {
        $this->hasSubmittedFilter = false;
        $this->inputStudents = [];
    }

    public function updatedInputDate(): void
    {
        if ($this->hasSubmittedFilter && $this->selectedClassId && $this->inputDate) {
            $this->loadStudentsForInput();
        }
    }

    public function filterData(): void
    {
        if (!$this->selectedClassId || !$this->inputDate) {
            session()->flash('error', 'Harap pilih Kelas dan Tanggal terlebih dahulu sebelum memproses data.');
            $this->hasSubmittedFilter = false;
            return;
        }

        $this->hasSubmittedFilter = true;
        $this->loadStudentsForInput();
    }

    public function loadStudentsForInput(): void
    {
        $this->inputStudents = [];
        if (!$this->selectedClassId || !$this->selectedAcademicYearId || !$this->inputDate) {
            return;
        }

        $sholatService = app(PresensiSholatDhuhurService::class);
        $dateObj = Carbon::parse($this->inputDate);
        $this->isInputDateHoliday = !$sholatService->isHariSholatDhuhur($dateObj, $this->selectedClassId);
        $this->inputDateHolidayDesc = $sholatService->getHolidayDescription($dateObj, $this->selectedClassId) ?? '';

        $enrollments = EnrollmentSiswa::where('class_id', $this->selectedClassId)
            ->where('academic_year_id', $this->selectedAcademicYearId)
            ->where('status', 'aktif')
            ->with('siswa')
            ->get()
            ->sortBy(fn ($enr) => $enr->siswa?->name ?? '');

        $existingSholat = PresensiSholatDhuhur::where('academic_year_id', $this->selectedAcademicYearId)
            ->where('class_id', $this->selectedClassId)
            ->whereDate('date', $this->inputDate)
            ->get()
            ->keyBy('student_id');

        $list = [];
        foreach ($enrollments as $enr) {
            $student = $enr->siswa;
            if (!$student) {
                continue;
            }

            $religion = strtolower(trim($student->religion ?? ''));
            $isNonMuslim = !empty($student->religion) && $religion !== 'islam';

            if ($isNonMuslim) {
                $list[] = [
                    'id'             => $student->id,
                    'enrollment_id'  => $enr->id,
                    'name'           => $student->name,
                    'nisn'           => $student->nisn,
                    'gender'         => $student->gender,
                    'religion'       => $student->religion,
                    'status'         => 'non_muslim',
                    'keterangan'     => 'Non-Muslim (' . $student->religion . ') — Tidak wajib sholat',
                    'is_halangan'    => false,
                    'is_pagi_absent' => false,
                    'is_non_muslim'  => true,
                ];
                continue;
            }

            $sholat = $existingSholat->get($student->id);
            $status = '';
            $ket = '';
            $isHalangan = false;

            if ($sholat) {
                $status = $sholat->status;
                $ket = (string) ($sholat->keterangan ?? '');
                $isHalangan = str_contains($ket, 'Halangan') || str_contains($ket, 'Haid');
                if ($isHalangan) {
                    $status = 'haid';
                }
            }

            $list[] = [
                'id'             => $student->id,
                'enrollment_id'  => $enr->id,
                'name'           => $student->name,
                'nisn'           => $student->nisn,
                'gender'         => $student->gender,
                'religion'       => $student->religion ?? 'Islam',
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

    public function applyBulkStatus(string $status): void
    {
        if (empty($status) || $this->isInputDateHoliday) {
            return;
        }

        foreach ($this->inputStudents as $index => $st) {
            if (!empty($st['is_non_muslim'])) {
                continue;
            }

            if ($status === 'haid') {
                if (($st['gender'] ?? '') === 'P') {
                    $this->inputStudents[$index]['status'] = 'haid';
                    $this->inputStudents[$index]['is_halangan'] = true;
                    $this->inputStudents[$index]['keterangan'] = 'Halangan / Haid';
                }
            } else {
                $this->inputStudents[$index]['status'] = $status;
                $this->inputStudents[$index]['is_halangan'] = false;
                if (in_array($this->inputStudents[$index]['keterangan'] ?? '', ['Halangan / Haid'], true)) {
                    $this->inputStudents[$index]['keterangan'] = '';
                }
            }
        }
    }

    public function updatedInputStudents($value, $key): void
    {
        $parts = explode('.', (string) $key);
        if (count($parts) === 2 && $parts[1] === 'status') {
            $index = (int) $parts[0];
            if (!isset($this->inputStudents[$index])) {
                return;
            }
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

    public function tarikDariPresensiPagi(): void
    {
        if (!$this->selectedClassId || !$this->selectedAcademicYearId || !$this->inputDate) {
            return;
        }

        $sholatService = app(PresensiSholatDhuhurService::class);
        $dateObj = Carbon::parse($this->inputDate);
        if ($this->isInputDateHoliday || !$sholatService->isHariSholatDhuhur($dateObj, $this->selectedClassId)) {
            $this->isInputDateHoliday = true;
            session()->flash('error', 'Tidak dapat menyinkronkan presensi sholat dhuhur pada hari libur.');
            return;
        }

        $morningAtts = Presensi::where('academic_year_id', $this->selectedAcademicYearId)
            ->where('class_id', $this->selectedClassId)
            ->whereDate('date', $this->inputDate)
            ->whereIn('status', ['alpa', 'izin', 'sakit'])
            ->get()
            ->keyBy('student_id');

        if ($morningAtts->isEmpty()) {
            session()->flash('warning', 'Tidak ditemukan data Alpa, Izin, atau Sakit di presensi pagi untuk kelas dan tanggal ini.');
            return;
        }

        $syncCount = 0;
        foreach ($this->inputStudents as $idx => $st) {
            if (!empty($st['is_non_muslim'])) {
                continue;
            }

            $morning = $morningAtts->get($st['id']);
            if (!$morning) {
                continue;
            }

            if ($morning->status === 'alpa') {
                $this->inputStudents[$idx]['status'] = 'tidak_hadir';
                $this->inputStudents[$idx]['keterangan'] = 'Alpa di presensi pagi';
                $this->inputStudents[$idx]['is_halangan'] = false;
                $this->inputStudents[$idx]['is_pagi_absent'] = true;
                $syncCount++;
            } elseif (in_array($morning->status, ['izin', 'sakit'], true)) {
                $this->inputStudents[$idx]['status'] = 'ijin';
                $this->inputStudents[$idx]['keterangan'] = ucfirst($morning->status) . ' di presensi pagi';
                $this->inputStudents[$idx]['is_halangan'] = false;
                $this->inputStudents[$idx]['is_pagi_absent'] = true;
                $syncCount++;
            }
        }

        session()->flash('success', "Berhasil menyelaraskan {$syncCount} data ketidakhadiran dari presensi pagi.");
    }

    public function save(): void
    {
        if (!$this->selectedClassId || !$this->selectedAcademicYearId || !$this->inputDate) {
            return;
        }

        $sholatService = app(PresensiSholatDhuhurService::class);
        $dateObj = Carbon::parse($this->inputDate);
        if ($this->isInputDateHoliday || !$sholatService->isHariSholatDhuhur($dateObj, $this->selectedClassId)) {
            $desc = $sholatService->getHolidayDescription($dateObj, $this->selectedClassId) ?? ($this->inputDateHolidayDesc ?: 'Libur');
            $this->isInputDateHoliday = true;
            $this->inputDateHolidayDesc = $desc;
            session()->flash('error', "Tidak dapat menyimpan presensi sholat dhuhur pada hari libur ({$desc}).");
            return;
        }

        $teacherId = Auth::user()?->teacher?->id;
        $savedCount = 0;

        foreach ($this->inputStudents as $stData) {
            if (!empty($stData['is_non_muslim']) || empty($stData['status']) || empty($stData['id'])) {
                continue;
            }

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
                    'enrollment_id'       => $stData['enrollment_id'] ?? null,
                    'status'              => $dbStatus,
                    'keterangan'          => trim((string) ($stData['keterangan'] ?? '')) ?: ($isHalangan ? 'Halangan / Haid' : null),
                    'input_by_teacher_id' => $teacherId,
                ]
            );
            $savedCount++;
        }

        if ($savedCount === 0) {
            session()->flash('warning', 'Pilih status kehadiran sholat siswa terlebih dahulu atau gunakan Set Massal.');
            return;
        }

        $this->loadStudentsForInput();
        $formattedDate = Carbon::parse($this->inputDate)->locale('id')->translatedFormat('l, d F Y');
        session()->flash('success', "{$savedCount} data presensi Sholat Dhuhur berhasil disimpan untuk {$formattedDate}.");
    }

    public function render()
    {
        return view('livewire.portal-presensi.sholat-dhuhur.input-manual-sholat-dhuhur')
            ->title('Input Manual Sholat Dhuhur - Portal Presensi');
    }
}

