<?php

namespace App\Exports\Sheets;

use Illuminate\Database\Query\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
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
use App\Services\SlimsConnectionService;

class SlimsEksemplarSheet implements FromQuery, WithHeadings, WithMapping, WithStyles, WithEvents, WithTitle
{
    protected SlimsConnectionService $slimsConnection;
    protected int $totalRows = 0;

    public function __construct(SlimsConnectionService $slimsConnection)
    {
        $this->slimsConnection = $slimsConnection;
        $conn = $this->slimsConnection->getConnection();
        $this->totalRows = $conn->table('item')->count();
    }

    public function title(): string
    {
        return 'Eksemplar';
    }

    public function query()
    {
        $conn = $this->slimsConnection->getConnection();
        
        return $conn->table('item')
            ->select(
                'item_code',
                'biblio_id',
                'inventory_code',
                'item_status_id',
                'received_date',
                'order_no',
                'price'
            )
            ->orderBy('item_code');
    }

    public function headings(): array
    {
        return [
            'No',
            'biblio_id',
            'Kode Eksemplar',
            'No Inventaris',
            'Tanggal Masuk',
            'Asal',
            'Harga',
            'Status',
        ];
    }

    public function map($row): array
    {
        static $no = 0;
        $no++;

        $status = match ($row->item_status_id ?? null) {
            'R'     => 'rusak',
            'MIS'   => 'hilang',
            default => 'tersedia',
        };
        
        // Coba identifikasi asal dari order_no/source jika memungkinkan, default Pembelian
        $asal = 'Pembelian';

        return [
            $no,
            $row->biblio_id,
            trim($row->item_code ?? ''),
            trim($row->inventory_code ?? ''),
            $row->received_date ?? '',
            $asal,
            $row->price ?? 0,
            $status,
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
                $sheet->setCellValue('A1', 'DATA EKSEMPLAR BUKU — EXPORT DARI SLIMS');
                $sheet->getStyle('A1')->applyFromArray([
                    'font'      => ['bold' => true, 'size' => 13],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                $sheet->mergeCells("A2:{$lastCol}2");
                $sheet->setCellValue('A2', 'Dicetak: ' . now()->translatedFormat('d F Y, H:i') . ' WIB  |  Total: ' . $this->totalRows . ' eksemplar');
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
                $sheet->getColumnDimension('B')->setWidth(12);
                $sheet->getColumnDimension('C')->setWidth(22);
                $sheet->getColumnDimension('D')->setWidth(18);
                $sheet->getColumnDimension('E')->setWidth(15);
                $sheet->getColumnDimension('F')->setWidth(15);
                $sheet->getColumnDimension('G')->setWidth(12);
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
                        $sheet->getStyle("B{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                        $sheet->getStyle("E{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                        $sheet->getStyle("F{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                        $sheet->getStyle("G{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
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
