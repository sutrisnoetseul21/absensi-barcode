<?php

namespace App\Exports;

use App\Support\DdcHelper;
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
 *
 * PERUBAHAN v2 (2026-08-07):
 * - Sumber data diubah dari mst_topic ke biblio.classification
 *   (distinct classification dari tabel biblio, bukan topik bebas pustakawan)
 * - Kolom diubah: No | Kode DDC | Nama Kategori  (kolom "Tipe Topik" dihapus)
 * - Nama kategori di-auto-map via DdcHelper::getNamaKategori()
 * - Kolom "Tipe Topik" DIHAPUS — tidak relevan di sumber data baru
 *
 * File ini menjadi sumber untuk import DDC ke ERP via SlimsDdcImport.php
 * Kolom header yang dipakai saat import: "Kode DDC" dan "Nama Kategori"
 */
class SlimsDdcExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithEvents, WithTitle
{
    protected Collection $data;
    protected int $totalRows = 0;

    /**
     * @param \App\Services\SlimsConnectionService $slimsConnection
     */
    public function __construct(\App\Services\SlimsConnectionService $slimsConnection)
    {
        $conn = $slimsConnection->getConnection();
        
        // Ambil distinct classification dari SLiMS
        $this->data = $conn->table('biblio')
            ->select('classification')
            ->whereNotNull('classification')
            ->where('classification', '!=', '')
            ->distinct()
            ->orderBy('classification')
            ->get();
            
        $this->totalRows = $this->data->count();
    }

    public function title(): string
    {
        return 'DDC SLiMS';
    }

    public function collection(): Collection
    {
        return $this->data;
    }

    /**
     * Header kolom — nama ini WAJIB konsisten dengan yang dicek di SlimsDdcImport
     * (WithHeadingRow cocokkan berdasarkan nama kolom, bukan posisi).
     */
    public function headings(): array
    {
        return ['No', 'Kode DDC', 'Nama Kategori'];
    }

    public function map($row): array
    {
        static $no = 0;
        $no++;

        // $row->classification adalah nilai distinct dari biblio.classification
        $kodeDdc      = trim($row->classification ?? '');
        $namaKategori = DdcHelper::getNamaKategori($kodeDdc);

        return [
            $no,
            $kodeDdc,
            $namaKategori,
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
                $lastCol = 'C'; // 3 kolom: No, Kode DDC, Nama Kategori

                // Insert 3 baris header di atas data
                $sheet->insertNewRowBefore(1, 3);

                // Baris 1: Judul
                $sheet->mergeCells("A1:{$lastCol}1");
                $sheet->setCellValue('A1', 'DATA KLASIFIKASI DDC — EXPORT DARI SLIMS');
                $sheet->getStyle('A1')->applyFromArray([
                    'font'      => ['bold' => true, 'size' => 13],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                // Baris 2: Keterangan
                $sheet->mergeCells("A2:{$lastCol}2");
                $sheet->setCellValue(
                    'A2',
                    'Dicetak: ' . now()->translatedFormat('d F Y, H:i') . ' WIB  |  Total: ' . $this->totalRows . ' kode DDC'
                );
                $sheet->getStyle('A2')->applyFromArray([
                    'font'      => ['size' => 9, 'italic' => true],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                // Baris 3: Spacer
                $sheet->mergeCells("A3:{$lastCol}3");
                $sheet->getRowDimension(3)->setRowHeight(6);

                // Baris 4: Header kolom (styling)
                $sheet->getStyle("A4:{$lastCol}4")->applyFromArray([
                    'font'      => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF1E3A5F']],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical'   => Alignment::VERTICAL_CENTER,
                    ],
                    'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFDDDDDD']]],
                ]);

                // Lebar kolom
                $sheet->getColumnDimension('A')->setWidth(6);   // No
                $sheet->getColumnDimension('B')->setWidth(18);  // Kode DDC
                $sheet->getColumnDimension('C')->setWidth(45);  // Nama Kategori

                // Zebra striping data
                if ($this->totalRows > 0) {
                    $lastRow = 4 + $this->totalRows;
                    for ($row = 5; $row <= $lastRow; $row++) {
                        $bg = ($row % 2 === 0) ? 'FFF0F4FF' : 'FFFFFFFF';
                        $sheet->getStyle("A{$row}:{$lastCol}{$row}")->applyFromArray([
                            'fill'    => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => $bg]],
                            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFDDDDDD']]],
                        ]);
                        // Kolom No dan Kode DDC rata tengah
                        $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                        $sheet->getStyle("B{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
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
