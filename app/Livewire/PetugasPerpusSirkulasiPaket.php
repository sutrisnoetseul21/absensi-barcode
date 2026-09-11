<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\PengaturanSekolah;

#[Layout('components.layouts.portal')]
class PetugasPerpusSirkulasiPaket extends Component
{
    public function render()
    {
        $settings = PengaturanSekolah::current();

        return view('livewire.petugas-perpus-sirkulasi-paket', [
            'settings' => $settings,
        ])->title('Sirkulasi Peminjaman Buku Paket (1 Tahun) - Portal Perpustakaan');
    }
}
