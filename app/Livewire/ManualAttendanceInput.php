<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use App\Models\Presensi;
use App\Models\TahunAjaran;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

class ManualAttendanceInput extends Component
{
    public $isOpen = false;

    public $studentId;
    public $studentName;
    public $classId;

    #[Validate('required|date')]
    public $date;

    #[Validate('required|in:hadir,telat,izin,sakit,alpa')]
    public $status = 'hadir';

    #[Validate('nullable|string')]
    public $note;

    #[Validate('nullable|integer|min:0')]
    public $lateMinutes;

    #[On('openManualInput')]
    public function open($studentId, $studentName, $classId)
    {
        $this->studentId = $studentId;
        $this->studentName = $studentName;
        $this->classId = $classId;
        $this->date = date('Y-m-d');
        $this->status = 'hadir';
        $this->note = '';
        $this->lateMinutes = null;
        
        $this->resetErrorBag();
        $this->isOpen = true;
    }

    public function close()
    {
        $this->isOpen = false;
    }

    public function save()
    {
        $this->validate();

        $activeYear = TahunAjaran::where('status', 'aktif')->first();
        if (!$activeYear) {
            throw ValidationException::withMessages(['date' => 'Tidak ada tahun ajaran aktif.']);
        }

        // Cek apakah sudah ada data dari kios
        $existing = Presensi::where('student_id', $this->studentId)
            ->where('date', $this->date)
            ->first();

        if ($existing && $existing->is_manual_input === false) {
            throw ValidationException::withMessages([
                'status' => 'Siswa ini sudah tercatat Hadir/Telat hari ini via scan kios, tidak bisa diubah manual.'
            ]);
        }

        $actor = Auth::guard('wali_kelas')->check() 
            ? Auth::guard('wali_kelas')->user() 
            : Auth::guard('web')->user();

        $oldStatus = $existing ? $existing->status : null;

        $attendance = Presensi::updateOrCreate(
            [
                'student_id' => $this->studentId,
                'date' => $this->date,
            ],
            [
                'class_id' => $this->classId,
                'academic_year_id' => $activeYear->id,
                'status' => $this->status,
                'late_minutes' => $this->status === 'telat' ? ($this->lateMinutes ?? 0) : null,
                'is_manual_input' => true,
                'manual_input_by_id' => $actor->id,
                'manual_input_by_type' => get_class($actor),
            ]
        );

        if ($this->note) {
            // Bisa disimpan sebagai notes jika ada relasi / diubah model
            // Disini kita biarkan activity log yang mencatat notes atau bisa buat field notes di presensi 
            // Cek apakah tabel attendances punya field 'note'
            // Sementara kita record di activity log properties
        }

        activity()
            ->performedOn($attendance)
            ->causedBy($actor)
            ->withProperties([
                'old_status' => $oldStatus, 
                'new_status' => $this->status, 
                'note' => $this->note
            ])
            ->log('Updated manual attendance');

        $this->close();
        
        // Refresh the dashboard component
        $this->dispatch('$refresh');
    }

    public function render()
    {
        return view('livewire.manual-attendance-input');
    }
}
