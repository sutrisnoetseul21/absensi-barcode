<?php

namespace App\Livewire\PortalWeb;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use App\Models\WebFaq;

class FaqSetting extends Component
{
    use WithPagination;

    public string $search = '';
    public string $filterKategori = '';
    public bool $showModal = false;
    public bool $showDeleteModal = false;
    public ?string $editingId = null;
    public ?string $deletingId = null;

    public string $kategori = 'SPMB';
    public string $pertanyaan = '';
    public string $jawaban = '';
    public int $urutan = 0;
    public bool $is_active = true;

    protected function rules(): array
    {
        return [
            'kategori'   => 'required|string|max:100',
            'pertanyaan' => 'required|string|max:500',
            'jawaban'    => 'required|string|max:5000',
            'urutan'     => 'integer|min:0',
            'is_active'  => 'boolean',
        ];
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedFilterKategori(): void
    {
        $this->resetPage();
    }

    public function openCreate(): void
    {
        $this->resetForm();
        $this->urutan = (WebFaq::max('urutan') ?? 0) + 1;
        $this->showModal = true;
    }

    public function openEdit(string $id): void
    {
        $item = WebFaq::findOrFail($id);
        $this->editingId   = $item->id;
        $this->kategori    = $item->kategori ?? 'Umum';
        $this->pertanyaan  = $item->pertanyaan;
        $this->jawaban     = $item->jawaban;
        $this->urutan      = $item->urutan ?? 0;
        $this->is_active   = (bool) $item->is_active;
        $this->showModal   = true;
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'kategori'   => trim($this->kategori),
            'pertanyaan' => trim($this->pertanyaan),
            'jawaban'    => trim($this->jawaban),
            'urutan'     => $this->urutan,
            'is_active'  => $this->is_active,
        ];

        if ($this->editingId) {
            WebFaq::findOrFail($this->editingId)->update($data);
            session()->flash('success', 'FAQ berhasil diperbarui.');
        } else {
            WebFaq::create($data);
            session()->flash('success', 'FAQ baru berhasil ditambahkan.');
        }

        $this->showModal = false;
        $this->resetForm();
    }

    public function toggleStatus(string $id): void
    {
        $item = WebFaq::findOrFail($id);
        $item->update(['is_active' => !$item->is_active]);
        session()->flash('success', 'Status FAQ berhasil diubah.');
    }

    public function confirmDelete(string $id): void
    {
        $this->deletingId = $id;
        $this->showDeleteModal = true;
    }

    public function delete(): void
    {
        if ($this->deletingId) {
            WebFaq::findOrFail($this->deletingId)->delete();
            session()->flash('success', 'FAQ berhasil dihapus.');
        }
        $this->showDeleteModal = false;
        $this->deletingId = null;
    }

    private function resetForm(): void
    {
        $this->editingId   = null;
        $this->kategori    = 'SPMB';
        $this->pertanyaan  = '';
        $this->jawaban     = '';
        $this->urutan      = 0;
        $this->is_active   = true;
    }

    #[Layout('components.layouts.portal')]
    public function render()
    {
        $faqs = WebFaq::when($this->search, function ($q) {
            $q->where('pertanyaan', 'like', '%' . $this->search . '%')
              ->orWhere('jawaban', 'like', '%' . $this->search . '%')
              ->orWhere('kategori', 'like', '%' . $this->search . '%');
        })
        ->when($this->filterKategori, function ($q) {
            $q->where('kategori', $this->filterKategori);
        })
        ->orderBy('kategori')
        ->orderBy('urutan')
        ->paginate(15);

        $categories = WebFaq::select('kategori')
            ->whereNotNull('kategori')
            ->distinct()
            ->pluck('kategori')
            ->values();

        $defaultCategories = WebFaq::defaultCategories();

        return view('livewire.portal-web.faq-setting', compact('faqs', 'categories', 'defaultCategories'))
            ->title('Kelola FAQ Sekolah — Portal Web');
    }
}
