<?php

namespace App\Livewire\PortalWeb;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use App\Models\WebMicrosite;
use App\Models\Guru;

class Microsite extends Component
{
    use WithPagination;

    public string $activeTab = 'sekolah'; // 'sekolah' or 'guru'

    // ==========================================
    // TAB 1: MICROSITE SEKOLAH
    // ==========================================
    public string $searchSekolah = '';
    public bool $showModalSekolah = false;
    public bool $showDeleteModalSekolah = false;
    public ?int $editingSekolahId = null;
    public ?int $deletingSekolahId = null;

    public string $judul = '';
    public string $deskripsi = '';
    public string $url = '';
    public string $kategori = 'Utama';
    public string $icon = 'fas fa-globe';
    public string $button_text = 'Kunjungi Microsite';
    public int $urutan = 0;
    public bool $is_active = true;

    // ==========================================
    // TAB 2: MICROSITE GURU
    // ==========================================
    public string $searchGuru = '';
    public string $filterStatusGuru = 'semua'; // 'semua', 'ada_link', 'belum_ada'
    public bool $showModalGuru = false;
    public ?string $editingGuruId = null;
    public string $editingGuruNama = '';
    public string $editingGuruNip = '';
    public string $editingGuruMicrositeUrl = '';

    protected function rulesSekolah(): array
    {
        return [
            'judul'       => 'required|string|max:255',
            'url'         => 'required|url|max:500',
            'deskripsi'   => 'nullable|string|max:1000',
            'kategori'    => 'nullable|string|max:100',
            'icon'        => 'nullable|string|max:100',
            'button_text' => 'required|string|max:100',
            'urutan'      => 'integer|min:0',
            'is_active'   => 'boolean',
        ];
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
        $this->resetPage('sekolahPage');
        $this->resetPage('guruPage');
    }

    public function updatedSearchSekolah(): void
    {
        $this->resetPage('sekolahPage');
    }

    public function updatedSearchGuru(): void
    {
        $this->resetPage('guruPage');
    }

    public function updatedFilterStatusGuru(): void
    {
        $this->resetPage('guruPage');
    }

    // --- Actions: Microsite Sekolah ---
    public function openCreateSekolah(): void
    {
        $this->resetFormSekolah();
        $this->showModalSekolah = true;
    }

    public function openEditSekolah(int $id): void
    {
        $item = WebMicrosite::findOrFail($id);
        $this->editingSekolahId = $item->id;
        $this->judul       = $item->judul;
        $this->url         = $item->url;
        $this->deskripsi   = $item->deskripsi ?? '';
        $this->kategori    = $item->kategori ?? 'Utama';
        $this->icon        = $item->icon ?? 'fas fa-globe';
        $this->button_text = $item->button_text ?? 'Kunjungi Microsite';
        $this->urutan      = $item->urutan ?? 0;
        $this->is_active   = (bool) $item->is_active;
        $this->showModalSekolah = true;
    }

    public function saveSekolah(): void
    {
        $this->validate($this->rulesSekolah(), [
            'judul.required'       => 'Judul microsite wajib diisi.',
            'url.required'         => 'Tautan URL wajib diisi.',
            'url.url'              => 'Format URL tidak valid (diawali http:// atau https://).',
            'button_text.required' => 'Teks tombol wajib diisi.',
        ]);

        $data = [
            'judul'       => $this->judul,
            'url'         => $this->url,
            'deskripsi'   => $this->deskripsi,
            'kategori'    => $this->kategori ?: 'Utama',
            'icon'        => $this->icon ?: 'fas fa-globe',
            'button_text' => $this->button_text ?: 'Kunjungi Microsite',
            'urutan'      => $this->urutan,
            'is_active'   => $this->is_active,
        ];

        if ($this->editingSekolahId) {
            WebMicrosite::findOrFail($this->editingSekolahId)->update($data);
            session()->flash('success_sekolah', 'Microsite sekolah berhasil diperbarui.');
        } else {
            WebMicrosite::create($data);
            session()->flash('success_sekolah', 'Microsite sekolah baru berhasil ditambahkan.');
        }

        $this->showModalSekolah = false;
        $this->resetFormSekolah();
    }

    public function toggleActiveSekolah(int $id): void
    {
        $item = WebMicrosite::findOrFail($id);
        $item->update(['is_active' => !$item->is_active]);
        session()->flash('success_sekolah', 'Status microsite berhasil diperbarui.');
    }

    public function confirmDeleteSekolah(int $id): void
    {
        $this->deletingSekolahId = $id;
        $this->showDeleteModalSekolah = true;
    }

    public function deleteSekolah(): void
    {
        if ($this->deletingSekolahId) {
            WebMicrosite::findOrFail($this->deletingSekolahId)->delete();
            session()->flash('success_sekolah', 'Microsite sekolah berhasil dihapus.');
        }
        $this->showDeleteModalSekolah = false;
        $this->deletingSekolahId = null;
    }

    private function resetFormSekolah(): void
    {
        $this->editingSekolahId = null;
        $this->judul       = '';
        $this->deskripsi   = '';
        $this->url         = '';
        $this->kategori    = 'Utama';
        $this->icon        = 'fas fa-globe';
        $this->button_text = 'Kunjungi Microsite';
        $this->urutan      = 0;
        $this->is_active   = true;
        $this->resetValidation();
    }

    // --- Actions: Microsite Guru ---
    public function openEditGuru(string $id): void
    {
        $guru = Guru::findOrFail($id);
        $this->editingGuruId = $guru->id;
        $this->editingGuruNama = $guru->name;
        $this->editingGuruNip = $guru->nip ?: '-';
        $this->editingGuruMicrositeUrl = $guru->microsite_url ?? '';
        $this->showModalGuru = true;
    }

    public function saveGuruMicrosite(): void
    {
        $this->validate([
            'editingGuruMicrositeUrl' => 'nullable|url|max:500',
        ], [
            'editingGuruMicrositeUrl.url' => 'Format URL tidak valid (diawali http:// atau https://).',
            'editingGuruMicrositeUrl.max' => 'URL maksimal 500 karakter.',
        ]);

        if ($this->editingGuruId) {
            $guru = Guru::findOrFail($this->editingGuruId);
            $guru->update([
                'microsite_url' => $this->editingGuruMicrositeUrl ?: null,
            ]);
            session()->flash('success_guru', "Tautan microsite untuk {$guru->name} berhasil diperbarui.");
        }

        $this->showModalGuru = false;
        $this->editingGuruId = null;
        $this->editingGuruMicrositeUrl = '';
    }

    public function clearGuruMicrosite(string $id): void
    {
        $guru = Guru::findOrFail($id);
        $guru->update(['microsite_url' => null]);
        session()->flash('success_guru', "Tautan microsite untuk {$guru->name} berhasil dikosongkan.");
    }

    #[Layout('components.layouts.portal')]
    public function render()
    {
        // Query Sekolah
        $sekolahQuery = WebMicrosite::query();
        if ($this->searchSekolah) {
            $sekolahQuery->where(function($q) {
                $q->where('judul', 'like', '%' . $this->searchSekolah . '%')
                  ->orWhere('kategori', 'like', '%' . $this->searchSekolah . '%')
                  ->orWhere('deskripsi', 'like', '%' . $this->searchSekolah . '%');
            });
        }
        $micrositesSekolah = $sekolahQuery->orderBy('urutan')->orderBy('created_at', 'desc')->paginate(10, ['*'], 'sekolahPage');

        // Query Guru
        $guruQuery = Guru::query()->with(['jabatans', 'pengajarans.mataPelajaran', 'kelasAjarans.kelas']);
        if ($this->searchGuru) {
            $guruQuery->where(function ($q) {
                $q->where('name', 'like', '%' . $this->searchGuru . '%')
                  ->orWhere('nip', 'like', '%' . $this->searchGuru . '%');
            });
        }
        if ($this->filterStatusGuru === 'ada_link') {
            $guruQuery->whereNotNull('microsite_url')->where('microsite_url', '!=', '');
        } elseif ($this->filterStatusGuru === 'belum_ada') {
            $guruQuery->where(function ($q) {
                $q->whereNull('microsite_url')->orWhere('microsite_url', '');
            });
        }
        $gurus = $guruQuery->orderBy('name')->paginate(10, ['*'], 'guruPage');

        $totalSekolah = WebMicrosite::count();
        $totalGuruAdaLink = Guru::whereNotNull('microsite_url')->where('microsite_url', '!=', '')->count();
        $totalGuru = Guru::count();

        return view('livewire.portal-web.microsite', [
            'micrositesSekolah' => $micrositesSekolah,
            'gurus'             => $gurus,
            'totalSekolah'      => $totalSekolah,
            'totalGuruAdaLink'  => $totalGuruAdaLink,
            'totalGuru'         => $totalGuru,
        ])->title('Kelola Microsite — Portal Web');
    }
}
