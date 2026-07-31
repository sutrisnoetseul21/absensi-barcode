<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\PengaturanSekolah;

#[Layout('components.layouts.portal')]
class PetugasPerpusSirkulasi extends Component
{
    public function render()
    {
        $settings = PengaturanSekolah::current();

        return view('livewire.petugas-perpus-sirkulasi', [
            'settings' => $settings,
        ])->title('Sirkulasi Perpustakaan - Portal Perpustakaan');
    }
}
