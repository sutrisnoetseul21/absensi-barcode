<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Pengumuman;
use App\Models\Peminjaman;
use App\Models\Presensi;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class SiswaMainDashboard extends Component
{
    public $student;
    public $activeAnnouncements;
    public $attendancePercentage;
    public $activeBooksCount;

    public function mount()
    {
        $this->student = Auth::user()->student;

        // Fetch announcements
        $this->activeAnnouncements = Pengumuman::aktifSekarang()->latest()->get();

        // Calculate attendance percentage for current month
        $this->calculateAttendance();

        // Count active book loans
        if ($this->student) {
            $this->activeBooksCount = Peminjaman::where('peminjam_type', 'siswa')
                ->where('peminjam_id', $this->student->id)
                ->where('status', 'dipinjam')
                ->count();
        } else {
            $this->activeBooksCount = 0;
        }
    }

    private function calculateAttendance()
    {
        if (!$this->student) {
            $this->attendancePercentage = 0;
            return;
        }

        $enrollment = $this->student->enrollments()->latest('created_at')->first();
        if (!$enrollment) {
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
