<?php

namespace App\Livewire\PortalGuru;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\LeaveRequest;
use App\Models\EnrollmentSiswa;
use App\Services\LeaveRequestService;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use App\Models\PresensiNotificationSetting;
use Illuminate\Support\Carbon;

#[Layout('components.layouts.portal')]
class IjinKehadiranDetail extends Component
{
    public $recordId;
    public $request;
    public $reason = '';
    
    // UI states for modals/forms
    public $showRejectForm = false;
    public $showResetForm = false;

    public function mount($id)
    {
        $this->recordId = $id;
        $this->loadRequest();
    }

    protected function loadRequest()
    {
        $teacher = Auth::user()->teacher;
        
        // Verify access by checking if student is in accessible classes
        $kelasWali = $teacher->kelasAjarans()->pluck('class_id')->toArray();
        $kelasPantau = $teacher->kelasPantau()->pluck('class_id')->toArray();
        $accessibleClassIds = array_unique(array_merge($kelasWali, $kelasPantau));

        $studentIds = EnrollmentSiswa::whereIn('class_id', $accessibleClassIds)
            ->where('status', 'aktif')
            ->pluck('student_id')
            ->toArray();

        $this->request = LeaveRequest::whereIn('student_id', $studentIds)
            ->with(['student.enrollmentAktif.kelas', 'logs.user', 'approvedBy'])
            ->findOrFail($this->recordId);
    }

    public function approve(LeaveRequestService $service)
    {
        if ($this->request->status === 'approved') return;

        $user = auth()->user();
        $oldStatus = $this->request->status;

        DB::beginTransaction();
        try {
            $this->request->update([
                'status' => 'approved',
                'approved_by' => $user->id,
                'approved_by_type' => get_class($user),
                'approved_at' => now(),
            ]);

            $this->request->recordLog('approved', "Disetujui oleh " . $user->name);
            $generatedCount = $service->syncAttendances($this->request);

            DB::commit();
            
            $this->sendNotificationToStudent('DISETUJUI', null);
            
            $this->loadRequest();
            $this->dispatch('notify', [
                'type' => 'success',
                'message' => "Ijin disetujui. $generatedCount data presensi berhasil di-generate."
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
        }
    }

    public function reject(LeaveRequestService $service)
    {
        $this->validate([
            'reason' => 'required|string|min:3'
        ], [
            'reason.required' => 'Alasan penolakan wajib diisi.'
        ]);

        $user = auth()->user();
        $oldStatus = $this->request->status;

        DB::beginTransaction();
        try {
            if ($oldStatus === 'approved') {
                $service->removeAttendances($this->request);
            }

            $this->request->update([
                'status' => 'rejected',
                'approved_by' => $user->id,
                'approved_by_type' => get_class($user),
                'approved_at' => now(),
            ]);

            $this->request->recordLog('rejected', "Ditolak: " . $this->reason);

            DB::commit();
            
            $this->sendNotificationToStudent('DITOLAK', $this->reason);
            
            $this->reason = '';
            $this->showRejectForm = false;
            $this->loadRequest();
            
            $this->dispatch('notify', [
                'type' => 'success',
                'message' => "Ijin berhasil ditolak."
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
        }
    }

    public function resetToPending(LeaveRequestService $service)
    {
        $this->validate([
            'reason' => 'required|string|min:3'
        ], [
            'reason.required' => 'Alasan pengembalian status wajib diisi.'
        ]);

        $oldStatus = $this->request->status;

        DB::beginTransaction();
        try {
            if ($oldStatus === 'approved') {
                $service->removeAttendances($this->request);
            }

            $this->request->update([
                'status' => 'pending',
                'approved_by' => null,
                'approved_by_type' => null,
                'approved_at' => null,
            ]);

            $this->request->recordLog('updated', "Dikembalikan ke Pending: " . $this->reason);

            DB::commit();
            
            $this->reason = '';
            $this->showResetForm = false;
            $this->loadRequest();
            
            $this->dispatch('notify', [
                'type' => 'success',
                'message' => "Status berhasil dikembalikan ke Pending."
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
        }
    }

    protected function sendNotificationToStudent($statusLabel, $alasanTolak)
    {
        $setting = PresensiNotificationSetting::where('status_presensi', 'leave_approval')->first();
        if (!$setting || !$setting->is_active || empty($setting->recipients) || empty($setting->template_pesan)) {
            return;
        }

        $student = $this->request->student;
        if (!$student) return;
        
        $noHpSiswa = $student->no_hp;
        $user = auth()->user();

        $alasanText = $alasanTolak ? "Alasan penolakan: " . $alasanTolak : "";

        $replacements = [
            '{nama_siswa}' => $student->name,
            '{jenis_ijin}' => ucfirst($this->request->type),
            '{tanggal_mulai}' => Carbon::parse($this->request->start_date)->translatedFormat('d F Y'),
            '{tanggal_selesai}' => Carbon::parse($this->request->end_date)->translatedFormat('d F Y'),
            '{status_persetujuan}' => $statusLabel,
            '{nama_guru}' => $user->name,
            '{alasan_penolakan}' => $alasanText,
        ];

        $pesan = strtr($setting->template_pesan, $replacements);
        $waService = app(\App\Services\WhatsAppGatewayService::class);

        foreach ($setting->recipients as $recipientType) {
            if ($recipientType === 'siswa' && $noHpSiswa) {
                $waService->sendMessage(
                    $noHpSiswa,
                    $pesan,
                    'leave_approval',
                    $this->request->id,
                    'siswa'
                );
            }
            // Optional: other recipient types
        }
    }

    public function render()
    {
        return view('livewire.portal-guru.ijin-kehadiran-detail');
    }
}
