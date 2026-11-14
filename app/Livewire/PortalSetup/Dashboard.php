<?php

namespace App\Livewire\PortalSetup;

use App\Models\Jabatan;
use App\Models\Kelas;
use App\Models\PengaturanSekolah;
use App\Models\TahunAjaran;
use Livewire\Attributes\Layout;
use Livewire\Component;

class Dashboard extends Component
{
    #[Layout('components.layouts.portal')]
    public function render()
    {
        $school = PengaturanSekolah::current();
        $schoolLevel = $school?->school_level ?: 'SMP';
        $activeYear = TahunAjaran::where('status', 'aktif')->first();
        $totalClasses = Kelas::count();
        $totalJabatans = Jabatan::count();

        $schoolReady = $school && filled($school->school_name) && filled($school->school_address);
        $yearReady = $activeYear !== null;
        $classReady = $totalClasses > 0;
        $dasarReady = $schoolReady && $yearReady && $classReady;

        $wajibJabatans = Jabatan::wajibByJenjang($schoolLevel);
        $jabatanReady = $dasarReady && Jabatan::whereIn('nama_jabatan', $wajibJabatans)->count() === count($wajibJabatans);

        $completedSteps = collect([$schoolReady, $yearReady, $classReady, $jabatanReady])->filter()->count();
        $totalSteps = 4;
        $progressPercent = (int) round(($completedSteps / $totalSteps) * 100);
        $allReady = $completedSteps === $totalSteps;

        return view('livewire.portal-setup.dashboard', [
            'school'          => $school,
            'schoolLevel'     => $schoolLevel,
            'activeYear'      => $activeYear,
            'totalClasses'    => $totalClasses,
            'totalJabatans'   => $totalJabatans,
            'wajibJabatans'   => $wajibJabatans,
            'schoolReady'     => $schoolReady,
            'yearReady'       => $yearReady,
            'classReady'      => $classReady,
            'dasarReady'      => $dasarReady,
            'jabatanReady'    => $jabatanReady,
            'completedSteps'  => $completedSteps,
            'totalSteps'      => $totalSteps,
            'progressPercent' => $progressPercent,
            'allReady'        => $allReady,
        ])->title('Ringkasan Setup & Instalasi');
    }
}
