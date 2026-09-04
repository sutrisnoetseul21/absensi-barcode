<?php

namespace App\Livewire\PortalWeb;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use App\Models\WebQuickLink;

class Pelayanan extends Component
{
    use WithPagination;

    public string $search = '';
    public bool $showModal = false;
    public bool $showDeleteModal = false;
    public ?int $editingId = null;
    public ?int $deletingId = null;

    public string $title = '';
    public string $description = '';
    public string $url = '';
    public string $icon = 'link';
    public string $color_class = 'bg-blue-500';
    public bool $is_active = true;
    public int $order = 0;

    protected function rules(): array
    {
        return [
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'url'         => 'required|url|max:500',
            'icon'        => 'nullable|string|max:100',
            'color_class' => 'nullable|string|max:100',
            'is_active'   => 'boolean',
            'order'       => 'integer|min:0',
        ];
    }

    public function updatedSearch(): void { $this->resetPage(); }

    public function openCreate(): void { $this->resetForm(); $this->showModal = true; }

    public function openEdit(int $id): void
    {
        $item = WebQuickLink::findOrFail($id);
        $this->editingId   = $item->id;
        $this->title       = $item->title;
        $this->description = $item->description ?? '';
        $this->url         = $item->url;
        $this->icon        = $item->icon ?? 'link';
        $this->color_class = $item->color_class ?? 'bg-blue-500';
        $this->is_active   = (bool) $item->is_active;
        $this->order       = $item->order ?? 0;
        $this->showModal   = true;
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'title'       => $this->title,
            'description' => $this->description ?: null,
            'url'         => $this->url,
            'icon'        => $this->icon,
            'color_class' => $this->color_class,
            'is_active'   => $this->is_active,
            'order'       => $this->order,
        ];

        if ($this->editingId) {
            WebQuickLink::findOrFail($this->editingId)->update($data);
        } else {
            WebQuickLink::create($data);
        }

        $this->showModal = false;
        $this->resetForm();
        session()->flash('success', 'Layanan publik berhasil disimpan.');
    }

    public function confirmDelete(int $id): void { $this->deletingId = $id; $this->showDeleteModal = true; }

    public function delete(): void
    {
        if ($this->deletingId) {
            WebQuickLink::findOrFail($this->deletingId)->delete();
            session()->flash('success', 'Layanan berhasil dihapus.');
        }
        $this->showDeleteModal = false;
        $this->deletingId = null;
    }

    private function resetForm(): void
    {
        $this->editingId = null; $this->title = ''; $this->description = '';
        $this->url = ''; $this->icon = 'link'; $this->color_class = 'bg-blue-500';
        $this->is_active = true; $this->order = 0;
    }

    #[Layout('components.layouts.portal')]
    public function render()
    {
        $pelayanans = WebQuickLink::when($this->search, fn($q) => $q->where('title', 'like', '%' . $this->search . '%'))
            ->orderBy('order')->orderBy('title')
            ->paginate(20);

        return view('livewire.portal-web.pelayanan', compact('pelayanans'))->title('Pelayanan Publik — Portal Web');
    }
}
