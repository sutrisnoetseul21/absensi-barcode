<?php

namespace App\Livewire;

use Livewire\Component;

use App\Models\PengaturanSekolah;

class AttendanceKiosk extends Component
{
    public function render()
    {
        $settings = PengaturanSekolah::current();
        return view('livewire.attendance-kiosk', [
            'settings' => $settings
        ])->title('Kios Absensi');
    }
}
