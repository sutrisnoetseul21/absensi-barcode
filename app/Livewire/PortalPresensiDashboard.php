<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Layout;

class PortalPresensiDashboard extends Component
{
    #[Layout('components.layouts.portal')]
    public function render()
    {
        $today = \Carbon\Carbon::today('Asia/Jakarta')->toDateString();
        $academicYearId = \App\Models\TahunAjaran::where('status', 'aktif')->value('id');

        $hadir = 0;
        $telat = 0;
        $izin = 0;
        $sakit = 0;
        $alpa = 0;

        if ($academicYearId) {
            $hadir = \App\Models\Presensi::where('date', $today)->where('academic_year_id', $academicYearId)->where('status', 'hadir')->count();
            $telat = \App\Models\Presensi::where('date', $today)->where('academic_year_id', $academicYearId)->where('status', 'telat')->count();
            $izin = \App\Models\Presensi::where('date', $today)->where('academic_year_id', $academicYearId)->where('status', 'izin')->count();
            $sakit = \App\Models\Presensi::where('date', $today)->where('academic_year_id', $academicYearId)->where('status', 'sakit')->count();
            $alpa = \App\Models\Presensi::where('date', $today)->where('academic_year_id', $academicYearId)->where('status', 'alpa')->count();
        }

        return view('livewire.portal-presensi-dashboard', [
            'hadir' => $hadir,
            'telat' => $telat,
            'izin' => $izin,
            'sakit' => $sakit,
            'alpa' => $alpa,
        ])->title('Dashboard Portal Absensi');
    }
}
