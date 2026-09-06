<?php

namespace App\Livewire\PortalWeb;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\Models\WebSarpra;

class Sarpras extends Component
{
    use WithPagination, WithFileUploads;

    public string $search = '';
    public bool $showModal = false;
    public bool $showDeleteModal = false;
    public ?string $editingId = null;
    public ?string $deletingId = null;

    public string $nama_fasilitas = '';
    public string $deskripsi = '';
    public int $urutan = 0;
    public string $icon = '';
    public string $color = 'text-nature-500';

    protected function rules(): array
    {
        return [
            'nama_fasilitas' => 'required|string|max:255',
            'deskripsi'      => 'nullable|string|max:255',
            'urutan'         => 'integer|min:0',
            'icon'           => 'required|string',
            'color'          => 'required|string',
        ];
    }

    public function updatedSearch(): void { $this->resetPage(); }

    public function openCreate(): void { $this->resetForm(); $this->showModal = true; }

    public function openEdit(string $id): void
    {
        $item = WebSarpra::findOrFail($id);
        $this->editingId      = $item->id;
        $this->nama_fasilitas = $item->nama_fasilitas;
        $this->deskripsi      = $item->deskripsi ?? '';
        $this->urutan         = $item->urutan ?? 0;
        $this->icon           = $item->icon ?? '';
        $this->color          = $item->color ?? 'text-nature-500';
        $this->showModal      = true;
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'nama_fasilitas' => $this->nama_fasilitas,
            'deskripsi'      => $this->deskripsi,
            'urutan'         => $this->urutan,
            'icon'           => $this->icon,
            'color'          => $this->color,
        ];

        if ($this->editingId) {
            WebSarpra::findOrFail($this->editingId)->update($data);
        } else {
            WebSarpra::create($data);
        }

        $this->showModal = false;
        $this->resetForm();
        session()->flash('success', 'Fasilitas sarpras berhasil disimpan.');
    }

    public function confirmDelete(string $id): void { $this->deletingId = $id; $this->showDeleteModal = true; }

    public function delete(): void
    {
        if ($this->deletingId) {
            WebSarpra::findOrFail($this->deletingId)->delete();
            session()->flash('success', 'Fasilitas sarpras berhasil dihapus.');
        }
        $this->showDeleteModal = false;
        $this->deletingId = null;
    }

    private function resetForm(): void
    {
        $this->editingId = null; $this->nama_fasilitas = ''; $this->deskripsi = '';
        $this->urutan = 0; $this->icon = ''; $this->color = 'text-nature-500';
    }

    #[Layout('components.layouts.portal')]
    public function render()
    {
        $sarpras = WebSarpra::when($this->search, fn($q) => $q->where('nama_fasilitas', 'like', '%' . $this->search . '%'))
            ->orderBy('urutan')->orderByDesc('created_at')
            ->paginate(15);

        $availableIcons = \App\Filament\Components\IconPickerField::icons();

        return view('livewire.portal-web.sarpras', compact('sarpras', 'availableIcons'))->title('Sarana Prasarana — Portal Web');
    }
}
