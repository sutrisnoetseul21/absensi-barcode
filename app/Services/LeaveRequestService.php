<?php

namespace App\Services;

use App\Models\LeaveRequest;
use App\Models\Presensi;
use App\Models\EnrollmentSiswa;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class LeaveRequestService
{
    /**
     * Generate or update attendance records for an approved LeaveRequest.
     */
    public function syncAttendances(LeaveRequest $record): int
    {
        if ($record->status !== 'approved') {
            return 0;
        }

        $enrollment = EnrollmentSiswa::where('student_id', $record->student_id)
            ->where('academic_year_id', $record->academic_year_id)
            ->where('status', 'aktif')
            ->first();

        if (!$enrollment) {
            return 0;
        }

        $kalenderService = app(KalenderSekolahService::class);
        $currentDate = Carbon::parse($record->start_date);
        $endDate = Carbon::parse($record->end_date);
        $user = auth()->user();
        $generatedCount = 0;

        while ($currentDate->lessThanOrEqualTo($endDate)) {
            $dateString = $currentDate->toDateString();

            $existingAttendance = Presensi::where('student_id', $record->student_id)
                ->where('date', $dateString)
                ->first();

            // Skip jika sudah terlanjur terabsen fisik (hadir/telat)
            if ($existingAttendance && in_array($existingAttendance->status, ['hadir', 'telat'])) {
                $currentDate->addDay();
                continue;
            }

            $isHariSekolah = $kalenderService->isHariSekolah($currentDate, $enrollment->class_id);
            $mappedStatus = $record->type === 'ijin' ? 'izin' : $record->type;
            $attendanceStatus = $isHariSekolah ? $mappedStatus : 'libur';

            Presensi::updateOrCreate(
                [
                    'student_id' => $record->student_id,
                    'date' => $dateString,
                ],
                [
                    'enrollment_id' => $enrollment->id,
                    'class_id' => $enrollment->class_id,
                    'academic_year_id' => $enrollment->academic_year_id,
                    'status' => $attendanceStatus,
                    'note' => $record->reason,
                    'is_manual_input' => true,
                    'manual_input_by_id' => $user?->id,
                    'manual_input_by_type' => $user ? get_class($user) : null,
                    'scanned_by' => $user?->id,
                    'leave_request_id' => $record->id,
                ]
            );

            $generatedCount++;
            $currentDate->addDay();
        }

        return $generatedCount;
    }

    /**
     * Remove generated attendance records when a LeaveRequest is deleted or rejected.
     */
    public function removeAttendances(LeaveRequest $record): int
    {
        return Presensi::where('leave_request_id', $record->id)->delete();
    }
}
