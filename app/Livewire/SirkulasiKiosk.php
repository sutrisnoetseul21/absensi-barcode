<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\PengaturanSekolah;

class SirkulasiKiosk extends Component
{
    public function render()
    {
        $settings = PengaturanSekolah::current();

        return view('livewire.sirkulasi-kiosk', [
            'settings' => $settings,
        ])->title('Sirkulasi Perpustakaan');
    }
}
