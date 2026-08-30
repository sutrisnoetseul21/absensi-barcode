<?php

namespace App\Exports;

use App\Models\Siswa;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class SiswaUpdateNoHpByClassExport implements FromQuery, WithHeadings, WithMapping, WithEvents, WithStyles, ShouldAutoSize
{
    protected $classId;
    protected $academicYearId;

    public function __construct($classId, $academicYearId)
    {
        $this->classId = $classId;
        $this->academicYearId = $academicYearId;
    }

    public function query()
    {
        return Siswa::query()
            ->with(['enrollmentAktif.kelas'])
            ->where('status', 'aktif')
            ->whereHas('enrollments', function ($q) {
                $q->where('class_id', $this->classId)
                  ->where('academic_year_id', $this->academicYearId)
                  ->where('status', 'aktif');
            })
            ->orderBy('name', 'asc');
    }

    public function headings(): array
    {
        return [
            'ID', // Akan disembunyikan
            'NISN',
            'NIS',
            'Nama Siswa',
            'Kelas',
            'No HP Siswa',
            'No HP Orang Tua / Wali',
        ];
    }

    public function map($siswa): array
    {
        $kelas = $siswa->enrollmentAktif?->kelas?->name ?? '-';

        return [
            $siswa->id,
            $siswa->nisn,
            $siswa->nis,
            $siswa->name,
            $kelas,
            $siswa->no_hp,
            $siswa->no_hp_orang_tua,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                // Sembunyikan kolom A (ID)
                $event->sheet->getDelegate()->getColumnDimension('A')->setVisible(false);
                
                // Beri warna latar kuning pada header (A1:E1)
                $event->sheet->getDelegate()->getStyle('A1:E1')->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'color' => ['argb' => 'FFFFE0'],
                    ]
                ]);

                // Beri warna latar hijau muda pada kolom yang diedit (F1:G1)
                $event->sheet->getDelegate()->getStyle('F1:G1')->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'color' => ['argb' => 'D1FAE5'],
                    ]
                ]);
            },
        ];
    }
}
