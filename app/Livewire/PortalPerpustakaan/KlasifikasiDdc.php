<?php

namespace App\Livewire\PortalPerpustakaan;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use App\Models\KlasifikasiDdc as KlasifikasiDdcModel;

#[Layout('components.layouts.portal')]
class KlasifikasiDdc extends Component
{
    use WithPagination;

    public $search = '';
    
    // Modal state & form
    public $showModal = false;
    public $edit_id = null;
    public $kode_ddc = '';
    public $kategori = '';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function openModal()
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->reset(['edit_id', 'kode_ddc', 'kategori']);
        $this->resetValidation();
    }

    public function edit($id)
    {
        $data = KlasifikasiDdcModel::find($id);
        if ($data) {
            $this->edit_id = $data->id;
            $this->kode_ddc = $data->kode_ddc;
            $this->kategori = $data->kategori;
            $this->showModal = true;
        }
    }

    public function simpan()
    {
        $this->validate([
            'kode_ddc' => 'required|max:50|unique:klasifikasi_ddcs,kode_ddc,' . $this->edit_id,
            'kategori' => 'required|string|max:255',
        ]);

        if ($this->edit_id) {
            $data = KlasifikasiDdcModel::find($this->edit_id);
            if ($data) {
                $data->update([
                    'kode_ddc' => $this->kode_ddc,
                    'kategori' => $this->kategori,
                ]);
                $this->dispatch('notify', ['type' => 'success', 'message' => 'Klasifikasi DDC berhasil diperbarui.']);
            }
        } else {
            KlasifikasiDdcModel::create([
                'kode_ddc' => $this->kode_ddc,
                'kategori' => $this->kategori,
            ]);
            $this->dispatch('notify', ['type' => 'success', 'message' => 'Klasifikasi DDC berhasil ditambahkan.']);
        }

        $this->closeModal();
    }

    public function hapus($id)
    {
        $data = KlasifikasiDdcModel::find($id);
        if ($data) {
            try {
                $data->delete();
                $this->dispatch('notify', ['type' => 'success', 'message' => 'Klasifikasi DDC berhasil dihapus.']);
            } catch (\Exception $e) {
                $this->dispatch('notify', ['type' => 'error', 'message' => 'Gagal menghapus data. Mungkin data ini masih digunakan.']);
            }
        }
    }

    public function render()
    {
        $query = KlasifikasiDdcModel::query();

        if ($this->search) {
            $query->where('kode_ddc', 'like', '%' . $this->search . '%')
                  ->orWhere('kategori', 'like', '%' . $this->search . '%');
        }

        $items = $query->orderBy('kode_ddc', 'asc')->paginate(10);

        return view('livewire.portal-perpustakaan.klasifikasi-ddc', [
            'items' => $items,
        ])->title('Klasifikasi DDC - Portal Perpustakaan');
    }
}
