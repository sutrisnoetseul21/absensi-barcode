<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Pengumuman;
use App\Models\Peminjaman;
use App\Models\Presensi;
use App\Models\LeaveRequest;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class SiswaMainDashboard extends Component
{
    public $student;
    public $activeAnnouncements;
    public $attendancePercentage = 0;
    public $activeBooksCount = 0;
    public $pendingIjinCount = 0;
    public $approvedIjinCount = 0;
    public $rejectedIjinCount = 0;
    public $totalIjinCount = 0;
    public $kelasName = '-';

    public function mount()
    {
        $this->student = Auth::user()->student;

        // Fetch announcements
        $this->activeAnnouncements = Pengumuman::aktifSekarang()->latest()->get();

        if ($this->student) {
            // Get active enrollment
            $enrollment = $this->student->enrollments()->latest('created_at')->first();
            $this->kelasName = $enrollment?->kelas?->name ?? 'Belum Terdaftar';

            // Calculate attendance percentage for current month
            $this->calculateAttendance($enrollment);

            // Count active book loans
            $this->activeBooksCount = Peminjaman::where('peminjam_type', 'siswa')
                ->where('peminjam_id', $this->student->id)
                ->where('status', 'dipinjam')
                ->count();

            // Count leave requests by status
            $this->pendingIjinCount = LeaveRequest::where('student_id', $this->student->id)
                ->where('status', 'pending')
                ->count();
                
            $this->approvedIjinCount = LeaveRequest::where('student_id', $this->student->id)
                ->where('status', 'approved')
                ->count();
                
            $this->rejectedIjinCount = LeaveRequest::where('student_id', $this->student->id)
                ->where('status', 'rejected')
                ->count();

            $this->totalIjinCount = LeaveRequest::where('student_id', $this->student->id)
                ->count();
        }
    }

    private function calculateAttendance($enrollment)
    {
        if (!$this->student || !$enrollment) {
            $this->attendancePercentage = 0;
            return;
        }

        $now = Carbon::now('Asia/Jakarta');
        $startOfMonth = $now->copy()->startOfMonth();
        $endCalcDate = $now->copy();

        $presensiList = Presensi::where('student_id', $this->student->id)
            ->where('enrollment_id', $enrollment->id)
            ->whereBetween('date', [$startOfMonth->format('Y-m-d'), $endCalcDate->format('Y-m-d')])
            ->get();

        $totalH = 0;
        $totalT = 0;

        foreach ($presensiList as $p) {
            if (strtolower($p->status) === 'hadir') $totalH++;
            if (strtolower($p->status) === 'telat') $totalT++;
        }

        $kalenderService = app(\App\Services\KalenderSekolahService::class);
        $effectiveDays = $kalenderService->getEffectiveDays($startOfMonth, $endCalcDate, $enrollment->class_id);
        $effectiveDays = max(1, $effectiveDays); // Prevent division by zero

        $presentCount = $totalH + $totalT;
        $this->attendancePercentage = min(100, round(($presentCount / $effectiveDays) * 100, 1));
    }

    #[Layout('components.layouts.portal')]
    public function render()
    {
        return view('livewire.siswa-main-dashboard');
    }
}
