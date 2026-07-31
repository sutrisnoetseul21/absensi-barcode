<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Pengumuman;
use App\Models\Peminjaman;
use App\Models\TahunAjaran;
use Illuminate\Support\Facades\Auth;

class GuruMainDashboard extends Component
{
    public $teacher;
    public $activeAnnouncements;
    public $activeBooksCount;
    public $kelasAmpuCount;

    public function mount()
    {
        $this->teacher = Auth::user()->teacher;

        // Fetch announcements
        $this->activeAnnouncements = Pengumuman::aktifSekarang()->latest()->get();

        $activeYear = TahunAjaran::where('status', 'aktif')->first();

        if ($this->teacher) {
            $this->activeBooksCount = Peminjaman::where('peminjam_type', 'guru')
                ->where('peminjam_id', $this->teacher->id)
                ->where('status', 'dipinjam')
                ->count();
                
            $this->kelasAmpuCount = 0;
            if ($activeYear) {
                // Calculate unique classes taught
                $kelasIds = [];
                foreach ($this->teacher->kelasAjarans()->where('academic_year_id', $activeYear->id)->get() as $k) {
                    $kelasIds[] = $k->class_id;
                }
                foreach ($this->teacher->pengajarans()->whereHas('kelasAjaran', function($q) use ($activeYear) {
                    $q->where('academic_year_id', $activeYear->id);
                })->get() as $p) {
                    $kelasIds[] = $p->kelasAjaran->class_id;
                }
                $this->kelasAmpuCount = count(array_unique($kelasIds));
            }
        } else {
            $this->activeBooksCount = 0;
            $this->kelasAmpuCount = 0;
        }
    }

    #[Layout('components.layouts.portal')]
    public function render()
    {
        return view('livewire.guru-main-dashboard');
    }
}
