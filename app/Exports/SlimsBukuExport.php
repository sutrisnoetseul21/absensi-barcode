<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

/**
 * Export data katalog buku dari SLiMS ke Excel.
 * Kolom: No | Judul | ISBN | Penulis | Penerbit | Tahun | Klasifikasi | Kategori
 */
class SlimsBukuExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithEvents, WithTitle
{
    protected Collection $data;
    protected int $totalRows = 0;

    public function __construct(Collection $data)
    {
        $this->data      = $data;
        $this->totalRows = $data->count();
    }

    public function title(): string
    {
        return 'Katalog Buku SLiMS';
    }

    public function collection(): Collection
    {
        return $this->data;
    }

    public function headings(): array
    {
        return [
            'No',
            'Judul',
            'ISBN',
            'Penulis',
            'Penerbit',
            'Tahun Terbit',
            'Klasifikasi DDC',
            'Jenis Koleksi',
        ];
    }

    public function map($row): array
    {
        static $no = 0;
        $no++;

        $koleksi = match ((int) ($row->coll_type_id ?? 0)) {
            1 => 'Reference',
            2 => 'Textbook',
            3 => 'Fiction',
            4 => 'Ensiklopedia',
            default => '-',
        };

        return [
            $no,
            trim($row->title ?? ''),
            trim($row->isbn_issn ?? ''),
            trim($row->penulis ?? ''),
            trim($row->publisher_name ?? ''),
            trim($row->publish_year ?? ''),
            trim($row->classification ?? ''),
            $koleksi,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet   = $event->sheet->getDelegate();
                $lastCol = 'H';

                $sheet->insertNewRowBefore(1, 3);

                $sheet->mergeCells("A1:{$lastCol}1");
                $sheet->setCellValue('A1', 'KATALOG BUKU — EXPORT DARI SLIMS');
                $sheet->getStyle('A1')->applyFromArray([
                    'font'      => ['bold' => true, 'size' => 13],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                $sheet->mergeCells("A2:{$lastCol}2");
                $sheet->setCellValue('A2', 'Dicetak: ' . now()->translatedFormat('d F Y, H:i') . ' WIB  |  Total: ' . $this->totalRows . ' judul');
                $sheet->getStyle('A2')->applyFromArray([
                    'font'      => ['size' => 9, 'italic' => true],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                $sheet->mergeCells("A3:{$lastCol}3");
                $sheet->getRowDimension(3)->setRowHeight(6);

                $sheet->getStyle("A4:{$lastCol}4")->applyFromArray([
                    'font'      => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF1E3A5F']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
                    'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFDDDDDD']]],
                ]);
                $sheet->getRowDimension(4)->setRowHeight(24);

                $sheet->getColumnDimension('A')->setWidth(6);
                $sheet->getColumnDimension('B')->setWidth(45);
                $sheet->getColumnDimension('C')->setWidth(16);
                $sheet->getColumnDimension('D')->setWidth(25);
                $sheet->getColumnDimension('E')->setWidth(22);
                $sheet->getColumnDimension('F')->setWidth(12);
                $sheet->getColumnDimension('G')->setWidth(18);
                $sheet->getColumnDimension('H')->setWidth(15);

                if ($this->totalRows > 0) {
                    $lastRow = 4 + $this->totalRows;
                    for ($row = 5; $row <= $lastRow; $row++) {
                        $bg = ($row % 2 === 0) ? 'FFF0F4FF' : 'FFFFFFFF';
                        $sheet->getStyle("A{$row}:{$lastCol}{$row}")->applyFromArray([
                            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => $bg]],
                            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFDDDDDD']]],
                            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
                        ]);
                        $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                        $sheet->getStyle("F{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                        $sheet->getStyle("H{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    }
                    $sheet->getStyle("A4:{$lastCol}{$lastRow}")->applyFromArray([
                        'borders' => ['outline' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['argb' => 'FF1E3A5F']]],
                    ]);
                }

                $sheet->freezePane('A5');
            },
        ];
    }
}
