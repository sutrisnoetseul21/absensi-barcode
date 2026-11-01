<?php

namespace App\Observers;

use App\Models\LeaveRequest;
use App\Models\KelasAjaran;
use App\Notifications\LeaveRequestSubmittedNotification;
use App\Notifications\LeaveRequestResultNotification;
use Illuminate\Support\Facades\Log;

class LeaveRequestObserver
{
    /**
     * Handle the LeaveRequest "created" event.
     */
    public function created(LeaveRequest $record): void
    {
        // 1. Jika dibuat oleh siswa dengan status 'pending':
        //    Kirim notifikasi pengajuan izin baru ke Wali Kelas.
        if ($record->status === 'pending') {
            $this->notifyWaliKelas($record);
            return;
        }

        // 2. Jika dibuat langsung dengan status 'approved' (misal input manual oleh guru):
        //    Kirim notifikasi hasil persetujuan ke Siswa.
        if ($record->status === 'approved') {
            $this->notifyStudent($record);
        }
    }

    /**
     * Handle the LeaveRequest "updated" event.
     */
    public function updated(LeaveRequest $record): void
    {
        // Jika status berubah menjadi approved atau rejected, kirim notifikasi hasil ke Siswa
        if ($record->wasChanged('status') && in_array($record->status, ['approved', 'rejected'], true)) {
            $this->notifyStudent($record);
        }
    }

    /**
     * Cari Wali Kelas dan kirim LeaveRequestSubmittedNotification.
     */
    private function notifyWaliKelas(LeaveRequest $record): void
    {
        try {
            $record->loadMissing(['student.enrollmentAktif.kelas']);
            $student = $record->student;
            $enrollment = $student?->enrollmentAktif;

            if (!$enrollment) {
                return;
            }

            $kelasAjaran = KelasAjaran::where('class_id', $enrollment->class_id)
                ->where('academic_year_id', $enrollment->academic_year_id)
                ->with(['guru.user'])
                ->first();

            $waliUser = $kelasAjaran?->guru?->user;

            if ($waliUser) {
                $waliUser->notify(new LeaveRequestSubmittedNotification($record));
            }
        } catch (\Throwable $e) {
            Log::error("LeaveRequestObserver: Gagal mengirim notifikasi ke Wali Kelas: " . $e->getMessage());
        }
    }

    /**
     * Cari User Siswa dan kirim LeaveRequestResultNotification.
     */
    private function notifyStudent(LeaveRequest $record): void
    {
        try {
            $record->loadMissing(['student.user']);
            $studentUser = $record->student?->user;

            if ($studentUser) {
                $studentUser->notify(new LeaveRequestResultNotification($record));
            }
        } catch (\Throwable $e) {
            Log::error("LeaveRequestObserver: Gagal mengirim notifikasi hasil ke Siswa: " . $e->getMessage());
        }
    }
}
