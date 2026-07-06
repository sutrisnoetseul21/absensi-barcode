<?php

namespace App\Livewire;

use Livewire\Component;

use App\Models\PengaturanSekolah;

class AttendanceKiosk extends Component
{
    public function render()
    {
        $settings = PengaturanSekolah::current();
        $kalenderService = app(\App\Services\KalenderSekolahService::class);
        $isGlobalHoliday = !$kalenderService->isHariSekolah(now('Asia/Jakarta'));

        return view('livewire.attendance-kiosk', [
            'settings' => $settings,
            'isGlobalHoliday' => $isGlobalHoliday
        ])->title('Kios Absensi');
    }
}
