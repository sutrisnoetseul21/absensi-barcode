<?php

namespace App\Livewire\PortalSiswa;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use App\Models\LeaveRequest;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.portal')]
class IjinKehadiranList extends Component
{
    use WithPagination;

    public $student;
    
    // Toggle untuk melihat riwayat log suatu pengajuan
    public $showLogId = null;

    public function mount()
    {
        $this->student = Auth::user()->student;
    }

    public function toggleLog($id)
    {
        if ($this->showLogId === $id) {
            $this->showLogId = null;
        } else {
            $this->showLogId = $id;
        }
    }

    public function deleteRequest($id)
    {
        $request = LeaveRequest::where('student_id', $this->student->id)
            ->where('id', $id)
            ->where('status', 'pending')
            ->first();

        if ($request) {
            $request->recordLog('deleted', 'Dibatalkan oleh siswa');
            $request->delete();
            
            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Pengajuan berhasil dibatalkan.'
            ]);
        }
    }

    public function render()
    {
        $requests = LeaveRequest::where('student_id', $this->student->id)
            ->with(['approvedBy', 'logs.user'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('livewire.portal-siswa.ijin-kehadiran-list', [
            'requests' => $requests
        ]);
    }
}
