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

class SlimsBukuSheet implements FromQuery, WithHeadings, WithMapping, WithStyles, WithEvents, WithTitle
{
    protected SlimsConnectionService $slimsConnection;
    protected int $totalRows = 0;

    public function __construct(SlimsConnectionService $slimsConnection)
    {
        $this->slimsConnection = $slimsConnection;
        $conn = $this->slimsConnection->getConnection();
        $this->totalRows = $conn->table('biblio')->count();
    }

    public function title(): string
    {
        return 'Buku';
    }

    public function query()
    {
        $conn = $this->slimsConnection->getConnection();
        
        return $conn->table('biblio as b')
            ->leftJoin('mst_publisher as p', 'b.publisher_id', '=', 'p.publisher_id')
            ->select(
                'b.biblio_id',
                'b.title',
                'b.isbn_issn',
                'b.publish_year',
                'b.classification',
                'p.publisher_name'
            )
            ->selectRaw('(SELECT GROUP_CONCAT(a.author_name SEPARATOR \', \') FROM biblio_author ba JOIN mst_author a ON ba.author_id = a.author_id WHERE ba.biblio_id = b.biblio_id) as penulis')
            ->selectRaw('(SELECT coll_type_id FROM item i WHERE i.biblio_id = b.biblio_id LIMIT 1) as coll_type_id')
            ->orderBy('b.biblio_id');
    }

    public function headings(): array
    {
        return [
            'No',
            'biblio_id',
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
            $row->biblio_id,
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
                $lastCol = 'I';

                $sheet->insertNewRowBefore(1, 3);

                $sheet->mergeCells("A1:{$lastCol}1");
                $sheet->setCellValue('A1', 'DATA KATALOG BUKU — EXPORT DARI SLIMS');
                $sheet->getStyle('A1')->applyFromArray([
                    'font'      => ['bold' => true, 'size' => 13],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                $sheet->mergeCells("A2:{$lastCol}2");
                $sheet->setCellValue('A2', 'Dicetak: ' . now()->translatedFormat('d F Y, H:i') . ' WIB  |  Total: ' . $this->totalRows . ' judul buku');
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
                $sheet->getColumnDimension('C')->setWidth(45);
                $sheet->getColumnDimension('D')->setWidth(16);
                $sheet->getColumnDimension('E')->setWidth(25);
                $sheet->getColumnDimension('F')->setWidth(22);
                $sheet->getColumnDimension('G')->setWidth(12);
                $sheet->getColumnDimension('H')->setWidth(18);
                $sheet->getColumnDimension('I')->setWidth(15);

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
                        $sheet->getStyle("G{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                        $sheet->getStyle("H{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                        $sheet->getStyle("I{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
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
