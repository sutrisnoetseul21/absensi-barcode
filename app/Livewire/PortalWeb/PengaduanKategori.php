<?php

namespace App\Livewire\PortalWeb;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\PengaduanKategori as ModelPengaduanKategori;

class PengaduanKategori extends Component
{
    public string $search = '';
    public bool $showModal = false;
    public bool $showDeleteModal = false;
    
    public ?int $editingId = null;
    public ?int $deletingId = null;

    public string $nama_kategori = '';
    public int $urutan = 0;

    protected function rules(): array
    {
        return [
            'nama_kategori' => 'required|string|max:255',
            'urutan' => 'required|integer|min:0',
        ];
    }

    public function openCreate(): void 
    { 
        $this->resetForm(); 
        $this->showModal = true; 
    }

    public function openEdit(int $id): void
    {
        $kategori = ModelPengaduanKategori::findOrFail($id);
        $this->editingId = $kategori->id;
        $this->nama_kategori = $kategori->nama_kategori;
        $this->urutan = $kategori->urutan;
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'nama_kategori' => $this->nama_kategori,
            'urutan' => $this->urutan,
        ];

        if ($this->editingId) {
            ModelPengaduanKategori::findOrFail($this->editingId)->update($data);
            session()->flash('success', 'Kategori berhasil diperbarui.');
        } else {
            ModelPengaduanKategori::create($data);
            session()->flash('success', 'Kategori berhasil ditambahkan.');
        }

        $this->showModal = false;
        $this->resetForm();
    }

    public function confirmDelete(int $id): void 
    { 
        $this->deletingId = $id; 
        $this->showDeleteModal = true; 
    }

    public function delete(): void
    {
        if ($this->deletingId) {
            ModelPengaduanKategori::findOrFail($this->deletingId)->delete();
            session()->flash('success', 'Kategori berhasil dihapus.');
        }
        $this->showDeleteModal = false;
        $this->deletingId = null;
    }

    private function resetForm(): void
    {
        $this->editingId = null; 
        $this->nama_kategori = ''; 
        $this->urutan = 0;
    }

    #[Layout('components.layouts.portal')]
    public function render()
    {
        $kategoris = ModelPengaduanKategori::withCount('pengaduans')
            ->when($this->search, fn($q) => $q->where('nama_kategori', 'like', '%' . $this->search . '%'))
            ->orderBy('urutan')
            ->get();

        return view('livewire.portal-web.pengaduan-kategori', compact('kategoris'))
            ->title('Kategori Pengaduan — Portal Web');
    }
}
