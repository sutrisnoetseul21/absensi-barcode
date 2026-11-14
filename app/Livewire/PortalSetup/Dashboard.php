<?php

namespace App\Livewire\PortalSetup;

use App\Models\Jabatan;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\PengaturanSekolah;
use App\Models\TahunAjaran;
use Illuminate\Support\Facades\Artisan;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Spatie\Permission\Models\Role;

class Dashboard extends Component
{
    public function runCoreSeeder(): void
    {
        Artisan::call('db:seed', [
            '--class' => \Database\Seeders\CoreSystemSeeder::class,
            '--force' => true,
        ]);
        session()->flash('setup_success', 'Role & pengaturan inti sistem berhasil diinisialisasi.');
    }

    #[Layout('components.layouts.portal')]
    public function render()
    {
        $school = PengaturanSekolah::current();
        $schoolLevel = $school?->school_level ?: 'SMP';
        $activeYear = TahunAjaran::where('status', 'aktif')->first();
        $totalClasses = Kelas::count();
        $totalJabatans = Jabatan::count();
        $totalMapels = MataPelajaran::count();

        $schoolReady = $school && filled($school->school_name) && filled($school->school_address);
        $yearReady = $activeYear !== null;
        $classReady = $totalClasses > 0;
        $dasarReady = $schoolReady && $yearReady && $classReady;

        $wajibJabatans = Jabatan::wajibByJenjang($schoolLevel);
        $jabatanReady = $dasarReady && Jabatan::whereIn('nama_jabatan', $wajibJabatans)->count() === count($wajibJabatans);
        $mapelReady = $dasarReady && $totalMapels > 0;

        $completedSteps = collect([$schoolReady, $yearReady, $classReady, $jabatanReady, $mapelReady])->filter()->count();
        $totalSteps = 5;
        $progressPercent = (int) round(($completedSteps / $totalSteps) * 100);
        $allReady = $completedSteps === $totalSteps;
        $coreSeeded = Role::where('name', 'siswa')->exists() && Role::where('name', 'admin_portal_akademik')->exists();

        return view('livewire.portal-setup.dashboard', [
            'school'          => $school,
            'schoolLevel'     => $schoolLevel,
            'activeYear'      => $activeYear,
            'totalClasses'    => $totalClasses,
            'totalJabatans'   => $totalJabatans,
            'totalMapels'     => $totalMapels,
            'wajibJabatans'   => $wajibJabatans,
            'schoolReady'     => $schoolReady,
            'yearReady'       => $yearReady,
            'classReady'      => $classReady,
            'dasarReady'      => $dasarReady,
            'jabatanReady'    => $jabatanReady,
            'mapelReady'      => $mapelReady,
            'completedSteps'  => $completedSteps,
            'totalSteps'      => $totalSteps,
            'progressPercent' => $progressPercent,
            'allReady'        => $allReady,
            'coreSeeded'      => $coreSeeded,
        ])->title('Ringkasan Setup & Instalasi');
    }
}
