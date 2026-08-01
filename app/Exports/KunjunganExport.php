<?php

namespace App\Exports;

use App\Models\KunjunganPerpustakaan;
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

class KunjunganExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithEvents
{
    protected Collection $kunjungan;
    protected ?PengaturanSekolah $settings;
    protected ?string $startDate;
    protected ?string $endDate;
    protected array $tipeAnggotaFilter;
    protected int $totalRows = 0;

    public function __construct(?string $startDate = null, ?string $endDate = null, array $tipeAnggotaFilter = [])
    {
        $this->startDate         = $startDate;
        $this->endDate           = $endDate;
        $this->tipeAnggotaFilter = $tipeAnggotaFilter;
        $this->settings          = PengaturanSekolah::current();
    }

    public function collection(): Collection
    {
        $query = KunjunganPerpustakaan::with(['pengunjung'])
            ->when($this->startDate, fn ($q) => $q->where('tanggal', '>=', $this->startDate))
            ->when($this->endDate, fn ($q) => $q->where('tanggal', '<=', $this->endDate))
            ->when(!empty($this->tipeAnggotaFilter), fn ($q) => $q->whereIn('pengunjung_type', $this->tipeAnggotaFilter))
            ->orderBy('tanggal', 'desc')
            ->orderBy('waktu_masuk', 'desc');

        $result = $query->get();
        $this->totalRows = $result->count();

        return $result;
    }

    public function headings(): array
    {
        return [
            'No',
            'Tanggal',
            'Waktu Masuk',
            'Nama Pengunjung',
            'Tipe Anggota',
            'Tujuan Kunjungan',
            'Catatan',
        ];
    }

    public function map($kun): array
    {
        static $no = 0;
        $no++;

        $tipeAnggota = match ((string) $kun->pengunjung_type) {
            'siswa' => 'Siswa',
            'guru'  => 'Guru / Staff',
            default => ucfirst($kun->pengunjung_type ?? '-'),
        };
        
        // Add class info if siswa
        $namaPengunjung = $kun->pengunjung?->name ?? '-';
        if ($kun->pengunjung_type === 'siswa' && $kun->pengunjung && $kun->pengunjung->enrollmentAktif) {
            $namaPengunjung .= ' (Kelas ' . $kun->pengunjung->enrollmentAktif->kelas->name . ')';
        }

        return [
            $no,
            $kun->tanggal ? $kun->tanggal->format('d/m/Y') : '-',
            $kun->waktu_masuk ?? '-',
            $namaPengunjung,
            $tipeAnggota,
            $kun->tujuan_kunjungan ?? '-',
            $kun->catatan ?? '-',
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
                $lastCol = 'G'; // 7 kolom

                // ===== INSERT 4 BARIS DI ATAS untuk kop surat =====
                $sheet->insertNewRowBefore(1, 4);

                // Baris 1: DATA KUNJUNGAN PERPUSTAKAAN
                $sheet->mergeCells("A1:{$lastCol}1");
                $sheet->setCellValue('A1', 'REKAPITULASI KUNJUNGAN PERPUSTAKAAN');
                $sheet->getStyle('A1')->applyFromArray([
                    'font'      => ['bold' => true, 'size' => 14],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                // Baris 2: Nama Sekolah (kiri) | Tanggal Cetak (kanan)
                $sheet->mergeCells("A2:E2");
                $sheet->setCellValue('A2', strtoupper($sekolah?->school_name ?? 'NAMA SEKOLAH'));
                $sheet->getStyle('A2')->applyFromArray([
                    'font'      => ['bold' => true, 'size' => 11],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
                ]);
                $sheet->mergeCells("F2:{$lastCol}2");
                $sheet->setCellValue('F2', 'Dicetak: ' . $tanggalCetak);
                $sheet->getStyle('F2')->applyFromArray([
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
                $sheet->getColumnDimension('B')->setWidth(14);  // Tanggal
                $sheet->getColumnDimension('C')->setWidth(14);  // Waktu
                $sheet->getColumnDimension('D')->setWidth(40);  // Nama
                $sheet->getColumnDimension('E')->setWidth(16);  // Tipe
                $sheet->getColumnDimension('F')->setWidth(25);  // Tujuan
                $sheet->getColumnDimension('G')->setWidth(30);  // Catatan

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
                        foreach (['A', 'B', 'C', 'E'] as $col) {
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
