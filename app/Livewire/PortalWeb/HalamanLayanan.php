<?php

namespace App\Livewire\PortalWeb;

use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Layout;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use App\Models\WebHalamanLayanan;

class HalamanLayanan extends Component
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
    public string $biaya = 'Gratis (Rp 0)';
    public string $waktu_layanan = '15 - 30 Menit';
    public string $konten = '';
    public int $urutan = 0;
    public bool $is_active = true;

    protected function rules(): array
    {
        return [
            'judul' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:web_halaman_layanans,slug,' . $this->editingId,
            'kategori' => 'nullable|string|max:255',
            'deskripsi_singkat' => 'nullable|string',
            'file_pdf' => 'nullable',
            'biaya' => 'nullable|string|max:255',
            'waktu_layanan' => 'nullable|string|max:255',
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
        $halaman = WebHalamanLayanan::findOrFail($id);
        $this->editingId = $halaman->id;
        $this->judul = $halaman->judul;
        $this->slug = $halaman->slug;
        $this->kategori = $halaman->kategori ?? '';
        $this->deskripsi_singkat = $halaman->deskripsi_singkat ?? '';
        $this->file_pdf = $halaman->file_pdf;
        $this->biaya = $halaman->biaya ?? 'Gratis (Rp 0)';
        $this->waktu_layanan = $halaman->waktu_layanan ?? '15 - 30 Menit';
        $this->konten = $halaman->konten ?? '';
        $this->urutan = $halaman->urutan;
        $this->is_active = $halaman->is_active;
        
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
            'biaya' => $this->biaya,
            'waktu_layanan' => $this->waktu_layanan,
            'konten' => $this->konten,
            'urutan' => $this->urutan,
            'is_active' => $this->is_active,
        ];

        // Handle File Upload Validation
        if (is_object($this->file_pdf)) {
            $this->validate([
                'file_pdf' => 'file|mimes:pdf|max:10240'
            ]);
        }

        // Handle File Upload
        if (is_object($this->file_pdf)) {
            // Delete old file if updating
            if ($this->editingId) {
                $oldHalaman = WebHalamanLayanan::find($this->editingId);
                if ($oldHalaman && $oldHalaman->file_pdf) {
                    Storage::disk('public')->delete($oldHalaman->file_pdf);
                }
            }
            $data['file_pdf'] = $this->file_pdf->store('layanan-pdf', 'public');
        }

        if ($this->editingId) {
            WebHalamanLayanan::findOrFail($this->editingId)->update($data);
            session()->flash('success', 'Halaman layanan berhasil diperbarui.');
        } else {
            WebHalamanLayanan::create($data);
            session()->flash('success', 'Halaman layanan berhasil ditambahkan.');
        }

        $this->isForm = false;
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
            $halaman = WebHalamanLayanan::findOrFail($this->deletingId);
            if ($halaman->file_pdf) {
                Storage::disk('public')->delete($halaman->file_pdf);
            }
            $halaman->delete();
            session()->flash('success', 'Halaman layanan berhasil dihapus.');
        }
        $this->showDeleteModal = false;
        $this->deletingId = null;
    }

    private function resetForm(): void
    {
        $this->editingId = null; 
        $this->judul = ''; 
        $this->slug = '';
        $this->kategori = '';
        $this->deskripsi_singkat = '';
        $this->file_pdf = null;
        $this->biaya = 'Gratis (Rp 0)';
        $this->waktu_layanan = '15 - 30 Menit';
        $this->konten = '';
        $this->urutan = 0;
        $this->is_active = true;
    }

    #[Layout('components.layouts.portal')]
    public function render()
    {
        $halamans = WebHalamanLayanan::when($this->search, function($q) {
                $q->where('judul', 'like', '%' . $this->search . '%')
                  ->orWhere('slug', 'like', '%' . $this->search . '%');
            })
            ->orderBy('urutan')
            ->get();

        return view('livewire.portal-web.halaman-layanan', compact('halamans'))
            ->title('Halaman Layanan — Portal Web');
    }
}
