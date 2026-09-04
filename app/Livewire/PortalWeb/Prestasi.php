<?php

namespace App\Livewire\PortalWeb;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\Models\WebArtikel;
use Illuminate\Support\Str;

class Prestasi extends Component
{
    use WithPagination, WithFileUploads;

    public string $search = '';
    public bool $showModal = false;
    public bool $showDeleteModal = false;
    public ?int $editingId = null;
    public ?int $deletingId = null;

    public string $judul = '';
    public string $konten = '';
    public bool $is_published = true;
    public $foto = null;
    public ?string $existingFoto = null;

    protected function rules(): array
    {
        return [
            'judul'  => 'required|string|max:255',
            'konten' => 'required|string',
            'is_published' => 'boolean',
            'foto'   => 'nullable|image|max:2048',
        ];
    }

    public function updatedSearch(): void { $this->resetPage(); }

    public function openCreate(): void { $this->resetForm(); $this->showModal = true; }

    public function openEdit(int $id): void
    {
        $item = WebArtikel::findOrFail($id);
        $this->editingId    = $item->id;
        $this->judul        = $item->judul;
        $this->konten       = $item->konten ?? '';
        $this->is_published = $item->is_published;
        $this->existingFoto = $item->thumbnail;
        $this->foto         = null;
        $this->showModal    = true;
    }

    public function save(): void
    {
        $this->validate();

        $fotoPath = $this->existingFoto;
        if ($this->foto) {
            $fotoPath = $this->foto->store('web-profil/prestasi', 'public');
        }

        $data = [
            'judul'        => $this->judul,
            'tipe'         => 'prestasi',
            'konten'       => $this->konten,
            'is_published' => $this->is_published,
            'thumbnail'    => $fotoPath,
            'slug'         => Str::slug($this->judul) . '-' . uniqid(),
            'published_at' => $this->is_published ? now() : null,
        ];

        if ($this->editingId) {
            $item = WebArtikel::findOrFail($this->editingId);
            $data['slug'] = $item->slug;
            $item->update($data);
        } else {
            WebArtikel::create($data);
        }

        $this->showModal = false;
        $this->resetForm();
        session()->flash('success', 'Prestasi berhasil disimpan.');
    }

    public function confirmDelete(int $id): void { $this->deletingId = $id; $this->showDeleteModal = true; }

    public function delete(): void
    {
        if ($this->deletingId) {
            WebArtikel::findOrFail($this->deletingId)->delete();
            session()->flash('success', 'Data prestasi berhasil dihapus.');
        }
        $this->showDeleteModal = false;
        $this->deletingId = null;
    }

    private function resetForm(): void
    {
        $this->editingId = null; $this->judul = ''; $this->konten = '';
        $this->is_published = true; $this->foto = null; $this->existingFoto = null;
    }

    #[Layout('components.layouts.portal')]
    public function render()
    {
        $prestasis = WebArtikel::where('tipe', 'prestasi')
            ->when($this->search, fn($q) => $q->where('judul', 'like', '%' . $this->search . '%'))
            ->latest()
            ->paginate(15);

        return view('livewire.portal-web.prestasi', compact('prestasis'))->title('Prestasi — Portal Web');
    }
}
