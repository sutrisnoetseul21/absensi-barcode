<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\TahunAjaran;
use App\Models\Kelas;
use App\Models\Presensi;
use App\Models\EnrollmentSiswa;
use App\Models\PengaturanSekolah;
use App\Models\HariLibur;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Layout;
use Carbon\Carbon;
use App\Actions\GetPublicDashboardDataAction;

class PublicDashboard extends Component
{
    public string $mode = 'public';

    // Filters
    public ?string $selectedAcademicYearId = null;
    public ?int $selectedMonth = null;
    public ?int $selectedYear = null;

    public function mount()
    {
        $this->mode = request()->routeIs('public.display') ? 'display' : 'public';

        $activeYear = TahunAjaran::where('status', 'aktif')->first();
        if ($activeYear) {
            $this->selectedAcademicYearId = $activeYear->id;
        }

        $this->selectedMonth = (int) now()->format('m');
        $this->selectedYear = (int) now()->format('Y');
    }

    public function updatedSelectedAcademicYearId()
    {
        $this->emitChartUpdate();
    }

    public function updatedSelectedMonth()
    {
        $this->emitChartUpdate();
    }

    public function updatedSelectedYear()
    {
        $this->emitChartUpdate();
    }

    public function emitChartUpdate()
    {
        $data = $this->getDashboardData();
        
        $this->dispatch('update-charts', [
            'donut' => $data['donutData'],
            'bar' => [
                'grade7' => $data['barData7'],
                'grade8' => $data['barData8'],
                'grade9' => $data['barData9']
            ],
            'line' => $data['lineData']
        ]);
    }

    public function getDashboardData()
    {
        $action = app(GetPublicDashboardDataAction::class);
        return $action->execute($this->selectedAcademicYearId, $this->selectedMonth, $this->selectedYear);
    }

    #[Layout('components.layouts.app')]
    public function render()
    {
        $data = $this->getDashboardData();
        $academicYears = TahunAjaran::orderBy('name', 'desc')->get();
        
        $months = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];

        return view('livewire.public-dashboard', array_merge($data, [
            'academicYears' => $academicYears,
            'months' => $months
        ]));
    }
}
