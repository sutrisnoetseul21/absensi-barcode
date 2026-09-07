<?php

namespace App\Livewire\PortalWeb;

use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Layout;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use App\Models\WebHalamanAkademik;

class HalamanAkademik extends Component
{
    use WithFileUploads;

    public string $search = '';
    public bool $isForm = false;
    public bool $showDeleteModal = false;
    
    public ?int $editingId = null;
    public ?int $deletingId = null;

    public string $judul = '';
    public string $slug = '';
    public ?string $kategori = '';
    public ?string $deskripsi_singkat = '';
    public $file_pdf; // Can be string (existing path) or UploadedFile
    public string $konten = '';
    public int $urutan = 0;
    public bool $is_active = true;

    protected function rules(): array
    {
        return [
            'judul' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:web_halaman_akademiks,slug,' . $this->editingId,
            'kategori' => 'nullable|string|max:255',
            'deskripsi_singkat' => 'nullable|string',
            'file_pdf' => 'nullable',
            'konten' => 'nullable|string',
            'urutan' => 'required|integer|min:0',
            'is_active' => 'boolean',
        ];
    }

    public function updatedJudul(): void
    {
        if (!$this->editingId) {
            $this->slug = Str::slug($this->judul);
        }
    }

    public function openCreate(): void 
    { 
        $this->resetForm(); 
        $this->isForm = true; 
    }

    public function openEdit(int $id): void
    {
        $halaman = WebHalamanAkademik::findOrFail($id);
        $this->editingId = $halaman->id;
        $this->judul = $halaman->judul;
        $this->slug = $halaman->slug;
        $this->kategori = $halaman->kategori ?? '';
        $this->deskripsi_singkat = $halaman->deskripsi_singkat ?? '';
        $this->file_pdf = $halaman->file_pdf;
        $this->konten = $halaman->konten ?? '';
        $this->urutan = $halaman->urutan ?? 0;
        $this->is_active = (bool) $halaman->is_active;
        
        $this->isForm = true;
    }

    public function save(): void
    {
        $this->slug = Str::slug($this->slug);
        
        $this->validate();

        $data = [
            'judul' => $this->judul,
            'slug' => $this->slug,
            'kategori' => $this->kategori,
            'deskripsi_singkat' => $this->deskripsi_singkat,
            'konten' => $this->konten,
            'urutan' => $this->urutan,
            'is_active' => $this->is_active,
        ];

        // Handle File Upload Validation & Storage
        if (is_object($this->file_pdf)) {
            $this->validate([
                'file_pdf' => 'mimes:pdf|max:10240', // max 10MB
            ], [
                'file_pdf.mimes' => 'Berkas harus berupa PDF.',
                'file_pdf.max' => 'Ukuran berkas PDF maksimal 10 MB.',
            ]);

            // Delete old file if updating
            if ($this->editingId) {
                $oldHalaman = WebHalamanAkademik::find($this->editingId);
                if ($oldHalaman && $oldHalaman->file_pdf && Storage::disk('public')->exists($oldHalaman->file_pdf)) {
                    Storage::disk('public')->delete($oldHalaman->file_pdf);
                }
            }

            $path = $this->file_pdf->store('akademik-dokumen', 'public');
            $data['file_pdf'] = $path;
        }

        if ($this->editingId) {
            WebHalamanAkademik::findOrFail($this->editingId)->update($data);
            session()->flash('success', 'Halaman akademik berhasil diperbarui.');
        } else {
            WebHalamanAkademik::create($data);
            session()->flash('success', 'Halaman akademik berhasil ditambahkan.');
        }

        $this->resetForm();
        $this->isForm = false;
    }

    public function confirmDelete(int $id): void
    {
        $this->deletingId = $id;
        $this->showDeleteModal = true;
    }

    public function delete(): void
    {
        if ($this->deletingId) {
            $halaman = WebHalamanAkademik::findOrFail($this->deletingId);
            if ($halaman->file_pdf && Storage::disk('public')->exists($halaman->file_pdf)) {
                Storage::disk('public')->delete($halaman->file_pdf);
            }
            $halaman->delete();
            session()->flash('success', 'Halaman akademik berhasil dihapus.');
        }
        
        $this->showDeleteModal = false;
        $this->deletingId = null;
    }

    public function resetForm(): void
    {
        $this->reset([
            'editingId',
            'judul',
            'slug',
            'kategori',
            'deskripsi_singkat',
            'file_pdf',
            'konten',
            'urutan',
            'is_active',
        ]);
        $this->urutan = (WebHalamanAkademik::max('urutan') ?? 0) + 1;
        $this->is_active = true;
        $this->resetValidation();
    }

    #[Layout('components.layouts.portal')]
    public function render()
    {
        $halamans = WebHalamanAkademik::when($this->search, function($q) {
                $q->where('judul', 'like', '%' . $this->search . '%')
                  ->orWhere('kategori', 'like', '%' . $this->search . '%')
                  ->orWhere('deskripsi_singkat', 'like', '%' . $this->search . '%');
            })
            ->orderBy('urutan')
            ->orderBy('id', 'desc')
            ->get();

        return view('livewire.portal-web.halaman-akademik', compact('halamans'))
            ->title('Halaman Akademik — Portal Web');
    }
}
