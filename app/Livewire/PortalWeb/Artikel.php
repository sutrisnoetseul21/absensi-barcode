<?php

namespace App\Livewire\PortalWeb;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\Models\WebArtikel;
use Illuminate\Support\Str;

class Artikel extends Component
{
    use WithPagination, WithFileUploads;

    public string $search = '';
    public string $filterTipe = '';

    // Form fields
    public bool $showModal = false;
    public bool $showDeleteModal = false;
    public ?int $editingId = null;
    public ?int $deletingId = null;

    public string $judul = '';
    public string $tipe = 'berita';
    public string $konten = '';
    public bool $is_published = true;
    public $foto = null;
    public ?string $existingFoto = null;

    protected function rules(): array
    {
        return [
            'judul'  => 'required|string|max:255',
            'tipe'   => 'required|in:berita,pengumuman',
            'konten' => 'required|string',
            'is_published' => 'boolean',
            'foto'   => $this->editingId ? 'nullable|image|max:2048' : 'nullable|image|max:2048',
        ];
    }

    public function updatedSearch(): void { $this->resetPage(); }
    public function updatedFilterTipe(): void { $this->resetPage(); }

    public function openCreate(): void
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $artikel = WebArtikel::findOrFail($id);
        $this->editingId   = $artikel->id;
        $this->judul       = $artikel->judul;
        $this->tipe        = $artikel->tipe;
        $this->konten      = $artikel->konten ?? '';
        $this->is_published = $artikel->is_published;
        $this->existingFoto = $artikel->thumbnail;
        $this->foto        = null;
        $this->showModal   = true;
    }

    public function save(): void
    {
        $this->validate();

        $fotoPath = $this->existingFoto;
        if ($this->foto) {
            $fotoPath = $this->foto->store('web-profil/artikel', 'public');
        }

        $data = [
            'judul'     => $this->judul,
            'tipe'      => $this->tipe,
            'konten'    => $this->konten,
            'is_published' => $this->is_published,
            'thumbnail' => $fotoPath,
            'slug'      => Str::slug($this->judul) . '-' . uniqid(),
            'published_at' => $this->is_published ? now() : null,
        ];

        if ($this->editingId) {
            $artikel = WebArtikel::findOrFail($this->editingId);
            $data['slug'] = $artikel->slug; // pertahankan slug asli
            $artikel->update($data);
        } else {
            WebArtikel::create($data);
        }

        $this->showModal = false;
        $this->resetForm();
        session()->flash('success', 'Artikel berhasil ' . ($this->editingId ? 'diperbarui' : 'disimpan') . '.');
    }

    public function confirmDelete(int $id): void
    {
        $this->deletingId = $id;
        $this->showDeleteModal = true;
    }

    public function delete(): void
    {
        if ($this->deletingId) {
            WebArtikel::findOrFail($this->deletingId)->delete();
            session()->flash('success', 'Artikel berhasil dihapus.');
        }
        $this->showDeleteModal = false;
        $this->deletingId = null;
    }

    private function resetForm(): void
    {
        $this->editingId    = null;
        $this->judul        = '';
        $this->tipe         = 'berita';
        $this->konten       = '';
        $this->is_published = true;
        $this->foto         = null;
        $this->existingFoto = null;
    }

    #[Layout('components.layouts.portal')]
    public function render()
    {
        $query = WebArtikel::whereIn('tipe', ['berita', 'pengumuman'])
            ->when($this->search, fn($q) => $q->where('judul', 'like', '%' . $this->search . '%'))
            ->when($this->filterTipe, fn($q) => $q->where('tipe', $this->filterTipe))
            ->latest();

        return view('livewire.portal-web.artikel', [
            'artikels' => $query->paginate(15),
        ])->title('Artikel & Pengumuman — Portal Web');
    }
}
