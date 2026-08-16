<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Pengumuman;
use App\Models\Peminjaman;
use App\Models\TahunAjaran;
use App\Models\LeaveRequest;
use App\Models\EnrollmentSiswa;
use Illuminate\Support\Facades\Auth;

class GuruMainDashboard extends Component
{
    public $teacher;
    public $activeAnnouncements;
    public $activeBooksCount = 0;
    public $kelasAmpuCount = 0;
    public $totalStudentsCount = 0;
    public $pendingLeaveRequestsCount = 0;
    public $approvedLeaveRequestsCount = 0;
    public $rejectedLeaveRequestsCount = 0;

    // Multi-Portal Access Flags
    public $hasPresensiAccess = false;
    public $hasPerpusAccess = false;
    public $isSuperAdmin = false;

    public function mount()
    {
        $user = Auth::user();
        $this->teacher = $user->teacher;

        // Check Multi-Portal Permissions
        $this->isSuperAdmin = $user->hasRole('super_admin');
        $this->hasPresensiAccess = $this->isSuperAdmin || $user->hasRole(['admin_portal_presensi', 'petugas_presensi']);
        $this->hasPerpusAccess = $this->isSuperAdmin || $user->hasRole(['petugas_perpustakaan', 'admin_perpustakaan']);

        // Fetch announcements
        $this->activeAnnouncements = Pengumuman::aktifSekarang()->latest()->get();

        $activeYear = TahunAjaran::where('status', 'aktif')->first();

        if ($this->teacher && $activeYear) {
            // Pinjaman buku pribadi guru
            $this->activeBooksCount = Peminjaman::where('peminjam_type', 'guru')
                ->where('peminjam_id', $this->teacher->id)
                ->where('status', 'dipinjam')
                ->count();
                
            // Dapatkan seluruh class_id binaan (Wali Kelas + Kelas Pantau BK + Pengajaran)
            $kelasWali = $this->teacher->kelasAjarans()->where('academic_year_id', $activeYear->id)->pluck('class_id')->toArray();
            $kelasPantau = $this->teacher->kelasPantau()->where('academic_year_id', $activeYear->id)->pluck('class_id')->toArray();
            
            $kelasAjar = [];
            foreach ($this->teacher->pengajarans()->whereHas('kelasAjaran', function($q) use ($activeYear) {
                $q->where('academic_year_id', $activeYear->id);
            })->get() as $p) {
                $kelasAjar[] = $p->kelasAjaran->class_id;
            }

            $allClassIds = array_unique(array_merge($kelasWali, $kelasPantau, $kelasAjar));
            $this->kelasAmpuCount = count($allClassIds);

            // Total siswa di kelas binaan
            $this->totalStudentsCount = EnrollmentSiswa::whereIn('class_id', $allClassIds)
                ->where('academic_year_id', $activeYear->id)
                ->where('status', 'aktif')
                ->count();

            // Hitung permohonan ijin pending dari siswa di kelas yang diakses guru
            $accessibleClassIds = array_unique(array_merge($kelasWali, $kelasPantau));
            if (!empty($accessibleClassIds)) {
                $this->pendingLeaveRequestsCount = LeaveRequest::where('status', 'pending')
                    ->where('academic_year_id', $activeYear->id)
                    ->whereHas('student.enrollments', function ($q) use ($accessibleClassIds, $activeYear) {
                        $q->whereIn('class_id', $accessibleClassIds)
                          ->where('academic_year_id', $activeYear->id)
                          ->where('status', 'aktif');
                    })
                    ->count();
                    
                $this->approvedLeaveRequestsCount = LeaveRequest::where('status', 'approved')
                    ->where('academic_year_id', $activeYear->id)
                    ->whereHas('student.enrollments', function ($q) use ($accessibleClassIds, $activeYear) {
                        $q->whereIn('class_id', $accessibleClassIds)
                          ->where('academic_year_id', $activeYear->id)
                          ->where('status', 'aktif');
                    })
                    ->count();
                    
                $this->rejectedLeaveRequestsCount = LeaveRequest::where('status', 'rejected')
                    ->where('academic_year_id', $activeYear->id)
                    ->whereHas('student.enrollments', function ($q) use ($accessibleClassIds, $activeYear) {
                        $q->whereIn('class_id', $accessibleClassIds)
                          ->where('academic_year_id', $activeYear->id)
                          ->where('status', 'aktif');
                    })
                    ->count();
            } else {
                $this->pendingLeaveRequestsCount = 0;
                $this->approvedLeaveRequestsCount = 0;
                $this->rejectedLeaveRequestsCount = 0;
            }
        }
    }

    #[Layout('components.layouts.portal')]
    public function render()
    {
        return view('livewire.guru-main-dashboard');
    }
}
