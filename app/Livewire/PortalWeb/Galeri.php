<?php

namespace App\Livewire\PortalWeb;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\Models\WebGaleri;

class Galeri extends Component
{
    use WithPagination, WithFileUploads;

    public string $search = '';
    public bool $showModal = false;
    public bool $showDeleteModal = false;
    public ?int $editingId = null;
    public ?int $deletingId = null;

    public string $judul = '';
    public string $keterangan = '';
    public int $urutan = 0;
    public $foto = null;
    public ?string $existingFoto = null;

    protected function rules(): array
    {
        return [
            'judul'     => 'required|string|max:255',
            'keterangan'=> 'nullable|string|max:500',
            'urutan'    => 'integer|min:0',
            'foto'      => $this->editingId ? 'nullable|image|max:3072' : 'required|image|max:3072',
        ];
    }

    public function updatedSearch(): void { $this->resetPage(); }

    public function openCreate(): void { $this->resetForm(); $this->showModal = true; }

    public function openEdit(int $id): void
    {
        $item = WebGaleri::findOrFail($id);
        $this->editingId    = $item->id;
        $this->judul        = $item->judul;
        $this->keterangan   = $item->keterangan ?? '';
        $this->urutan       = $item->urutan ?? 0;
        $this->existingFoto = $item->foto_path;
        $this->foto         = null;
        $this->showModal    = true;
    }

    public function save(): void
    {
        $this->validate();

        $fotoPath = $this->existingFoto;
        if ($this->foto) {
            $fotoPath = $this->foto->store('web-profil/galeri', 'public');
        }

        $data = [
            'judul'      => $this->judul,
            'keterangan' => $this->keterangan,
            'urutan'     => $this->urutan,
            'foto_path'  => $fotoPath,
        ];

        if ($this->editingId) {
            WebGaleri::findOrFail($this->editingId)->update($data);
        } else {
            WebGaleri::create($data);
        }

        $this->showModal = false;
        $this->resetForm();
        session()->flash('success', 'Foto galeri berhasil disimpan.');
    }

    public function confirmDelete(int $id): void { $this->deletingId = $id; $this->showDeleteModal = true; }

    public function delete(): void
    {
        if ($this->deletingId) {
            WebGaleri::findOrFail($this->deletingId)->delete();
            session()->flash('success', 'Foto berhasil dihapus.');
        }
        $this->showDeleteModal = false;
        $this->deletingId = null;
    }

    private function resetForm(): void
    {
        $this->editingId = null; $this->judul = ''; $this->keterangan = '';
        $this->urutan = 0; $this->foto = null; $this->existingFoto = null;
    }

    #[Layout('components.layouts.portal')]
    public function render()
    {
        $galeris = WebGaleri::when($this->search, fn($q) => $q->where('judul', 'like', '%' . $this->search . '%'))
            ->orderBy('urutan')->orderByDesc('created_at')
            ->paginate(18);

        return view('livewire.portal-web.galeri', compact('galeris'))->title('Galeri Foto — Portal Web');
    }
}
