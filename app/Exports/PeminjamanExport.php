<?php

namespace App\Exports;

use App\Models\Peminjaman;
use App\Models\PengaturanSekolah;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Carbon\Carbon;

class PeminjamanExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithEvents
{
    protected Collection $peminjaman;
    protected ?PengaturanSekolah $settings;
    protected array $statusFilter;
    protected array $tipeAnggotaFilter;
    protected int $totalRows = 0;

    public function __construct(array $statusFilter = [], array $tipeAnggotaFilter = [])
    {
        $this->statusFilter      = $statusFilter;
        $this->tipeAnggotaFilter = $tipeAnggotaFilter;
        $this->settings          = PengaturanSekolah::current();
    }

    public function collection(): Collection
    {
        $query = Peminjaman::with(['peminjam', 'eksemplarBuku.buku'])
            ->when(!empty($this->statusFilter), fn ($q) => $q->whereIn('status', $this->statusFilter))
            ->when(!empty($this->tipeAnggotaFilter), fn ($q) => $q->whereIn('peminjam_type', $this->tipeAnggotaFilter))
            ->orderBy('created_at', 'desc');

        $result = $query->get();
        $this->totalRows = $result->count();

        return $result;
    }

    public function headings(): array
    {
        return [
            'No',
            'Tgl Pinjam',
            'Tgl Jatuh Tempo',
            'Peminjam',
            'Tipe Anggota',
            'Judul Buku',
            'Kode Eksemplar',
            'Tgl Kembali',
            'Status',
        ];
    }

    public function map($pem): array
    {
        static $no = 0;
        $no++;

        $statusLengkap = ucfirst($pem->status ?? '-');
        if ($pem->status === 'dipinjam' && $pem->tanggal_jatuh_tempo && $pem->tanggal_jatuh_tempo < Carbon::now()->startOfDay()) {
            $statusLengkap = 'Terlambat';
        }

        $tipeAnggota = match ((string) $pem->peminjam_type) {
            'siswa' => 'Siswa',
            'guru'  => 'Guru / Staff',
            default => ucfirst($pem->peminjam_type ?? '-'),
        };

        return [
            $no,
            $pem->tanggal_pinjam ? $pem->tanggal_pinjam->format('d/m/Y') : '-',
            $pem->tanggal_jatuh_tempo ? $pem->tanggal_jatuh_tempo->format('d/m/Y') : '-',
            $pem->peminjam?->name ?? '-',
            $tipeAnggota,
            $pem->eksemplarBuku?->buku?->judul ?? '-',
            $pem->eksemplarBuku?->kode_eksemplar ?? '-',
            $pem->tanggal_kembali ? $pem->tanggal_kembali->format('d/m/Y') : '-',
            $statusLengkap,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $sekolah = $this->settings;
                $tanggalCetak = now()->translatedFormat('d F Y');
                $lastCol = 'I'; // 9 kolom

                // ===== INSERT 4 BARIS DI ATAS untuk kop surat =====
                $sheet->insertNewRowBefore(1, 4);

                // Baris 1: DATA PEMINJAMAN BUKU PERPUSTAKAAN
                $sheet->mergeCells("A1:{$lastCol}1");
                $sheet->setCellValue('A1', 'DATA PEMINJAMAN BUKU PERPUSTAKAAN');
                $sheet->getStyle('A1')->applyFromArray([
                    'font'      => ['bold' => true, 'size' => 14],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                // Baris 2: Nama Sekolah (kiri) | Tanggal Cetak (kanan)
                $sheet->mergeCells("A2:F2");
                $sheet->setCellValue('A2', strtoupper($sekolah?->school_name ?? 'NAMA SEKOLAH'));
                $sheet->getStyle('A2')->applyFromArray([
                    'font'      => ['bold' => true, 'size' => 11],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
                ]);
                $sheet->mergeCells("G2:{$lastCol}2");
                $sheet->setCellValue('G2', 'Dicetak: ' . $tanggalCetak);
                $sheet->getStyle('G2')->applyFromArray([
                    'font'      => ['size' => 10],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT],
                ]);

                // Baris 3: Alamat Sekolah
                $sheet->mergeCells("A3:{$lastCol}3");
                $sheet->setCellValue('A3', $sekolah?->school_address ?? '');
                $sheet->getStyle('A3')->applyFromArray([
                    'font'      => ['size' => 10, 'italic' => true],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
                ]);

                // Baris 4: Kosong sebagai pemisah
                $sheet->mergeCells("A4:{$lastCol}4");
                $sheet->getRowDimension(4)->setRowHeight(6);

                // Baris 5: Header kolom
                $sheet->getStyle("A5:{$lastCol}5")->applyFromArray([
                    'font'      => ['bold' => true, 'size' => 10, 'color' => ['argb' => 'FFFFFFFF']],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF1E3A5F']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
                    'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFDDDDDD']]],
                ]);
                $sheet->getRowDimension(5)->setRowHeight(22);

                // Lebar kolom
                $sheet->getColumnDimension('A')->setWidth(5);   // No
                $sheet->getColumnDimension('B')->setWidth(14);  // Tgl Pinjam
                $sheet->getColumnDimension('C')->setWidth(16);  // Tgl Jatuh Tempo
                $sheet->getColumnDimension('D')->setWidth(30);  // Peminjam
                $sheet->getColumnDimension('E')->setWidth(14);  // Tipe Anggota
                $sheet->getColumnDimension('F')->setWidth(35);  // Judul Buku
                $sheet->getColumnDimension('G')->setWidth(16);  // Kode Eksemplar
                $sheet->getColumnDimension('H')->setWidth(14);  // Tgl Kembali
                $sheet->getColumnDimension('I')->setWidth(14);  // Status

                // Formatting isi tabel
                if ($this->totalRows > 0) {
                    $lastDataRow = 5 + $this->totalRows;

                    for ($row = 6; $row <= $lastDataRow; $row++) {
                        $isEven = ($row % 2 === 0);
                        $bg     = $isEven ? 'FFF0F4FF' : 'FFFFFFFF';
                        $sheet->getStyle("A{$row}:{$lastCol}{$row}")->applyFromArray([
                            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => $bg]],
                            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFDDDDDD']]],
                            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
                        ]);
                        // Center kolom tertentu
                        foreach (['A', 'B', 'C', 'E', 'G', 'H', 'I'] as $col) {
                            $sheet->getStyle("{$col}{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                        }
                    }

                    // Border luar tabel
                    $sheet->getStyle("A5:{$lastCol}{$lastDataRow}")->applyFromArray([
                        'borders' => ['outline' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['argb' => 'FF1E3A5F']]],
                    ]);
                }

                $sheet->freezePane('A6');
            },
        ];
    }
}
