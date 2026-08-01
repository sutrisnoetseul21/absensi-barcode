<?php

namespace App\Exports;

use App\Models\InventarisBuku;
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

class InventarisBukuExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithEvents
{
    protected Collection $inventaris;
    protected ?PengaturanSekolah $settings;
    protected array $statusFilter;
    protected int $totalRows = 0;

    public function __construct(array $statusFilter = [])
    {
        $this->statusFilter = $statusFilter;
        $this->settings     = PengaturanSekolah::current();
    }

    public function collection(): Collection
    {
        $query = InventarisBuku::with(['buku.kategoriBuku', 'buku.klasifikasiDdc'])
            ->when(!empty($this->statusFilter), fn ($q) => $q->whereIn('status', $this->statusFilter))
            ->orderBy('tanggal_masuk', 'desc');

        $result = $query->get();
        $this->totalRows = $result->count();

        return $result;
    }

    public function headings(): array
    {
        return [
            'No',
            'Tanggal Masuk',
            'No Inventaris',
            'Judul Buku',
            'Pengarang',
            'Penerbit',
            'Tahun Terbit',
            'Asal',
            'No Klasifikasi (DDC)',
            'Harga (Rp)',
            'Jumlah Eksemplar',
            'Status',
        ];
    }

    public function map($inv): array
    {
        static $no = 0;
        $no++;

        return [
            $no,
            $inv->tanggal_masuk ? \Carbon\Carbon::parse($inv->tanggal_masuk)->format('d/m/Y') : '-',
            $inv->no_inventaris ?? '-',
            $inv->buku?->judul ?? '-',
            $inv->buku?->penulis ?? '-',
            $inv->buku?->penerbit ?? '-',
            $inv->buku?->tahun_terbit ?? '-',
            ucwords(str_replace('_', ' ', $inv->asal ?? '')),
            $inv->buku?->klasifikasiDdc?->kode_ddc ?? '-',
            $inv->harga > 0 ? $inv->harga : 0,
            $inv->jumlah_eksemplar ?? 0,
            ucfirst($inv->status ?? '-'),
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
                $lastCol = 'L'; // 12 kolom

                // ===== INSERT 4 BARIS DI ATAS untuk kop surat =====
                $sheet->insertNewRowBefore(1, 4);

                // Baris 1: BUKU INDUK INVENTARIS
                $sheet->mergeCells("A1:{$lastCol}1");
                $sheet->setCellValue('A1', 'BUKU INDUK INVENTARIS PERPUSTAKAAN');
                $sheet->getStyle('A1')->applyFromArray([
                    'font'      => ['bold' => true, 'size' => 14],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                // Baris 2: Nama Sekolah (kiri) | Tanggal Cetak (kanan)
                $sheet->mergeCells("A2:I2");
                $sheet->setCellValue('A2', strtoupper($sekolah?->school_name ?? 'NAMA SEKOLAH'));
                $sheet->getStyle('A2')->applyFromArray([
                    'font'      => ['bold' => true, 'size' => 11],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
                ]);
                $sheet->mergeCells("J2:{$lastCol}2");
                $sheet->setCellValue('J2', 'Dicetak: ' . $tanggalCetak);
                $sheet->getStyle('J2')->applyFromArray([
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
                $sheet->getColumnDimension('B')->setWidth(13);  // Tanggal Masuk
                $sheet->getColumnDimension('C')->setWidth(20);  // No Inventaris
                $sheet->getColumnDimension('D')->setWidth(36);  // Judul Buku
                $sheet->getColumnDimension('E')->setWidth(18);  // Pengarang
                $sheet->getColumnDimension('F')->setWidth(16);  // Penerbit
                $sheet->getColumnDimension('G')->setWidth(10);  // Tahun Terbit
                $sheet->getColumnDimension('H')->setWidth(14);  // Asal
                $sheet->getColumnDimension('I')->setWidth(18);  // DDC
                $sheet->getColumnDimension('J')->setWidth(14);  // Harga
                $sheet->getColumnDimension('K')->setWidth(14);  // Jumlah Eksemplar
                $sheet->getColumnDimension('L')->setWidth(10);  // Status

                // Formatting kolom harga sebagai angka
                if ($this->totalRows > 0) {
                    $lastDataRow = 5 + $this->totalRows;
                    $sheet->getStyle("J6:J{$lastDataRow}")
                        ->getNumberFormat()
                        ->setFormatCode('#,##0');

                    for ($row = 6; $row <= $lastDataRow; $row++) {
                        $isEven = ($row % 2 === 0);
                        $bg     = $isEven ? 'FFF0F4FF' : 'FFFFFFFF';
                        $sheet->getStyle("A{$row}:{$lastCol}{$row}")->applyFromArray([
                            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => $bg]],
                            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFDDDDDD']]],
                            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
                        ]);
                        // Center kolom tertentu
                        foreach (['A', 'B', 'G', 'H', 'K', 'L'] as $col) {
                            $sheet->getStyle("{$col}{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                        }
                        // Right-align harga
                        $sheet->getStyle("J{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
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
