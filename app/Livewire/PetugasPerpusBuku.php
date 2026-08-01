<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use App\Models\Buku;
use App\Models\KategoriBuku;
use App\Models\KlasifikasiDdc;
use App\Models\InventarisBuku;
use App\Models\EksemplarBuku;
use App\Models\MataPelajaran;
use Illuminate\Support\Facades\DB;
use App\Models\PengaturanSekolah;

#[Layout('components.layouts.portal')]
class PetugasPerpusBuku extends Component
{
    use WithPagination;

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
    public $grade_level = 'umum';
    
    public $asal_buku = 'Pembelian';
    public $harga_buku = null;
    public $prefix_kode = '';

    // Modal Unduh Katalog
    public bool $showUnduhModal = false;
    public array $filterKategoriUnduh = [];
    public array $filterMapelUnduh = [];
    public string $formatUnduh = 'pdf';

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
        'grade_level' => 'nullable|string|in:umum,7,8,9',
        'asal_buku' => 'required|in:Pembelian,Hibah,Tukar,Terbitan Sendiri',
        'harga_buku' => 'nullable|numeric|min:0',
        'prefix_kode' => 'required|string|max:10',
    ];

    public function mount()
    {
        $this->tahun_terbit = date('Y');
        $firstCat = KategoriBuku::first();
        if ($firstCat) {
            $this->kategori_id = $firstCat->id;
        }
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
        $this->reset(['judul', 'penulis', 'penerbit', 'isbn', 'lokasi_rak', 'klasifikasi_ddc_id', 'grade_level', 'mapel_id', 'harga_buku', 'prefix_kode']);
        $this->tahun_terbit = date('Y');
        $this->jumlah_eksemplar = null;
        $this->asal_buku = 'Pembelian';
        $this->grade_level = 'umum';
        $this->mapel_id = 'lainnya';
        $this->showInputModal = true;
    }

    public function simpanBuku()
    {
        $this->validate();

        DB::transaction(function () {
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
            for ($i = 0; $i < (int)$this->jumlah_eksemplar; $i++) {
                $kodeBarcode = EksemplarBuku::generateKodeEksemplar($this->prefix_kode);
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
                        ->orWhere('isbn', 'like', "%{$this->search}%");
                });
            })
            ->when($this->filterKategori, function ($q) {
                $q->where('kategori_id', $this->filterKategori);
            })
            ->orderBy('created_at', 'desc');

        $bukus = $query->paginate(10);

        return view('livewire.petugas-perpus-buku', [
            'kategoriList'       => $kategoriList,
            'ddcList'            => $ddcList,
            'mapelList'          => $mapelList,
            'bukus'              => $bukus,
            'nonFiksiKategoriId' => $nonFiksiKategoriId,
        ])->title('Katalog & Input Buku - Portal Perpustakaan');
    }
}
