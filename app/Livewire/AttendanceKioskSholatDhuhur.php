<?php

namespace App\Livewire;

use App\Models\PengaturanSekolah;
use App\Models\PresensiSholatDhuhur;
use App\Services\PresensiSholatDhuhurService;
use Carbon\Carbon;
use Livewire\Component;

class AttendanceKioskSholatDhuhur extends Component
{
    public function render()
    {
        $settings = PengaturanSekolah::current();
        $now = Carbon::now('Asia/Jakarta');
        $service = app(PresensiSholatDhuhurService::class);

        $isFriday = $now->isFriday();
        $isHariSholat = $service->isHariSholatDhuhur($now);
        $holidayDesc = $service->getHolidayDescription($now);

        $todayScannedCount = PresensiSholatDhuhur::whereDate('date', $now->toDateString())
            ->where('status', 'hadir')
            ->count();

        // Ambil 10 siswa terakhir yang scan hari ini
        $recentScans = PresensiSholatDhuhur::with(['siswa', 'kelas'])
            ->whereDate('date', $now->toDateString())
            ->where('status', 'hadir')
            ->orderBy('updated_at', 'desc')
            ->take(10)
            ->get();

        return view('livewire.attendance-kiosk-sholat-dhuhur', [
            'settings'          => $settings,
            'isFriday'          => $isFriday,
            'isHariSholat'      => $isHariSholat,
            'holidayDesc'       => $holidayDesc,
            'todayScannedCount' => $todayScannedCount,
            'recentScans'       => $recentScans,
            'now'               => $now,
        ])->title('Kiosk Presensi Sholat Dhuhur - ' . ($settings->school_name ?? 'Sekolah'));
    }
}
