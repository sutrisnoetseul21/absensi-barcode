<?php

namespace App\Livewire\PortalWeb;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\WebGaleri;

class Galeri extends Component
{
    use WithPagination, WithFileUploads;

    // Filter & Search
    public string $search = '';
    public string $sortBy = 'urutan'; // 'urutan', 'latest', 'oldest'

    // Modals
    public bool $showModal = false;
    public bool $showDeleteModal = false;
    public bool $showBatchDeleteModal = false;
    public bool $showPreviewModal = false;

    // Preview lightbox
    public ?string $previewFotoUrl = null;
    public ?string $previewFotoJudul = null;
    public ?string $previewFotoKeterangan = null;

    // Single Edit / Delete ID
    public ?string $editingId = null;
    public ?string $deletingId = null;

    // Multi-Selection
    public array $selectedIds = [];
    public bool $selectAll = false;

    // Form Mode: 'batch' (multi-upload) or 'single'
    public string $uploadMode = 'batch';

    // Single Form fields
    public string $judul = '';
    public string $keterangan = '';
    public int $urutan = 0;
    public $foto = null;
    public ?string $existingFoto = null;

    // Batch Form fields
    public $batchFotos = [];
    public string $batchJudul = '';
    public string $batchKeterangan = '';
    public string $batchNaming = 'numbered'; // 'numbered', 'original', 'same'
    public int $batchUrutanAwal = 0;

    public function updatedSearch(): void
    {
        $this->resetPage();
        $this->selectedIds = [];
        $this->selectAll = false;
    }

    public function updatedSortBy(): void
    {
        $this->resetPage();
    }

    public function updatedSelectAll(bool $value): void
    {
        if ($value) {
            $this->selectedIds = $this->getCurrentPageIds();
        } else {
            $this->selectedIds = [];
        }
    }

    private function getCurrentPageIds(): array
    {
        $query = WebGaleri::query();
        if ($this->search) {
            $query->where('judul', 'like', '%' . $this->search . '%')
                  ->orWhere('keterangan', 'like', '%' . $this->search . '%');
        }
        return $query->pluck('id')->map(fn($id) => (string) $id)->toArray();
    }

    public function openCreate(string $mode = 'batch'): void
    {
        $this->resetForm();
        $this->uploadMode = $mode;
        $this->showModal = true;
    }

    public function openEdit(string $id): void
    {
        $item = WebGaleri::findOrFail($id);
        $this->resetForm();
        $this->uploadMode   = 'single';
        $this->editingId    = $item->id;
        $this->judul        = $item->judul ?? '';
        $this->keterangan   = $item->keterangan ?? '';
        $this->urutan       = $item->urutan ?? 0;
        $this->existingFoto = $item->foto_path;
        $this->foto         = null;
        $this->showModal    = true;
    }

    public function removeBatchFoto(int $index): void
    {
        if (isset($this->batchFotos[$index])) {
            unset($this->batchFotos[$index]);
            $this->batchFotos = array_values($this->batchFotos);
        }
    }

    public function save(): void
    {
        if ($this->uploadMode === 'single' || $this->editingId) {
            $this->saveSingle();
        } else {
            $this->saveBatch();
        }
    }

    private function saveSingle(): void
    {
        $this->validate([
            'judul'      => 'required|string|max:255',
            'keterangan' => 'nullable|string|max:1000',
            'urutan'     => 'integer|min:0',
            'foto'       => $this->editingId ? 'nullable|image|max:10240' : 'required|image|max:10240',
        ], [
            'judul.required' => 'Judul foto wajib diisi.',
            'foto.required'  => 'Pilih berkas foto terlebih dahulu.',
            'foto.image'     => 'Berkas harus berupa gambar (jpg, png, webp).',
            'foto.max'       => 'Ukuran foto maksimal 10MB.',
        ]);

        $fotoPath = $this->existingFoto;
        if ($this->foto) {
            $fotoPath = $this->foto->store('web-profil/galeri', 'public');
        }

        $data = [
            'judul'      => $this->judul,
            'keterangan' => $this->keterangan ?: null,
            'urutan'     => $this->urutan,
            'foto_path'  => $fotoPath,
        ];

        if ($this->editingId) {
            WebGaleri::findOrFail($this->editingId)->update($data);
            session()->flash('success', 'Foto galeri berhasil diperbarui.');
        } else {
            WebGaleri::create($data);
            session()->flash('success', 'Foto galeri baru berhasil ditambahkan.');
        }

        $this->showModal = false;
        $this->resetForm();
    }

    private function saveBatch(): void
    {
        $this->validate([
            'batchFotos'       => 'required|array|min:1',
            'batchFotos.*'     => 'image|max:10240',
            'batchJudul'       => 'required|string|max:255',
            'batchKeterangan'  => 'nullable|string|max:1000',
            'batchUrutanAwal'  => 'integer|min:0',
        ], [
            'batchFotos.required' => 'Pilih minimal satu foto untuk diunggah.',
            'batchFotos.min'      => 'Pilih minimal satu foto untuk diunggah.',
            'batchFotos.*.image'  => 'Setiap berkas harus berupa gambar valid.',
            'batchFotos.*.max'    => 'Ukuran masing-masing foto maksimal 10MB.',
            'batchJudul.required' => 'Judul utama / nama kegiatan wajib diisi.',
        ]);

        $count = count($this->batchFotos);
        $currentUrutan = $this->batchUrutanAwal;

        foreach ($this->batchFotos as $index => $uploadedFile) {
            $fotoPath = $uploadedFile->store('web-profil/galeri', 'public');

            // Format judul sesuai opsi
            if ($this->batchNaming === 'numbered') {
                $finalJudul = $count > 1 ? ($this->batchJudul . ' #' . ($index + 1)) : $this->batchJudul;
            } elseif ($this->batchNaming === 'original') {
                $originalName = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
                $cleanName = Str::title(str_replace(['_', '-'], ' ', $originalName));
                $finalJudul = $this->batchJudul . ' - ' . $cleanName;
            } else {
                $finalJudul = $this->batchJudul;
            }

            WebGaleri::create([
                'judul'      => $finalJudul,
                'keterangan' => $this->batchKeterangan ?: null,
                'urutan'     => $currentUrutan++,
                'foto_path'  => $fotoPath,
            ]);
        }

        $this->showModal = false;
        $this->resetForm();
        session()->flash('success', "Berhasil menambahkan {$count} foto baru ke galeri dokumentasi!");
    }

    public function openPreview(string $id): void
    {
        $item = WebGaleri::findOrFail($id);
        $this->previewFotoUrl = asset('storage/' . $item->foto_path);
        $this->previewFotoJudul = $item->judul;
        $this->previewFotoKeterangan = $item->keterangan;
        $this->showPreviewModal = true;
    }

    public function confirmDelete(string $id): void
    {
        $this->deletingId = $id;
        $this->showDeleteModal = true;
    }

    public function delete(): void
    {
        if ($this->deletingId) {
            $item = WebGaleri::findOrFail($this->deletingId);
            $item->delete();
            session()->flash('success', 'Foto berhasil dihapus.');
        }
        $this->showDeleteModal = false;
        $this->deletingId = null;
    }

    public function confirmBatchDelete(): void
    {
        if (!empty($this->selectedIds)) {
            $this->showBatchDeleteModal = true;
        }
    }

    public function deleteBatch(): void
    {
        if (!empty($this->selectedIds)) {
            $count = count($this->selectedIds);
            WebGaleri::whereIn('id', $this->selectedIds)->delete();
            $this->selectedIds = [];
            $this->selectAll = false;
            session()->flash('success', "Berhasil menghapus {$count} foto yang dipilih.");
        }
        $this->showBatchDeleteModal = false;
    }

    private function resetForm(): void
    {
        $this->editingId       = null;
        $this->judul           = '';
        $this->keterangan      = '';
        $this->urutan          = 0;
        $this->foto            = null;
        $this->existingFoto    = null;

        $this->batchFotos      = [];
        $this->batchJudul      = '';
        $this->batchKeterangan = '';
        $this->batchNaming     = 'numbered';
        $this->batchUrutanAwal = 0;

        $this->resetValidation();
    }

    #[Layout('components.layouts.portal')]
    public function render()
    {
        $query = WebGaleri::query();

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('judul', 'like', '%' . $this->search . '%')
                  ->orWhere('keterangan', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->sortBy === 'latest') {
            $query->latest('created_at');
        } elseif ($this->sortBy === 'oldest') {
            $query->oldest('created_at');
        } else {
            $query->orderBy('urutan')->orderByDesc('created_at');
        }

        $galeris = $query->paginate(24);
        $totalGaleri = WebGaleri::count();

        return view('livewire.portal-web.galeri', [
            'galeris'     => $galeris,
            'totalGaleri' => $totalGaleri,
        ])->title('Galeri Foto — Portal Web');
    }
}
