<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use App\Models\Peminjaman;
use App\Models\EksemplarBuku;
use App\Actions\ProcessSirkulasiAction;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

#[Layout('components.layouts.portal')]
class PetugasPerpusSirkulasi extends Component
{
    use WithPagination;

    // Scan Barcode State
    public $barcodeAnggota = '';
    public $barcodeBuku = '';

    // Selected Peminjam info
    public $peminjamId = null;
    public $peminjamType = null;
    public $peminjamNama = null;
    public $peminjamSub = null;

    // Feedback message
    public $feedbackType = null;
    public $feedbackMessage = null;

    // Search & Filter Peminjaman Aktif
    public $search = '';
    public $filterStatus = 'dipinjam';

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedFilterStatus()
    {
        $this->resetPage();
    }

    public function processScanPeminjam()
    {
        if (empty(trim($this->barcodeAnggota))) return;

        $action = new ProcessSirkulasiAction();
        $res = $action->execute([
            'jenis_scan' => 'PEMINJAM',
            'barcode' => trim($this->barcodeAnggota)
        ], Auth::id());

        if ($res['status'] === 'success') {
            $this->peminjamId = $res['peminjam_id'];
            $this->peminjamType = $res['peminjam_type'];
            $this->peminjamNama = $res['name'];
            $this->peminjamSub = $res['sub_info'];
            $this->feedbackType = 'success';
            $this->feedbackMessage = "Anggota {$res['name']} berhasil diidentifikasi. Silakan scan kode eksemplar buku.";
        } else {
            $this->feedbackType = 'error';
            $this->feedbackMessage = $res['message'] ?? 'Kartu anggota tidak ditemukan.';
        }

        $this->barcodeAnggota = '';
    }

    public function processScanBuku()
    {
        if (empty(trim($this->barcodeBuku))) return;

        if (!$this->peminjamId || !$this->peminjamType) {
            $this->feedbackType = 'error';
            $this->feedbackMessage = 'Silakan scan/identifikasi kartu anggota terlebih dahulu!';
            return;
        }

        $action = new ProcessSirkulasiAction();
        $res = $action->execute([
            'jenis_scan' => 'BUKU',
            'barcode' => trim($this->barcodeBuku),
            'peminjam_id' => $this->peminjamId,
            'peminjam_type' => $this->peminjamType,
        ], Auth::id());

        if ($res['status'] === 'success_pinjam') {
            $this->feedbackType = 'success';
            $this->feedbackMessage = "Peminjaman Berhasil: Buku \"{$res['buku_title']}\" (Jatuh Tempo: {$res['jatuh_tempo']})";
        } elseif ($res['status'] === 'success_kembali') {
            $this->feedbackType = 'success';
            $this->feedbackMessage = "Pengembalian Berhasil: Buku \"{$res['buku_title']}\" telah dikembalikan.";
        } else {
            $this->feedbackType = 'error';
            $this->feedbackMessage = $res['message'] ?? 'Gagal memproses sirkulasi buku.';
        }

        $this->barcodeBuku = '';
        $this->resetPage();
    }

    public function kembalikanBukuDirect($peminjamanId)
    {
        $peminjaman = Peminjaman::find($peminjamanId);
        if (!$peminjaman || $peminjaman->status !== 'dipinjam') {
            $this->feedbackType = 'error';
            $this->feedbackMessage = 'Data peminjaman tidak ditemukan atau sudah dikembalikan.';
            return;
        }

        $eksemplar = $peminjaman->eksemplarBuku;
        $peminjaman->update([
            'status' => 'dikembalikan',
            'tanggal_kembali' => now()->toDateString(),
        ]);

        if ($eksemplar) {
            $eksemplar->update(['status' => 'tersedia']);
        }

        $this->feedbackType = 'success';
        $this->feedbackMessage = "Buku " . ($eksemplar?->buku?->judul ?? '') . " berhasil dikembalikan!";
    }

    public function resetPeminjam()
    {
        $this->reset(['peminjamId', 'peminjamType', 'peminjamNama', 'peminjamSub', 'barcodeAnggota', 'barcodeBuku', 'feedbackType', 'feedbackMessage']);
    }

    public function render()
    {
        $today = Carbon::today('Asia/Jakarta');

        $query = Peminjaman::with(['peminjam', 'eksemplarBuku.buku', 'petugas'])
            ->when($this->filterStatus, function ($q) {
                if ($this->filterStatus === 'terlambat') {
                    $q->where('status', 'dipinjam')->where('tanggal_jatuh_tempo', '<', Carbon::today());
                } else {
                    $q->where('status', $this->filterStatus);
                }
            })
            ->when($this->search, function ($q) {
                $q->whereHas('eksemplarBuku', function ($sub) {
                    $sub->where('kode_eksemplar', 'like', "%{$this->search}%")
                        ->orWhereHas('buku', function ($b) {
                            $b->where('judul', 'like', "%{$this->search}%");
                        });
                });
            })
            ->orderBy('created_at', 'desc');

        $peminjamans = $query->paginate(10);

        return view('livewire.petugas-perpus-sirkulasi', [
            'peminjamans' => $peminjamans,
            'today' => $today,
        ])->title('Sirkulasi & Peminjaman - Portal Perpustakaan');
    }
}
