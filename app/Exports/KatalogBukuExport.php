<?php

namespace App\Exports;

use App\Models\Buku;
use App\Models\KategoriBuku;
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

class KatalogBukuExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithEvents
{
    protected Collection $bukus;
    protected ?PengaturanSekolah $settings;
    protected array $kategoriIds;
    protected array $mapelIds;
    protected int $totalRows = 0;

    public function __construct(array $kategoriIds = [], array $mapelIds = [])
    {
        $this->kategoriIds = $kategoriIds;
        $this->mapelIds    = $mapelIds;
        $this->settings    = PengaturanSekolah::current();
    }

    public function collection(): Collection
    {
        $query = Buku::with(['kategoriBuku', 'klasifikasiDdc', 'mataPelajaran', 'eksemplarBukus'])
            ->when(!empty($this->kategoriIds), fn ($q) => $q->whereIn('kategori_id', $this->kategoriIds))
            ->when(!empty($this->mapelIds), fn ($q) => $q->whereIn('mapel_id', $this->mapelIds))
            ->orderBy('judul');

        $result = $query->get();
        $this->totalRows = $result->count();

        return $result;
    }

    public function headings(): array
    {
        return [
            'No',
            'Judul',
            'Koleksi',
            'Penulis',
            'Penerbit',
            'Tahun Terbit',
            'ISBN',
            'Kode DDC',
            'Jenjang',
            'Lokasi Rak',
            'Total Eksemplar',
            'Tersedia',
        ];
    }

    public function map($buku): array
    {
        static $no = 0;
        $no++;

        $total    = $buku->eksemplarBukus->count();
        $tersedia = $buku->eksemplarBukus->where('status', 'tersedia')->count();

        $jenjang = match ((string) $buku->grade_level) {
            '7'     => 'Kelas 7',
            '8'     => 'Kelas 8',
            '9'     => 'Kelas 9',
            '10'    => 'Kelas 10',
            '11'    => 'Kelas 11',
            '12'    => 'Kelas 12',
            default => 'Semua Jenjang',
        };

        return [
            $no,
            $buku->judul,
            $buku->kategoriBuku?->nama_kategori ?? '-',
            $buku->penulis ?? '-',
            $buku->penerbit ?? '-',
            $buku->tahun_terbit ?? '-',
            $buku->isbn ?? '-',
            $buku->klasifikasiDdc ? ($buku->klasifikasiDdc->kode_ddc . ' - ' . $buku->klasifikasiDdc->kategori) : '-',
            $jenjang,
            $buku->lokasi_rak ?? '-',
            $total,
            $tersedia,
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
                $lastCol = 'L'; // kolom ke-12

                // ===== INSERT 4 BARIS DI ATAS untuk kop surat =====
                $sheet->insertNewRowBefore(1, 4);

                // Baris 1: KATALOG BUKU (judul dokumen)
                $sheet->mergeCells("A1:{$lastCol}1");
                $sheet->setCellValue('A1', 'KATALOG BUKU');
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

                // Baris 5: Header kolom (bold, background biru gelap)
                $sheet->getStyle("A5:{$lastCol}5")->applyFromArray([
                    'font'      => ['bold' => true, 'size' => 10, 'color' => ['argb' => 'FFFFFFFF']],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF1E3A5F']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
                    'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFDDDDDD']]],
                ]);
                $sheet->getRowDimension(5)->setRowHeight(22);

                // Lebar kolom
                $sheet->getColumnDimension('A')->setWidth(5);   // No
                $sheet->getColumnDimension('B')->setWidth(40);  // Judul
                $sheet->getColumnDimension('C')->setWidth(14);  // Koleksi
                $sheet->getColumnDimension('D')->setWidth(20);  // Penulis
                $sheet->getColumnDimension('E')->setWidth(18);  // Penerbit
                $sheet->getColumnDimension('F')->setWidth(10);  // Tahun Terbit
                $sheet->getColumnDimension('G')->setWidth(16);  // ISBN
                $sheet->getColumnDimension('H')->setWidth(22);  // DDC
                $sheet->getColumnDimension('I')->setWidth(12);  // Jenjang
                $sheet->getColumnDimension('J')->setWidth(12);  // Lokasi Rak
                $sheet->getColumnDimension('K')->setWidth(12);  // Total Eksemplar
                $sheet->getColumnDimension('L')->setWidth(10);  // Tersedia

                // Styling baris data (zebra striping)
                if ($this->totalRows > 0) {
                    $lastDataRow = 5 + $this->totalRows;
                    for ($row = 6; $row <= $lastDataRow; $row++) {
                        $isEven = ($row % 2 === 0);
                        $bg     = $isEven ? 'FFF0F4FF' : 'FFFFFFFF';
                        $sheet->getStyle("A{$row}:{$lastCol}{$row}")->applyFromArray([
                            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => $bg]],
                            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFDDDDDD']]],
                            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => false],
                        ]);
                        // Center kolom numerik
                        $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                        $sheet->getStyle("F{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                        $sheet->getStyle("I{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                        $sheet->getStyle("J{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                        $sheet->getStyle("K{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                        $sheet->getStyle("L{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    }

                    // Border luar tabel
                    $sheet->getStyle("A5:{$lastCol}{$lastDataRow}")->applyFromArray([
                        'borders' => ['outline' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['argb' => 'FF1E3A5F']]],
                    ]);
                }

                // Freeze header row
                $sheet->freezePane('A6');
            },
        ];
    }
}
