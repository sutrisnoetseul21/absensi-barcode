<?php

namespace App\Filament\Presensi\Pages;

use Filament\Pages\Page;
use App\Filament\Traits\HasSimplePageRoleAccess;
use Filament\Notifications\Notification;
use App\Models\Kelas;
use App\Models\TahunAjaran;
use App\Models\Presensi;
use App\Models\EnrollmentSiswa;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class InputPresensiManual extends Page
{
    use HasSimplePageRoleAccess;

    protected static function getModuleRolePrefix(): string
    {
        return 'presensi';
    }

    protected static function requiresEditorRole(): bool
    {
        return true;
    }

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-pencil-square';
    protected string $view = 'filament.pages.input-presensi-manual';
    protected static string|\UnitEnum|null $navigationGroup = 'Presensi';
    protected static ?string $title = 'Input Presensi Manual';
    protected static ?string $navigationLabel = 'Input Presensi Manual';
    protected static ?int $navigationSort = 5;

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

    public function mount(): void
    {
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
            Notification::make()
                ->title('Pilih Filter Presensi')
                ->body('Silakan pilih Kelas dan Tanggal terlebih dahulu sebelum memproses.')
                ->warning()
                ->send();
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

    public function updated($property, $value): void
    {
        if (str_starts_with($property, 'inputStudents.')) {
            $parts = explode('.', $property);
            if (count($parts) === 3 && $parts[2] === 'status') {
                $index = (int)$parts[1];
                if (in_array($value, ['izin', 'sakit', 'alpa'])) {
                    $this->inputStudents[$index]['status_pulang'] = $value;
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

        $attendances = Presensi::where('academic_year_id', $this->selectedAcademicYearId)
            ->where('class_id', $this->selectedClassId)
            ->where('date', $this->inputDate)
            ->get()->keyBy('student_id');

        $list = [];
        foreach ($this->students as $student) {
            $att = $attendances->get($student->id);
            $list[] = [
                'id'           => $student->id,
                'name'         => $student->name,
                'status'       => $att ? $att->status : '',
                'status_pulang'=> $att ? $att->status_pulang : '',
                'late_minutes' => $att ? $att->late_minutes : null,
                'is_manual_input' => $att ? $att->is_manual_input : null,
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

        foreach ($this->inputStudents as $index => $student) {
            // Untuk Admin Presensi, jika ada data yang terekam otomatis, tetap izinkan override/edit jika perlu, tetapi aksi massal mengupdate siswa
            $this->inputStudents[$index]['status'] = $status;
            if (in_array($status, ['izin', 'sakit', 'alpa'])) {
                $this->inputStudents[$index]['status_pulang'] = $status;
            }
        }
    }

    public function updatedBulkStatusPulang($value): void
    {
        $this->applyBulkStatusPulang($value);
    }

    public function applyBulkStatusPulang($status): void
    {
        if (empty($status)) return;

        foreach ($this->inputStudents as $index => $student) {
            $currentStatus = $student['status'] ?? '';
            if (in_array($currentStatus, ['izin', 'sakit', 'alpa'])) {
                continue;
            }

            $this->inputStudents[$index]['status_pulang'] = $status;
        }
    }

    public function saveManualInput(): void
    {
        if (!$this->selectedClassId || !$this->selectedAcademicYearId || !$this->inputDate) return;
        
        if ($this->isInputDateHoliday) {
            Notification::make()
                ->title('Gagal Menyimpan')
                ->body('Tidak dapat menyimpan presensi pada hari libur!')
                ->danger()
                ->send();
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

            // Jika status datang adalah izin, sakit, atau alpa, maka status pulang mengikuti
            if (in_array($data['status'], ['izin', 'sakit', 'alpa'])) {
                $newStatusPulang = $data['status'];
            }

            // Jika tidak ada perubahan data, lewati
            if ($existing && $existing->status === $data['status'] && $existing->late_minutes == $newLate && $existing->status_pulang === $newStatusPulang) {
                continue;
            }

            $actor = Auth::guard('web')->user();
            
            $note = null;
            if ($existing) {
                $strLama = $existing->status === 'telat' ? "Telat ({$existing->late_minutes} mnt)" : ucfirst($existing->status);
                $strBaru = $data['status'] === 'telat' ? "Telat ({$newLate} mnt)" : ucfirst($data['status']);
                $strPulangLama = $existing->status_pulang ? ucfirst($existing->status_pulang) : 'Kosong';
                $strPulangBaru = $newStatusPulang ? ucfirst($newStatusPulang) : 'Kosong';
                $appendNote = "Diedit oleh Admin: " . ($actor ? $actor->name : 'Sistem') . " (Datang: {$strLama}->{$strBaru}, Pulang: {$strPulangLama}->{$strPulangBaru})";
                $note = $existing->note ? $existing->note . ' | ' . $appendNote : $appendNote;
            } else {
                $note = "Diinput Manual oleh Admin: " . ($actor ? $actor->name : 'Sistem');
            }

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
                    'note'                 => $note,
                    'scan_time'            => $existing && $existing->scan_time ? $existing->scan_time : now()->toTimeString(),
                    'scan_out_time'        => ($newStatusPulang && (!$existing || !$existing->scan_out_time)) ? now()->toTimeString() : ($existing ? $existing->scan_out_time : null),
                ]
            );
            $savedCount++;
        }

        $this->loadStudentsForInput();

        Notification::make()
            ->title('Presensi berhasil disimpan')
            ->body("{$savedCount} siswa diperbarui untuk tanggal {$this->inputDate}.")
            ->success()
            ->send();
    }
}
