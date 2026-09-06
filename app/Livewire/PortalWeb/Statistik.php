<?php

namespace App\Livewire\PortalWeb;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\WebStatistic;

class Statistik extends Component
{
    public string $search = '';
    public bool $showModal = false;
    public bool $showDeleteModal = false;
    public ?int $editingId = null;
    public ?int $deletingId = null;

    public string $icon = 'fas fa-chart-bar';
    public string $value = '';
    public string $label = '';
    public int $order = 0;

    protected function rules(): array
    {
        return [
            'icon'  => 'required|string|max:100',
            'value' => 'required|string|max:100',
            'label' => 'required|string|max:255',
            'order' => 'required|integer|min:0',
        ];
    }

    public function openCreate(): void
    {
        $this->resetForm();
        $this->order = (WebStatistic::max('order') ?? 0) + 1;
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $item = WebStatistic::findOrFail($id);
        $this->editingId = $item->id;
        $this->icon      = $item->icon;
        $this->value     = $item->value;
        $this->label     = $item->label;
        $this->order     = (int) $item->order;
        $this->showModal = true;
    }

    public function save(): void
    {
        $data = $this->validate();

        if ($this->editingId) {
            WebStatistic::findOrFail($this->editingId)->update($data);
            session()->flash('success', 'Statistik web berhasil diperbarui.');
        } else {
            WebStatistic::create($data);
            session()->flash('success', 'Statistik web berhasil ditambahkan.');
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
            WebStatistic::findOrFail($this->deletingId)->delete();
            session()->flash('success', 'Statistik berhasil dihapus.');
        }
        $this->showDeleteModal = false;
        $this->deletingId = null;
    }

    private function resetForm(): void
    {
        $this->editingId = null;
        $this->icon      = 'fas fa-chart-bar';
        $this->value     = '';
        $this->label     = '';
        $this->order     = 0;
    }

    #[Layout('components.layouts.portal')]
    public function render()
    {
        $statistics = WebStatistic::when($this->search, function ($q) {
                $q->where('label', 'like', '%' . $this->search . '%')
                  ->orWhere('value', 'like', '%' . $this->search . '%');
            })
            ->orderBy('order')
            ->get();

        return view('livewire.portal-web.statistik', compact('statistics'))
            ->title('Statistik & Info Web — Portal Web');
    }
}
