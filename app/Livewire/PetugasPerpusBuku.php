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
use Illuminate\Support\Facades\DB;

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
    public $klasifikasi_ddc_id = null;
    public $jumlah_eksemplar = 1;
    public $grade_level = null;

    // Search & Filter
    public $search = '';
    public $filterKategori = '';

    protected $rules = [
        'judul' => 'required|string|max:255',
        'kategori_id' => 'required|exists:kategori_bukus,id',
        'penulis' => 'nullable|string|max:255',
        'penerbit' => 'nullable|string|max:255',
        'tahun_terbit' => 'nullable|integer|digits:4',
        'isbn' => 'nullable|string|max:50',
        'lokasi_rak' => 'nullable|string|max:100',
        'jumlah_eksemplar' => 'required|integer|min:1|max:200',
    ];

    public function mount()
    {
        $this->tahun_terbit = date('Y');
        $firstCat = KategoriBuku::first();
        if ($firstCat) {
            $this->kategori_id = $firstCat->id;
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

    public function openInputModal()
    {
        $this->reset(['judul', 'penulis', 'penerbit', 'isbn', 'lokasi_rak', 'klasifikasi_ddc_id', 'grade_level']);
        $this->tahun_terbit = date('Y');
        $this->jumlah_eksemplar = 1;
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
                'klasifikasi_ddc_id' => $this->klasifikasi_ddc_id ?: null,
                'penulis' => $this->penulis,
                'penerbit' => $this->penerbit,
                'tahun_terbit' => $this->tahun_terbit ? (int)$this->tahun_terbit : null,
                'isbn' => $this->isbn,
                'lokasi_rak' => $this->lokasi_rak,
                'grade_level' => $this->grade_level ?: null,
            ]);

            // 2. Buat Record Inventaris Buku Batch
            $inventaris = InventarisBuku::create([
                'buku_id' => $buku->id,
                'no_inventaris' => 'INV-' . date('Ymd-His'),
                'tanggal_masuk' => now()->toDateString(),
                'asal' => 'pembelian',
                'harga' => 0,
                'jumlah_eksemplar' => (int)$this->jumlah_eksemplar,
                'status' => 'aktif',
            ]);

            // 3. Generate Eksemplar Fisik
            for ($i = 0; $i < (int)$this->jumlah_eksemplar; $i++) {
                $kodeBarcode = EksemplarBuku::generateKodeEksemplar('UMM');
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

        $query = Buku::with(['kategori', 'klasifikasiDdc', 'eksemplarBukus'])
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
            'kategoriList' => $kategoriList,
            'ddcList' => $ddcList,
            'bukus' => $bukus,
        ])->title('Katalog & Input Buku - Portal Perpustakaan');
    }
}
