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
 * Export data DDC dari SLiMS ke format Excel.
 * Bisa dipakai oleh sekolah baru yang belum punya data buku
 * — cukup download DDC dari SLiMS lama lalu import ke ERP baru.
 *
 * Kolom: No | Kode DDC | Kategori / Subjek | Tipe Topik
 */
class SlimsDdcExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithEvents, WithTitle
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
        return 'DDC SLiMS';
    }

    public function collection(): Collection
    {
        return $this->data;
    }

    public function headings(): array
    {
        return ['No', 'Kode DDC', 'Kategori / Subjek', 'Tipe Topik'];
    }

    public function map($row): array
    {
        static $no = 0;
        $no++;

        $tipe = match ($row->topic_type ?? '') {
            't'  => 'Topik Utama',
            'g'  => 'Geografis',
            'n'  => 'Nama',
            'tm' => 'Temporal',
            'gr' => 'Genre',
            'oc' => 'Pekerjaan',
            default => $row->topic_type ?? '-',
        };

        $kodeDdc = (isset($row->classification) && trim($row->classification) !== '')
            ? trim($row->classification)
            : 'T' . $row->topic_id;

        return [
            $no,
            $kodeDdc,
            trim($row->topic),
            $tipe,
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
                $lastCol = 'D';

                // Insert 3 baris header
                $sheet->insertNewRowBefore(1, 3);

                $sheet->mergeCells("A1:{$lastCol}1");
                $sheet->setCellValue('A1', 'DATA KLASIFIKASI DDC — EXPORT DARI SLIMS');
                $sheet->getStyle('A1')->applyFromArray([
                    'font'      => ['bold' => true, 'size' => 13],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                $sheet->mergeCells("A2:{$lastCol}2");
                $sheet->setCellValue('A2', 'Dicetak: ' . now()->translatedFormat('d F Y, H:i') . ' WIB  |  Total: ' . $this->totalRows . ' topik');
                $sheet->getStyle('A2')->applyFromArray([
                    'font'      => ['size' => 9, 'italic' => true],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                // Baris 3 kosong
                $sheet->mergeCells("A3:{$lastCol}3");
                $sheet->getRowDimension(3)->setRowHeight(6);

                // Header kolom
                $sheet->getStyle("A4:{$lastCol}4")->applyFromArray([
                    'font'      => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF1E3A5F']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                    'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFDDDDDD']]],
                ]);

                // Lebar kolom
                $sheet->getColumnDimension('A')->setWidth(6);
                $sheet->getColumnDimension('B')->setWidth(16);
                $sheet->getColumnDimension('C')->setWidth(45);
                $sheet->getColumnDimension('D')->setWidth(16);

                // Zebra striping
                if ($this->totalRows > 0) {
                    $lastRow = 4 + $this->totalRows;
                    for ($row = 5; $row <= $lastRow; $row++) {
                        $bg = ($row % 2 === 0) ? 'FFF0F4FF' : 'FFFFFFFF';
                        $sheet->getStyle("A{$row}:{$lastCol}{$row}")->applyFromArray([
                            'fill'    => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => $bg]],
                            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFDDDDDD']]],
                        ]);
                        $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                        $sheet->getStyle("B{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                        $sheet->getStyle("D{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
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
