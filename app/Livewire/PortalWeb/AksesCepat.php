<?php

namespace App\Livewire\PortalWeb;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use App\Models\WebQuickLink;

class AksesCepat extends Component
{
    use WithPagination;

    public string $search = '';
    public bool $showModal = false;
    public bool $showDeleteModal = false;
    public ?string $editingId = null;
    public ?string $deletingId = null;

    public string $title = '';
    public string $description = '';
    public string $url = '';
    public string $icon = 'fas fa-link';
    public string $color_class = 'bg-blue-500';
    public bool $is_active = true;
    public int $order = 0;

    protected function rules(): array
    {
        return [
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'url'         => 'required|url|max:500',
            'icon'        => 'required|string|max:100',
            'color_class' => 'required|string|max:100',
            'is_active'   => 'boolean',
            'order'       => 'integer|min:0',
        ];
    }

    public function updatedSearch(): void { $this->resetPage(); }

    public function openCreate(): void 
    { 
        $this->resetForm(); 
        $this->order = (WebQuickLink::max('order') ?? 0) + 1;
        $this->showModal = true; 
    }

    public function openEdit(string $id): void
    {
        $item = WebQuickLink::findOrFail($id);
        $this->editingId   = $item->id;
        $this->title       = $item->title;
        $this->description = $item->description ?? '';
        $this->url         = $item->url;
        $this->icon        = $item->icon ?? 'fas fa-link';
        // Normalize if icon was saved without 'fa' prefix
        if (!str_starts_with($this->icon, 'fa')) {
            $this->icon = 'fas fa-' . $this->icon;
        }
        $this->color_class = $item->color_class ?? 'bg-blue-500';
        $this->is_active   = (bool) $item->is_active;
        $this->order       = $item->order ?? 0;
        $this->showModal   = true;
    }

    public function toggleActive(string|int $id): void
    {
        $item = WebQuickLink::findOrFail($id);
        $item->update(['is_active' => !$item->is_active]);
        session()->flash('success', 'Status ' . $item->title . ' berhasil diperbarui.');
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
        session()->flash('success', 'Akses Cepat berhasil disimpan.');
    }

    public function confirmDelete(string $id): void { $this->deletingId = $id; $this->showDeleteModal = true; }

    public function delete(): void
    {
        if ($this->deletingId) {
            WebQuickLink::findOrFail($this->deletingId)->delete();
            session()->flash('success', 'Akses Cepat berhasil dihapus.');
        }
        $this->showDeleteModal = false;
        $this->deletingId = null;
    }

    private function resetForm(): void
    {
        $this->editingId = null; 
        $this->title = ''; 
        $this->description = '';
        $this->url = ''; 
        $this->icon = 'fas fa-link'; 
        $this->color_class = 'bg-blue-500';
        $this->is_active = true; 
        $this->order = 0;
    }

    #[Layout('components.layouts.portal')]
    public function render()
    {
        $pelayanans = WebQuickLink::when($this->search, fn($q) => $q->where('title', 'like', '%' . $this->search . '%')->orWhere('url', 'like', '%' . $this->search . '%'))
            ->orderBy('order')->orderBy('title')
            ->paginate(25);

        $availableIcons = \App\Filament\Components\IconPickerField::icons();

        $colorOptions = [
            'bg-blue-500'    => '🔵 Biru (Blue)',
            'bg-emerald-500' => '🟢 Hijau (Emerald)',
            'bg-green-500'   => '🍃 Hijau Segar (Green)',
            'bg-violet-500'  => '🟣 Ungu (Violet)',
            'bg-purple-500'  => '🪻 Ungu Pekat (Purple)',
            'bg-amber-500'   => '🟡 Kuning / Emas (Amber)',
            'bg-orange-500'  => '🟠 Oranye (Orange)',
            'bg-rose-500'    => '🔴 Merah Mawar (Rose)',
            'bg-red-500'     => '🛑 Merah Terang (Red)',
            'bg-teal-500'    => '🩵 Tosca (Teal)',
            'bg-cyan-500'    => '🔷 Cyan (Cyan)',
            'bg-indigo-500'  => '🌀 Nila (Indigo)',
            'bg-pink-500'    => '🌸 Merah Muda (Pink)',
            'bg-slate-600'   => '⬛ Abu-abu (Slate)',
        ];

        return view('livewire.portal-web.akses-cepat', compact('pelayanans', 'availableIcons', 'colorOptions'))->title('Akses Cepat — Portal Web');
    }
}
