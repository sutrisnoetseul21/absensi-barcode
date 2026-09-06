<?php

namespace App\Livewire\PortalWeb;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\AlumniJenjang as ModelAlumniJenjang;

class AlumniJenjang extends Component
{
    public string $search = '';
    public bool $showModal = false;
    public bool $showDeleteModal = false;
    public ?int $editingId = null;
    public ?int $deletingId = null;

    public string $nama_jenjang = '';

    protected function rules(): array
    {
        return [
            'nama_jenjang' => 'required|string|max:255',
        ];
    }

    public function openCreate(): void
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $item = ModelAlumniJenjang::findOrFail($id);
        $this->editingId = $item->id;
        $this->nama_jenjang = $item->nama_jenjang;
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate();

        if ($this->editingId) {
            ModelAlumniJenjang::findOrFail($this->editingId)->update([
                'nama_jenjang' => $this->nama_jenjang,
            ]);
            session()->flash('success', 'Jenjang lanjutan berhasil diperbarui.');
        } else {
            ModelAlumniJenjang::create([
                'nama_jenjang' => $this->nama_jenjang,
            ]);
            session()->flash('success', 'Jenjang lanjutan berhasil ditambahkan.');
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
            $item = ModelAlumniJenjang::findOrFail($this->deletingId);
            $item->delete();
            session()->flash('success', 'Jenjang berhasil dihapus.');
        }
        $this->showDeleteModal = false;
        $this->deletingId = null;
    }

    private function resetForm(): void
    {
        $this->editingId = null;
        $this->nama_jenjang = '';
    }

    #[Layout('components.layouts.portal')]
    public function render()
    {
        $jenjangs = ModelAlumniJenjang::withCount('alumnis')
            ->when($this->search, function ($q) {
                $q->where('nama_jenjang', 'like', '%' . $this->search . '%');
            })
            ->latest()
            ->get();

        return view('livewire.portal-web.alumni-jenjang', compact('jenjangs'))
            ->title('Pilihan Jenjang Alumni — Portal Web');
    }
}
