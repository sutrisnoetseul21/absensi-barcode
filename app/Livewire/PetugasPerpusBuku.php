<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Livewire\Attributes\Layout;
use App\Models\Buku;
use App\Models\KategoriBuku;
use App\Models\KlasifikasiDdc;
use App\Models\InventarisBuku;
use App\Models\EksemplarBuku;
use App\Models\MataPelajaran;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\PengaturanSekolah;

#[Layout('components.layouts.portal')]
class PetugasPerpusBuku extends Component
{
    use WithPagination, WithFileUploads;

    // Form Input Buku Baru
    public $showInputModal = false;
    public $judul = '';
    public $penulis = '';
    public $penerbit = '';
    public $tahun_terbit = '';
    public $isbn = '';
    public $lokasi_rak = '';
    public $kategori_id = '';
    public $mapel_id = 'lainnya';
    public $klasifikasi_ddc_id = null;
    public $jumlah_eksemplar = null;
    public $grade_level = '';
    public $sampul_buku;
    public $file_pdf;
    
    public $asal_buku = '';
    public $harga_buku = null;
    public $prefix_kode = '';

    // Form Edit Buku
    public bool $showEditBukuModal = false;
    public ?string $editingBukuId = null;
    public string $edit_judul = '';
    public string $edit_penulis = '';
    public string $edit_penerbit = '';
    public string $edit_tahun_terbit = '';
    public string $edit_isbn = '';
    public string $edit_lokasi_rak = '';
    public string $edit_kategori_id = '';
    public string $edit_mapel_id = 'lainnya';
    public $edit_klasifikasi_ddc_id = null;
    public string $edit_grade_level = '';
    public $edit_sampul_buku;
    public ?string $edit_existing_sampul = null;
    public $edit_file_pdf;
    public ?string $edit_existing_pdf = null;

    // Modal Unduh Katalog
    public bool $showUnduhModal = false;
    public array $filterKategoriUnduh = [];
    public array $filterMapelUnduh = [];
    public string $formatUnduh = 'pdf';

    // Modal Detail Eksemplar Buku
    public bool $showDetailEksemplarModal = false;
    public ?string $selectedBukuIdForDetail = null;
    public string $searchEksemplar = '';

    // Modal Edit Eksemplar Buku
    public bool $showEditEksemplarModal = false;
    public ?string $editingEksemplarId = null;
    public string $editingEksemplarKode = '';
    public string $editingEksemplarStatus = 'tersedia';
    public string $editingEksemplarKondisiFisik = 'baik';

    // Search & Filter
    public $search = '';
    public $filterKategori = '';

    protected $rules = [
        'judul' => 'required|string|max:255',
        'kategori_id' => 'required|exists:kategori_bukus,id',
        'mapel_id' => 'nullable',
        'penulis' => 'nullable|string|max:255',
        'penerbit' => 'nullable|string|max:255',
        'tahun_terbit' => 'nullable|integer|digits:4',
        'isbn' => 'nullable|string|max:50',
        'lokasi_rak' => 'nullable|string|max:100',
        'jumlah_eksemplar' => 'required|integer|min:1|max:200',
        'grade_level' => 'nullable',
        'asal_buku' => 'required|in:Pembelian,Hibah,Tukar,Terbitan Sendiri',
        'harga_buku' => 'nullable|numeric|min:0',
        'prefix_kode' => 'required|string|max:10',
        'sampul_buku' => 'nullable|image|max:2048',
    ];

    public function mount()
    {
        $this->tahun_terbit = date('Y');
    }

    public function updatedKategoriId($value)
    {
        if ($value) {
            $kategori = KategoriBuku::find($value);
            if (! $kategori || strtolower(trim($kategori->nama_kategori)) !== 'non fiksi') {
                $this->mapel_id = 'lainnya';
            }
        } else {
            $this->mapel_id = 'lainnya';
        }
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedFilterKategori()
    {
        $this->resetPage();
    }

    public function openUnduhModal(): void
    {
        $this->filterKategoriUnduh = [];
        $this->filterMapelUnduh    = [];
        $this->formatUnduh         = 'pdf';
        $this->showUnduhModal      = true;
    }

    public function openDetailEksemplarModal($bukuId): void
    {
        $this->selectedBukuIdForDetail = $bukuId;
        $this->searchEksemplar = '';
        $this->showDetailEksemplarModal = true;
    }

    public function openEditEksemplarModal($eksemplarId): void
    {
        $eksemplar = EksemplarBuku::find($eksemplarId);
        if (! $eksemplar) return;

        $this->editingEksemplarId = $eksemplar->id;
        $this->editingEksemplarKode = $eksemplar->kode_eksemplar;
        $this->editingEksemplarStatus = $eksemplar->status;
        $this->editingEksemplarKondisiFisik = $eksemplar->kondisi_fisik;
        $this->showEditEksemplarModal = true;
    }

    public function simpanEditEksemplar(): void
    {
        if (! $this->editingEksemplarId) return;

        $this->validate([
            'editingEksemplarStatus' => 'required|in:tersedia,dipinjam,rusak,hilang',
            'editingEksemplarKondisiFisik' => 'required|in:baik,rusak_ringan,rusak_berat',
        ]);

        $eksemplar = EksemplarBuku::find($this->editingEksemplarId);
        if ($eksemplar) {
            $eksemplar->update([
                'status' => $this->editingEksemplarStatus,
                'kondisi_fisik' => $this->editingEksemplarKondisiFisik,
            ]);
        }

        $this->showEditEksemplarModal = false;
        session()->flash('message', 'Data eksemplar ' . $this->editingEksemplarKode . ' berhasil diperbarui!');
    }

    public function downloadKatalog(): void
    {
        $routeName = $this->formatUnduh === 'excel'
            ? 'perpustakaan.katalog-buku.excel'
            : 'perpustakaan.katalog-buku.pdf';

        $params = [];
        if (!empty($this->filterKategoriUnduh)) {
            $params['kategori_ids'] = $this->filterKategoriUnduh;
        }
        if (!empty($this->filterMapelUnduh)) {
            $params['mapel_ids'] = $this->filterMapelUnduh;
        }

        $this->showUnduhModal = false;
        $this->redirect(route($routeName, $params));
    }

    public function openInputModal()
    {
        $this->reset(['judul', 'penulis', 'penerbit', 'isbn', 'lokasi_rak', 'klasifikasi_ddc_id', 'harga_buku', 'prefix_kode', 'sampul_buku']);
        $this->kategori_id = '';
        $this->asal_buku = '';
        $this->grade_level = '';
        $this->tahun_terbit = date('Y');
        $this->jumlah_eksemplar = null;
        $this->mapel_id = 'lainnya';
        $this->showInputModal = true;
    }

    public function simpanBuku()
    {
        $this->validate();

        $sampulPath = null;
        if ($this->sampul_buku) {
            try {
                $imageManager = new \Intervention\Image\ImageManager(new \Intervention\Image\Drivers\Gd\Driver());
                $image = $imageManager->decode($this->sampul_buku->getRealPath());
                $image->scaleDown(width: 800, height: 1000);

                $filename = \Illuminate\Support\Str::random(40) . '.jpg';
                $directoryPath = storage_path('app/public/sampul-buku');
                if (!file_exists($directoryPath)) {
                    mkdir($directoryPath, 0755, true);
                }

                $image->save($directoryPath . '/' . $filename, 75);
                $sampulPath = 'sampul-buku/' . $filename;
            } catch (\Throwable $e) {
                $sampulPath = $this->sampul_buku->store('sampul-buku', 'public');
            }
        }

        DB::transaction(function () use ($sampulPath) {
            // 1. Simpan Buku Katalog
            $buku = Buku::create([
                'judul' => $this->judul,
                'kategori_id' => $this->kategori_id,
                'mapel_id' => ($this->mapel_id === 'lainnya' || empty($this->mapel_id)) ? null : $this->mapel_id,
                'klasifikasi_ddc_id' => $this->klasifikasi_ddc_id ?: null,
                'penulis' => $this->penulis,
                'penerbit' => $this->penerbit,
                'tahun_terbit' => $this->tahun_terbit ? (int)$this->tahun_terbit : null,
                'isbn' => $this->isbn,
                'lokasi_rak' => $this->lokasi_rak,
                'grade_level' => $this->grade_level === 'umum' ? null : ($this->grade_level ?: null),
                'sampul_buku' => $sampulPath,
            ]);

            // 2. Buat Record Inventaris Buku Batch
            $inventaris = InventarisBuku::create([
                'buku_id' => $buku->id,
                'no_inventaris' => 'INV-' . date('Ymd-His'),
                'tanggal_masuk' => now()->toDateString(),
                'asal' => $this->asal_buku,
                'harga' => $this->harga_buku ? (int)$this->harga_buku : 0,
                'jumlah_eksemplar' => (int)$this->jumlah_eksemplar,
                'status' => 'aktif',
            ]);

            // 3. Generate Eksemplar Fisik
            $jumlah = (int)$this->jumlah_eksemplar;
            $prefixUpper = strtoupper($this->prefix_kode);
            $generateResult = EksemplarBuku::generateKodeEksemplar($prefixUpper, $jumlah);
            foreach ($generateResult['codes'] as $kodeBarcode) {
                EksemplarBuku::create([
                    'buku_id' => $buku->id,
                    'inventaris_buku_id' => $inventaris->id,
                    'kode_eksemplar' => $kodeBarcode,
                    'status' => 'tersedia',
                    'kondisi_fisik' => 'baik',
                ]);
            }
        });

        $this->showInputModal = false;
        session()->flash('message', 'Buku baru dan eksemplar berhasil ditambahkan!');
    }

    public function updatedEditKategoriId($value)
    {
        if ($value) {
            $kategori = KategoriBuku::find($value);
            if (! $kategori || strtolower(trim($kategori->nama_kategori)) !== 'non fiksi') {
                $this->edit_mapel_id = 'lainnya';
            }
        } else {
            $this->edit_mapel_id = 'lainnya';
        }
    }

    public function openEditBukuModal($bukuId): void
    {
        $buku = Buku::find($bukuId);
        if (! $buku) return;

        $this->editingBukuId = $buku->id;
        $this->edit_judul = $buku->judul;
        $this->edit_kategori_id = (string)$buku->kategori_id;
        $this->edit_mapel_id = $buku->mapel_id ? (string)$buku->mapel_id : 'lainnya';
        $this->edit_klasifikasi_ddc_id = $buku->klasifikasi_ddc_id;
        $this->edit_grade_level = $buku->grade_level ? (string)$buku->grade_level : '';
        $this->edit_penulis = $buku->penulis ?? '';
        $this->edit_penerbit = $buku->penerbit ?? '';
        $this->edit_tahun_terbit = $buku->tahun_terbit ? (string)$buku->tahun_terbit : '';
        $this->edit_isbn = $buku->isbn ?? '';
        $this->edit_lokasi_rak = $buku->lokasi_rak ?? '';
        $this->edit_existing_sampul = $buku->sampul_buku;
        $this->edit_existing_pdf = $buku->file_pdf;
        $this->edit_sampul_buku = null;
        $this->edit_file_pdf = null;

        $this->showEditBukuModal = true;
    }

    public function simpanEditBuku(): void
    {
        if (! $this->editingBukuId) return;

        $this->validate([
            'edit_judul' => 'required|string|max:255',
            'edit_kategori_id' => 'required|exists:kategori_bukus,id',
            'edit_mapel_id' => 'nullable',
            'edit_penulis' => 'nullable|string|max:255',
            'edit_penerbit' => 'nullable|string|max:255',
            'edit_tahun_terbit' => 'nullable|integer|digits:4',
            'edit_isbn' => 'nullable|string|max:50',
            'edit_lokasi_rak' => 'nullable|string|max:100',
            'edit_grade_level' => 'nullable',
            'edit_sampul_buku' => 'nullable|image|max:5120',
            'edit_file_pdf' => 'nullable|mimes:pdf|max:51200',
        ]);

        $buku = Buku::find($this->editingBukuId);
        if (! $buku) return;

        // Process Sampul Baru jika ada
        $sampulPath = $buku->sampul_buku;
        if ($this->edit_sampul_buku) {
            try {
                $imageManager = new \Intervention\Image\ImageManager(new \Intervention\Image\Drivers\Gd\Driver());
                $image = $imageManager->decode($this->edit_sampul_buku->getRealPath());
                $image->scaleDown(width: 800, height: 1000);

                $filename = \Illuminate\Support\Str::random(40) . '.jpg';
                $directoryPath = storage_path('app/public/sampul-buku');
                if (!file_exists($directoryPath)) {
                    mkdir($directoryPath, 0755, true);
                }

                $image->save($directoryPath . '/' . $filename, 75);
                @chmod($directoryPath . '/' . $filename, 0644);
                $sampulPath = 'sampul-buku/' . $filename;
            } catch (\Throwable $e) {
                $sampulPath = $this->edit_sampul_buku->store('sampul-buku', 'public');
                $fullPath = storage_path('app/public/' . $sampulPath);
                if (file_exists($fullPath)) @chmod($fullPath, 0644);
            }
        }

        // Process PDF Baru jika ada
        $pdfPath = $buku->file_pdf;
        if ($this->edit_file_pdf) {
            $pdfPath = $this->edit_file_pdf->store('buku-pdf', 'public');
            $fullPath = storage_path('app/public/' . $pdfPath);
            if (file_exists($fullPath)) @chmod($fullPath, 0644);
        }

        $buku->update([
            'judul' => $this->edit_judul,
            'kategori_id' => $this->edit_kategori_id,
            'mapel_id' => ($this->edit_mapel_id === 'lainnya' || empty($this->edit_mapel_id)) ? null : $this->edit_mapel_id,
            'klasifikasi_ddc_id' => $this->edit_klasifikasi_ddc_id ?: null,
            'penulis' => $this->edit_penulis,
            'penerbit' => $this->edit_penerbit,
            'tahun_terbit' => $this->edit_tahun_terbit ? (int)$this->edit_tahun_terbit : null,
            'isbn' => $this->edit_isbn,
            'lokasi_rak' => $this->edit_lokasi_rak,
            'grade_level' => $this->edit_grade_level === 'umum' ? null : ($this->edit_grade_level ?: null),
            'sampul_buku' => $sampulPath,
            'file_pdf' => $pdfPath,
        ]);

        $this->showEditBukuModal = false;
        session()->flash('message', 'Data buku "' . $buku->judul . '" berhasil diperbarui!');
    }

    public function hapusBuku($bukuId): void
    {
        $buku = Buku::find($bukuId);
        if (! $buku) return;

        // Check if any exemplar is currently borrowed
        if ($buku->eksemplarBukus()->where('status', 'dipinjam')->exists()) {
            session()->flash('error', 'Buku "' . $buku->judul . '" tidak dapat dihapus karena ada eksemplar yang sedang dipinjam!');
            return;
        }

        DB::transaction(function () use ($buku) {
            $buku->eksemplarBukus()->delete();
            InventarisBuku::where('buku_id', $buku->id)->update(['status' => 'dibatalkan']);
            $buku->delete();
        });

        session()->flash('message', 'Buku "' . $buku->judul . '" berhasil dihapus.');
    }

    public function render()
    {
        $kategoriList = KategoriBuku::orderBy('nama_kategori')->get();
        $ddcList = KlasifikasiDdc::orderBy('kode_ddc')->get();
        $mapelList = MataPelajaran::orderBy('nama_mapel')->get();

        // Cari ID kategori Non Fiksi untuk kondisi tampil mapel di modal unduh
        $nonFiksiKategoriId = KategoriBuku::whereRaw('LOWER(TRIM(nama_kategori)) = ?', ['non fiksi'])->value('id');

        $query = Buku::with(['kategoriBuku', 'klasifikasiDdc', 'eksemplarBukus'])
            ->when($this->search, function ($q) {
                $q->where(function ($sub) {
                    $sub->where('judul', 'like', "%{$this->search}%")
                        ->orWhere('penulis', 'like', "%{$this->search}%")
                        ->orWhere('isbn', 'like', "%{$this->search}%")
                        ->orWhereHas('eksemplarBukus', fn ($e) => $e->where('kode_eksemplar', $this->search));
                });
            })
            ->when($this->filterKategori, function ($q) {
                $q->where('kategori_id', $this->filterKategori);
            })
            ->orderBy('created_at', 'desc');

        $bukus = $query->paginate(10);

        $selectedBukuDetail = null;
        $eksemplarDetailList = collect();

        if ($this->showDetailEksemplarModal && $this->selectedBukuIdForDetail) {
            $selectedBukuDetail = Buku::find($this->selectedBukuIdForDetail);
            if ($selectedBukuDetail) {
                $eksemplarDetailList = EksemplarBuku::where('buku_id', $this->selectedBukuIdForDetail)
                    ->when($this->searchEksemplar, function ($q) {
                        $q->where(function ($sub) {
                            $sub->where('kode_eksemplar', 'like', "%{$this->searchEksemplar}%")
                                ->orWhere('status', 'like', "%{$this->searchEksemplar}%")
                                ->orWhere('kondisi_fisik', 'like', "%{$this->searchEksemplar}%");
                        });
                    })
                    ->orderBy('kode_eksemplar', 'asc')
                    ->get();
            }
        }

        $listPenulis = Buku::whereNotNull('penulis')->where('penulis', '!=', '')->distinct()->orderBy('penulis')->pluck('penulis');
        $listPenerbit = Buku::whereNotNull('penerbit')->where('penerbit', '!=', '')->distinct()->orderBy('penerbit')->pluck('penerbit');

        return view('livewire.petugas-perpus-buku', [
            'kategoriList'        => $kategoriList,
            'ddcList'             => $ddcList,
            'mapelList'           => $mapelList,
            'bukus'               => $bukus,
            'nonFiksiKategoriId'  => $nonFiksiKategoriId,
            'selectedBukuDetail'  => $selectedBukuDetail,
            'eksemplarDetailList' => $eksemplarDetailList,
            'listPenulis'         => $listPenulis,
            'listPenerbit'        => $listPenerbit,
        ])->title('Katalog & Input Buku - Portal Perpustakaan');
    }
}
