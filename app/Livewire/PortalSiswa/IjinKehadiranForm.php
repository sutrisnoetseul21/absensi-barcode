<?php

namespace App\Livewire\PortalSiswa;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;
use App\Models\LeaveRequest;
use App\Models\TahunAjaran;
use Livewire\Attributes\Layout;
use Illuminate\Support\Carbon;
use App\Models\PresensiNotificationSetting;
use App\Models\KelasAjaran;

#[Layout('components.layouts.portal')]
class IjinKehadiranForm extends Component
{
    use WithFileUploads;

    public $student;
    public $recordId = null;

    public $type = 'ijin';
    public $start_date;
    public $end_date;
    public $duration_days = 1;
    public $reason;
    public $attachments = []; // For new upload(s)
    public $existing_file_paths = []; // To show existing(s)
    public $holidayMessages = [];


    public function mount($id = null)
    {
        $this->student = Auth::user()->student;

        if ($id) {
            $record = LeaveRequest::where('student_id', $this->student->id)
                ->where('id', $id)
                ->where('status', 'pending')
                ->firstOrFail();

            $this->recordId = $record->id;
            $this->type = $record->type;
            $this->start_date = $record->start_date->format('Y-m-d');
            $this->end_date = $record->end_date->format('Y-m-d');
            $this->duration_days = $record->duration_days;
            $this->reason = $record->reason;
            $this->existing_file_paths = $record->attachments ?? [];
        } else {
            $this->start_date = now()->format('Y-m-d');
            $this->end_date = now()->format('Y-m-d');
        }
        $this->checkHolidays();
    }

    public function updatedDurationDays($value)
    {
        if ($this->start_date && $value > 0) {
            $this->end_date = Carbon::parse($this->start_date)->addDays((int)$value - 1)->format('Y-m-d');
            $this->checkHolidays();
        }
    }

    public function setDuration($days)
    {
        $this->duration_days = (int) $days;
        $this->updatedDurationDays($this->duration_days);
    }

    public function removeAttachment($index)
    {
        if (isset($this->attachments[$index])) {
            unset($this->attachments[$index]);
            $this->attachments = array_values($this->attachments);
        }
    }

    public function updatedStartDate($value)
    {
        if ($value && $this->duration_days > 0) {
            $this->end_date = Carbon::parse($value)->addDays((int)$this->duration_days - 1)->format('Y-m-d');
            $this->checkHolidays();
        }
    }

    protected function checkHolidays()
    {
        $this->holidayMessages = [];
        if (!$this->start_date || !$this->end_date || !$this->student) return;

        $enrollment = $this->student->enrollmentAktif;
        if (!$enrollment) return;

        $kalenderService = app(\App\Services\KalenderSekolahService::class);
        $current = Carbon::parse($this->start_date);
        $end = Carbon::parse($this->end_date);

        while ($current->lessThanOrEqualTo($end)) {
            if (!$kalenderService->isHariSekolah($current, $enrollment->class_id)) {
                $this->holidayMessages[] = "Catatan: " . $current->translatedFormat('l, d F Y') . " dalam rentang ini adalah hari libur, sehingga otomatis akan tercatat sebagai 'Libur', bukan 'Sakit/Izin'.";
            }
            $current->addDay();
        }
    }

    public function save()
    {
        $this->validate([
            'type' => 'required|in:ijin,sakit',
            'duration_days' => 'required|integer|min:1',
            'start_date' => 'required|date',
            'reason' => 'required|string',
            'attachments' => 'nullable|array|max:5',
            'attachments.*' => 'file|mimes:pdf,jpg,jpeg,png,webp|max:2048',
        ]);

        // Validasi Tahun Ajaran Aktif
        $activeYear = TahunAjaran::where('status', 'aktif')->first();
        if (!$activeYear) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Tahun ajaran aktif tidak ditemukan.'
            ]);
            return;
        }

        // Validasi Rentang Waktu Pengajuan (Max 1 minggu ke belakang & ke depan)
        $minDate = now()->subDays(7)->startOfDay();
        $maxDate = now()->addDays(7)->endOfDay();
        $startDateObj = Carbon::parse($this->start_date)->startOfDay();

        if ($startDateObj->lessThan($minDate) || $startDateObj->greaterThan($maxDate)) {
            $this->addError('start_date', 'Gagal: Tanggal pengajuan hanya diizinkan maksimal 7 hari sebelum atau sesudah tanggal hari ini.');
            return;
        }

        // Validasi Overlap (Exclude self if editing)
        $overlapRecord = LeaveRequest::where('student_id', $this->student->id)
            ->when($this->recordId, function ($query) {
                $query->where('id', '!=', $this->recordId);
            })
            ->whereIn('status', ['pending', 'approved'])
            ->where(function ($query) {
                $query->whereBetween('start_date', [$this->start_date, $this->end_date])
                      ->orWhereBetween('end_date', [$this->start_date, $this->end_date])
                      ->orWhere(function ($q) {
                          $q->where('start_date', '<=', $this->start_date)
                            ->where('end_date', '>=', $this->end_date);
                      });
            })
            ->first();

        if ($overlapRecord) {
            $statusLabel = $overlapRecord->status === 'approved' ? 'Disetujui' : 'Pending';
            $this->addError('start_date', "Gagal: Anda sudah memiliki pengajuan yang berstatus [{$statusLabel}] pada rentang tanggal ini.");
            return;
        }

        // Validasi Hari Lampau yang Sudah Hadir/Telat
        $existingAttendance = \App\Models\Presensi::where('student_id', $this->student->id)
            ->whereBetween('date', [$this->start_date, $this->end_date])
            ->whereIn('status', ['hadir', 'telat'])
            ->first();

        if ($existingAttendance) {
            $formattedDate = Carbon::parse($existingAttendance->date)->translatedFormat('d F Y');
            $this->addError('start_date', "Gagal: Anda sudah memiliki catatan presensi (Hadir/Telat) pada tanggal {$formattedDate}. Silakan periksa kembali tanggal pengajuan Anda.");
            return;
        }

        $paths = $this->existing_file_paths ?? [];
        if ($this->attachments && is_array($this->attachments)) {
            foreach ($this->attachments as $file) {
                $paths[] = $file->store('leave-requests', 'public');
            }
        }
        $paths = array_unique($paths);

        $data = [
            'student_id' => $this->student->id,
            'academic_year_id' => $activeYear->id,
            'type' => $this->type,
            'duration_days' => $this->duration_days,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'reason' => $this->reason,
            'file_path' => count($paths) > 0 ? $paths[0] : null,
            'file_paths' => count($paths) > 1 ? array_slice($paths, 1) : null,
        ];

        if ($this->recordId) {
            $record = LeaveRequest::findOrFail($this->recordId);
            $record->update($data);
            $record->recordLog('updated', 'Diedit oleh siswa');
            $message = 'Pengajuan berhasil diperbarui.';
        } else {
            $data['status'] = 'pending';
            $record = LeaveRequest::create($data);
            $record->recordLog('created', 'Dibuat oleh siswa');
            $message = 'Pengajuan berhasil dibuat.';
            
            $this->sendNotification($record);
        }

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => $message
        ]);

        return redirect()->route('portal-siswa.ijin');
    }

    protected function sendNotification($record)
    {
        $setting = PresensiNotificationSetting::where('status_presensi', 'leave_request')->first();
        if (!$setting || !$setting->is_active || empty($setting->recipients) || empty($setting->template_pesan)) {
            return;
        }

        $enrollment = $this->student->enrollmentAktif;
        if (!$enrollment) return;
        
        $kelas = $enrollment->kelas->name ?? '-';

        $kelasAjaran = KelasAjaran::where('class_id', $enrollment->class_id)
            ->where('academic_year_id', $enrollment->academic_year_id)
            ->with('guru')
            ->first();

        $namaWaliKelas = $kelasAjaran?->guru?->name ?? 'Wali Kelas';
        $noHpWaliKelas = $kelasAjaran?->guru?->no_hp;
        
        $replacements = [
            '{nama_siswa}' => $this->student->name,
            '{kelas}' => $kelas,
            '{jenis_ijin}' => ucfirst($this->type),
            '{tanggal_mulai}' => Carbon::parse($this->start_date)->translatedFormat('d F Y'),
            '{tanggal_selesai}' => Carbon::parse($this->end_date)->translatedFormat('d F Y'),
            '{alasan}' => $this->reason,
            '{nama_wali_kelas}' => $namaWaliKelas,
            '{link_detail}' => url('/portal-guru/ijin-kehadiran/' . $record->id),
        ];

        $pesan = strtr($setting->template_pesan, $replacements);
        $waService = app(\App\Services\WhatsAppGatewayService::class);

        foreach ($setting->recipients as $recipientType) {
            if ($recipientType === 'wali_kelas' && $noHpWaliKelas) {
                \App\Jobs\SendWhatsAppNotificationJob::dispatch(
                    $noHpWaliKelas,
                    $pesan,
                    'leave_request',
                    $record->id,
                    'guru'
                );
            }
        }
    }

    public function render()
    {
        return view('livewire.portal-siswa.ijin-kehadiran-form');
    }
}
