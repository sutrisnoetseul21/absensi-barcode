<?php

namespace App\Livewire\PortalWeb;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use App\Models\WebPillar;
use App\Filament\Components\IconPickerField;

class PilarKeunggulan extends Component
{
    use WithPagination;

    public string $search = '';
    public bool $showModal = false;
    public bool $showDeleteModal = false;
    public ?string $editingId = null;
    public ?string $deletingId = null;

    public string $title = '';
    public string $tag = '';
    public string $desc = '';
    public string $icon = 'fas fa-star';
    public string $color_theme = 'blue';
    public string $link = '';
    public int $urutan = 0;
    public bool $is_active = true;

    protected function rules(): array
    {
        return [
            'title'       => 'required|string|max:255',
            'tag'         => 'nullable|string|max:100',
            'desc'        => 'nullable|string|max:1000',
            'icon'        => 'required|string|max:100',
            'color_theme' => 'required|string|max:50',
            'link'        => 'nullable|url|max:500',
            'urutan'      => 'integer|min:0',
            'is_active'   => 'boolean',
        ];
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function openCreate(): void
    {
        $this->resetForm();
        $this->urutan = (WebPillar::max('urutan') ?? 0) + 1;
        $this->showModal = true;
    }

    public function openEdit(string $id): void
    {
        $item = WebPillar::findOrFail($id);
        $this->editingId   = $item->id;
        $this->title       = $item->title;
        $this->tag         = $item->tag ?? '';
        $this->desc        = $item->desc ?? '';
        $this->icon        = $item->icon ?? 'fas fa-star';
        $this->color_theme = $item->color_theme ?? 'blue';
        $this->link        = $item->link ?? '';
        $this->urutan      = $item->urutan ?? 0;
        $this->is_active   = (bool) $item->is_active;
        $this->showModal   = true;
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'title'       => $this->title,
            'tag'         => $this->tag ?: null,
            'desc'        => $this->desc ?: null,
            'icon'        => $this->icon,
            'color_theme' => $this->color_theme,
            'link'        => $this->link ?: null,
            'urutan'      => $this->urutan,
            'is_active'   => $this->is_active,
        ];

        if ($this->editingId) {
            WebPillar::findOrFail($this->editingId)->update($data);
            session()->flash('success', 'Pilar keunggulan berhasil diperbarui.');
        } else {
            WebPillar::create($data);
            session()->flash('success', 'Pilar keunggulan baru berhasil ditambahkan.');
        }

        $this->showModal = false;
        $this->resetForm();
    }

    public function toggleStatus(string $id): void
    {
        $item = WebPillar::findOrFail($id);
        $item->update(['is_active' => !$item->is_active]);
        session()->flash('success', 'Status pilar berhasil diperbarui.');
    }

    public function confirmDelete(string $id): void
    {
        $this->deletingId = $id;
        $this->showDeleteModal = true;
    }

    public function delete(): void
    {
        if ($this->deletingId) {
            WebPillar::findOrFail($this->deletingId)->delete();
            session()->flash('success', 'Pilar keunggulan berhasil dihapus.');
        }
        $this->showDeleteModal = false;
        $this->deletingId = null;
    }

    private function resetForm(): void
    {
        $this->editingId   = null;
        $this->title       = '';
        $this->tag         = '';
        $this->desc        = '';
        $this->icon        = 'fas fa-star';
        $this->color_theme = 'blue';
        $this->link        = '';
        $this->urutan      = 0;
        $this->is_active   = true;
    }

    #[Layout('components.layouts.portal')]
    public function render()
    {
        $pillars = WebPillar::when($this->search, function ($q) {
            $q->where('title', 'like', '%' . $this->search . '%')
              ->orWhere('tag', 'like', '%' . $this->search . '%')
              ->orWhere('desc', 'like', '%' . $this->search . '%');
        })
        ->orderBy('urutan')
        ->orderByDesc('created_at')
        ->paginate(12);

        $availableIcons  = IconPickerField::icons();
        $availableThemes = WebPillar::colorThemes();

        return view('livewire.portal-web.pilar-keunggulan', compact('pillars', 'availableIcons', 'availableThemes'))
            ->title('Pilar Keunggulan — Portal Web');
    }
}
