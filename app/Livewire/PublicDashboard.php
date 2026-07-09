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

        // Fetch data for the new redesign
        $pengaturanSekolah = Cache::remember('public_pengaturan_sekolah', 300, function () {
            return PengaturanSekolah::current();
        });

        $hariLiburs = Cache::remember('public_hari_liburs', 300, function () {
            return HariLibur::whereNull('class_id')
                ->where('start_date', '>=', today())
                ->orderBy('start_date')
                ->take(5)
                ->get();
        });

        $pengumuman = Cache::remember('public_pengumuman', 300, function () {
            return \App\Models\Pengumuman::aktifSekarang()
                ->orderBy('urutan')
                ->orderBy('updated_at', 'desc')
                ->get();
        });

        $today = now('Asia/Jakarta');
        $kalenderService = app(\App\Services\KalenderSekolahService::class);
        $isTodayHoliday = !$kalenderService->isHariSekolah($today);
        
        $holiday = HariLibur::hariIni($today->toDateString())->first();
        $todayHolidayName = $holiday ? $holiday->description : ($today->isWeekend() ? 'Akhir Pekan' : 'Hari Libur');

        $todayStr = $today->toDateString();
        $statusCounts = Presensi::where('academic_year_id', $this->selectedAcademicYearId)
            ->where('date', $todayStr)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $totalStudentsCount = EnrollmentSiswa::where('academic_year_id', $this->selectedAcademicYearId)
            ->where('status', 'aktif')
            ->count();

        $realStats = [
            'total_siswa' => $totalStudentsCount,
            'hadir_telat' => $statusCounts->get('hadir', 0) + $statusCounts->get('telat', 0),
            'sakit' => $statusCounts->get('sakit', 0),
            'izin' => $statusCounts->get('izin', 0),
            'alpa_db' => $statusCounts->get('alpa', 0),
            'belum_absen' => max(0, $totalStudentsCount - $statusCounts->sum()),
        ];

        return view('livewire.public-dashboard', array_merge($data, [
            'academicYears' => $academicYears,
            'months' => $months,
            'pengaturanSekolah' => $pengaturanSekolah,
            'hariLiburs' => $hariLiburs,
            'pengumuman' => $pengumuman,
            'isTodayHoliday' => $isTodayHoliday,
            'todayHolidayName' => $todayHolidayName,
            'realStats' => $realStats,
        ]));
    }
}
