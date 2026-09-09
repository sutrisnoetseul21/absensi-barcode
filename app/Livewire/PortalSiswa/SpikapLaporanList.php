<?php

namespace App\Livewire\PortalSiswa;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Auth;
use App\Models\SpikapLaporan;

#[Layout('components.layouts.portal')]
class SpikapLaporanList extends Component
{
    public $student;
    public ?int $selectedLaporanId = null;

    public function mount(): void
    {
        $this->student = Auth::user()->student;

        if (!$this->student || !in_array($this->student->status, ['aktif', 'active'])) {
            abort(403, 'Akses ditolak. Anda tidak terdaftar sebagai siswa aktif.');
        }
    }

    public function toggleDetail(int $id): void
    {
        $this->selectedLaporanId = ($this->selectedLaporanId === $id) ? null : $id;
    }

    public function render()
    {
        $laporan = SpikapLaporan::where('student_id', $this->student->id)
            ->with(['lampiran', 'logStatus.changedBy'])
            ->latest()
            ->get();

        return view('livewire.portal-siswa.spikap-laporan-list', compact('laporan'));
    }
}
