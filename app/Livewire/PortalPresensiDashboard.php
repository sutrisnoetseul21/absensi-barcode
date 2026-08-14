<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Layout;

class PortalPresensiDashboard extends Component
{
    #[Layout('components.layouts.app')]
    public function render()
    {
        return view('livewire.portal-presensi-dashboard')->title('Dashboard Portal Absensi');
    }
}
