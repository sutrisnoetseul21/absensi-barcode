<?php

namespace App\Livewire\PortalWeb;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use App\Models\Pengaduan;

class PengaduanData extends Component
{
    use WithPagination;

    public string $search = '';
    public string $filterStatus = '';
    public bool $showModal = false;
    public bool $showDeleteModal = false;
    
    public ?Pengaduan $selectedPengaduan = null;
    public string $updateStatus = '';
    public ?int $deletingId = null;

    public function updatedSearch(): void { $this->resetPage(); }
    public function updatedFilterStatus(): void { $this->resetPage(); }

    public function viewDetails(int $id): void
    {
        $this->selectedPengaduan = Pengaduan::with('kategori')->findOrFail($id);
        $this->updateStatus = $this->selectedPengaduan->status;
        $this->showModal = true;
    }

    public function saveStatus(): void
    {
        if ($this->selectedPengaduan) {
            $this->validate(['updateStatus' => 'required|in:menunggu,diproses,selesai']);
            
            $this->selectedPengaduan->update([
                'status' => $this->updateStatus
            ]);

            $this->showModal = false;
            session()->flash('success', 'Status pengaduan berhasil diperbarui.');
        }
    }

    public function confirmDelete(int $id): void 
    { 
        $this->deletingId = $id; 
        $this->showDeleteModal = true; 
    }

    public function delete(): void
    {
        if ($this->deletingId) {
            Pengaduan::findOrFail($this->deletingId)->delete();
            session()->flash('success', 'Pengaduan berhasil dihapus.');
        }
        $this->showDeleteModal = false;
        $this->deletingId = null;
    }

    #[Layout('components.layouts.portal')]
    public function render()
    {
        $pengaduans = Pengaduan::with('kategori')
            ->when($this->search, function($q) {
                $q->where(function($subQ) {
                    $subQ->where('nama', 'like', '%' . $this->search . '%')
                         ->orWhere('email', 'like', '%' . $this->search . '%')
                         ->orWhere('isi_pengaduan', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->filterStatus, function($q) {
                $q->where('status', $this->filterStatus);
            })
            ->latest()
            ->paginate(15);

        return view('livewire.portal-web.pengaduan-data', compact('pengaduans'))
            ->title('Data Pengaduan — Portal Web');
    }
}
