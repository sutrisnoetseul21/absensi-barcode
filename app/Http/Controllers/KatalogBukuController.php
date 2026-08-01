<?php

namespace App\Http\Controllers;

use App\Exports\KatalogBukuExport;
use App\Models\Buku;
use App\Models\KategoriBuku;
use App\Models\PengaturanSekolah;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class KatalogBukuController extends Controller
{
    /**
     * Bangun label filter untuk kop dokumen (PDF / keterangan Excel).
     */
    private function buildFilterLabel(array $kategoriIds, array $mapelIds): string
    {
        if (empty($kategoriIds) && empty($mapelIds)) {
            return 'Semua Koleksi';
        }

        $parts = [];

        if (!empty($kategoriIds)) {
            $namaKategori = KategoriBuku::whereIn('id', $kategoriIds)
                ->pluck('nama_kategori')
                ->toArray();
            $parts[] = 'Koleksi: ' . implode(', ', $namaKategori);
        }

        if (!empty($mapelIds)) {
            $namaMapel = \App\Models\MataPelajaran::whereIn('id', $mapelIds)
                ->pluck('nama_mapel')
                ->toArray();
            $parts[] = 'Mapel: ' . implode(', ', $namaMapel);
        }

        return implode(' | ', $parts);
    }

    /**
     * Bangun query buku berdasarkan filter.
     */
    private function buildQuery(array $kategoriIds, array $mapelIds)
    {
        return Buku::with(['kategoriBuku', 'klasifikasiDdc', 'mataPelajaran', 'eksemplarBukus'])
            ->when(!empty($kategoriIds), fn ($q) => $q->whereIn('kategori_id', $kategoriIds))
            ->when(!empty($mapelIds), fn ($q) => $q->whereIn('mapel_id', $mapelIds))
            ->orderBy('judul');
    }

    /**
     * Download PDF Katalog Buku.
     */
    public function downloadPdf(Request $request)
    {
        $request->validate([
            'kategori_ids'   => ['nullable', 'array'],
            'kategori_ids.*' => ['string'],
            'mapel_ids'      => ['nullable', 'array'],
            'mapel_ids.*'    => ['string'],
        ]);

        $kategoriIds  = $request->input('kategori_ids', []);
        $mapelIds     = $request->input('mapel_ids', []);
        $filterLabel  = $this->buildFilterLabel($kategoriIds, $mapelIds);
        $settings     = PengaturanSekolah::current();

        $bukus = $this->buildQuery($kategoriIds, $mapelIds)->get();

        $pdf = Pdf::loadView('pdf.katalog-buku', [
            'bukus'       => $bukus,
            'settings'    => $settings,
            'filterLabel' => $filterLabel,
        ])->setPaper('a4', 'landscape');

        $filename = 'katalog-buku-' . now()->format('Ymd-His') . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * Download Excel Katalog Buku.
     */
    public function downloadExcel(Request $request)
    {
        $request->validate([
            'kategori_ids'   => ['nullable', 'array'],
            'kategori_ids.*' => ['string'],
            'mapel_ids'      => ['nullable', 'array'],
            'mapel_ids.*'    => ['string'],
        ]);

        $kategoriIds = $request->input('kategori_ids', []);
        $mapelIds    = $request->input('mapel_ids', []);

        $filename = 'katalog-buku-' . now()->format('Ymd-His') . '.xlsx';

        return Excel::download(
            new KatalogBukuExport($kategoriIds, $mapelIds),
            $filename
        );
    }

    // ===== INVENTARIS BUKU =====

    /**
     * Download PDF Inventaris Buku.
     */
    public function downloadInventarisPdf(Request $request)
    {
        $request->validate([
            'status' => ['nullable', 'array'],
            'status.*' => ['string', 'in:aktif,dibatalkan'],
        ]);

        $statusFilter = $request->input('status', []);
        $filterLabel  = $this->buildInventarisFilterLabel($statusFilter);
        $settings     = PengaturanSekolah::current();

        $inventaris = \App\Models\InventarisBuku::with(['buku.kategoriBuku', 'buku.klasifikasiDdc'])
            ->when(!empty($statusFilter), fn ($q) => $q->whereIn('status', $statusFilter))
            ->orderBy('tanggal_masuk', 'desc')
            ->get();

        $pdf = Pdf::loadView('pdf.inventaris-buku', [
            'inventaris'  => $inventaris,
            'settings'    => $settings,
            'filterLabel' => $filterLabel,
        ])->setPaper('a4', 'landscape');

        $filename = 'inventaris-buku-' . now()->format('Ymd-His') . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * Download Excel Inventaris Buku.
     */
    public function downloadInventarisExcel(Request $request)
    {
        $request->validate([
            'status'   => ['nullable', 'array'],
            'status.*' => ['string', 'in:aktif,dibatalkan'],
        ]);

        $statusFilter = $request->input('status', []);

        $filename = 'inventaris-buku-' . now()->format('Ymd-His') . '.xlsx';

        return Excel::download(
            new \App\Exports\InventarisBukuExport($statusFilter),
            $filename
        );
    }

    private function buildInventarisFilterLabel(array $statusFilter): string
    {
        if (empty($statusFilter)) {
            return 'Semua Status';
        }

        $labels = array_map(fn ($s) => ucfirst($s), $statusFilter);

        return 'Status: ' . implode(', ', $labels);
    }

    // ===== PEMINJAMAN BUKU =====

    /**
     * Download PDF Peminjaman Buku.
     */
    public function downloadPeminjamanPdf(Request $request)
    {
        $request->validate([
            'status'   => ['nullable', 'array'],
            'status.*' => ['string', 'in:dipinjam,dikembalikan,hilang'],
            'tipe'     => ['nullable', 'array'],
            'tipe.*'   => ['string', 'in:siswa,guru'],
        ]);

        $statusFilter      = $request->input('status', []);
        $tipeAnggotaFilter = $request->input('tipe', []);
        $filterLabel       = $this->buildPeminjamanFilterLabel($statusFilter, $tipeAnggotaFilter);
        $settings          = PengaturanSekolah::current();

        $peminjaman = \App\Models\Peminjaman::with(['peminjam', 'eksemplarBuku.buku'])
            ->when(!empty($statusFilter), fn ($q) => $q->whereIn('status', $statusFilter))
            ->when(!empty($tipeAnggotaFilter), fn ($q) => $q->whereIn('peminjam_type', $tipeAnggotaFilter))
            ->orderBy('created_at', 'desc')
            ->get();

        $pdf = Pdf::loadView('pdf.peminjaman-buku', [
            'peminjaman'  => $peminjaman,
            'settings'    => $settings,
            'filterLabel' => $filterLabel,
        ])->setPaper('a4', 'landscape');

        $filename = 'peminjaman-buku-' . now()->format('Ymd-His') . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * Download Excel Peminjaman Buku.
     */
    public function downloadPeminjamanExcel(Request $request)
    {
        $request->validate([
            'status'   => ['nullable', 'array'],
            'status.*' => ['string', 'in:dipinjam,dikembalikan,hilang'],
            'tipe'     => ['nullable', 'array'],
            'tipe.*'   => ['string', 'in:siswa,guru'],
        ]);

        $statusFilter      = $request->input('status', []);
        $tipeAnggotaFilter = $request->input('tipe', []);

        $filename = 'peminjaman-buku-' . now()->format('Ymd-His') . '.xlsx';

        return Excel::download(
            new \App\Exports\PeminjamanExport($statusFilter, $tipeAnggotaFilter),
            $filename
        );
    }

    private function buildPeminjamanFilterLabel(array $statusFilter, array $tipeFilter): string
    {
        $labels = [];
        
        if (!empty($statusFilter)) {
            $statusLabels = array_map(fn ($s) => ucfirst($s), $statusFilter);
            $labels[] = 'Status: ' . implode(', ', $statusLabels);
        }

        if (!empty($tipeFilter)) {
            $tipeLabels = array_map(fn ($t) => match($t) {
                'siswa' => 'Siswa',
                'guru'  => 'Guru/Staff',
                default => ucfirst($t)
            }, $tipeFilter);
            $labels[] = 'Tipe: ' . implode(', ', $tipeLabels);
        }

        return empty($labels) ? 'Semua Data' : implode(' | ', $labels);
    }

    // ===== KUNJUNGAN PERPUSTAKAAN =====

    /**
     * Download PDF Kunjungan Perpustakaan.
     */
    public function downloadKunjunganPdf(Request $request)
    {
        $request->validate([
            'start_date' => ['nullable', 'date'],
            'end_date'   => ['nullable', 'date', 'after_or_equal:start_date'],
            'tipe'       => ['nullable', 'array'],
            'tipe.*'     => ['string', 'in:siswa,guru'],
        ]);

        $startDate         = $request->input('start_date');
        $endDate           = $request->input('end_date');
        $tipeAnggotaFilter = $request->input('tipe', []);
        $filterLabel       = $this->buildKunjunganFilterLabel($startDate, $endDate, $tipeAnggotaFilter);
        $settings          = PengaturanSekolah::current();

        $kunjungan = \App\Models\KunjunganPerpustakaan::with(['pengunjung'])
            ->when($startDate, fn ($q) => $q->where('tanggal', '>=', $startDate))
            ->when($endDate, fn ($q) => $q->where('tanggal', '<=', $endDate))
            ->when(!empty($tipeAnggotaFilter), fn ($q) => $q->whereIn('pengunjung_type', $tipeAnggotaFilter))
            ->orderBy('tanggal', 'desc')
            ->orderBy('waktu_masuk', 'desc')
            ->get();

        $pdf = Pdf::loadView('pdf.kunjungan-perpustakaan', [
            'kunjungan'   => $kunjungan,
            'settings'    => $settings,
            'filterLabel' => $filterLabel,
        ])->setPaper('a4', 'portrait');

        $filename = 'riwayat-kunjungan-' . now()->format('Ymd-His') . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * Download Excel Kunjungan Perpustakaan.
     */
    public function downloadKunjunganExcel(Request $request)
    {
        $request->validate([
            'start_date' => ['nullable', 'date'],
            'end_date'   => ['nullable', 'date', 'after_or_equal:start_date'],
            'tipe'       => ['nullable', 'array'],
            'tipe.*'     => ['string', 'in:siswa,guru'],
        ]);

        $startDate         = $request->input('start_date');
        $endDate           = $request->input('end_date');
        $tipeAnggotaFilter = $request->input('tipe', []);

        $filename = 'riwayat-kunjungan-' . now()->format('Ymd-His') . '.xlsx';

        return Excel::download(
            new \App\Exports\KunjunganExport($startDate, $endDate, $tipeAnggotaFilter),
            $filename
        );
    }

    private function buildKunjunganFilterLabel(?string $startDate, ?string $endDate, array $tipeFilter): string
    {
        $labels = [];
        
        if ($startDate && $endDate) {
            $labels[] = 'Periode: ' . \Carbon\Carbon::parse($startDate)->format('d/m/Y') . ' - ' . \Carbon\Carbon::parse($endDate)->format('d/m/Y');
        } elseif ($startDate) {
            $labels[] = 'Mulai: ' . \Carbon\Carbon::parse($startDate)->format('d/m/Y');
        } elseif ($endDate) {
            $labels[] = 'Sampai: ' . \Carbon\Carbon::parse($endDate)->format('d/m/Y');
        }

        if (!empty($tipeFilter)) {
            $tipeLabels = array_map(fn ($t) => match($t) {
                'siswa' => 'Siswa',
                'guru'  => 'Guru/Staff',
                default => ucfirst($t)
            }, $tipeFilter);
            $labels[] = 'Tipe Anggota: ' . implode(', ', $tipeLabels);
        }

        return empty($labels) ? 'Semua Data' : implode(' | ', $labels);
    }
}
