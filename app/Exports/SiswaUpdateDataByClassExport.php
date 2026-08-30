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

class SiswaUpdateDataByClassExport implements FromQuery, WithHeadings, WithMapping, WithEvents, WithStyles, ShouldAutoSize
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
            'Tempat Lahir',
            'Tanggal Lahir (DD-MM-YYYY)',
            'Jenis Kelamin (L/P)',
            'Agama',
            'Alamat',
            'No HP Siswa',
            'Asal Sekolah',
            'Tanggal Masuk (DD-MM-YYYY)',
            'Kelas Masuk',
            'Status Keluarga',
            'Anak Ke-',
            'Nama Ayah',
            'Pekerjaan Ayah',
            'Nama Ibu',
            'Pekerjaan Ibu',
            'Nama Wali',
            'Pekerjaan Wali',
            'No HP Ortu / Wali',
        ];
    }

    public function map($siswa): array
    {
        return [
            $siswa->id,
            $siswa->nisn,
            $siswa->nis,
            $siswa->name,
            $siswa->birth_place,
            $siswa->birth_date ? $siswa->birth_date->format('d-m-Y') : null,
            $siswa->gender,
            $siswa->religion,
            $siswa->address,
            $siswa->no_hp,
            $siswa->previous_school,
            $siswa->admission_date ? $siswa->admission_date->format('d-m-Y') : null,
            $siswa->admission_class,
            $siswa->family_status,
            $siswa->child_order,
            $siswa->nama_ayah,
            $siswa->pekerjaan_ayah,
            $siswa->nama_ibu,
            $siswa->pekerjaan_ibu,
            $siswa->nama_wali,
            $siswa->pekerjaan_wali,
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
                
                // Beri warna latar kuning pada header untuk membedakan
                $event->sheet->getDelegate()->getStyle('A1:V1')->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'color' => ['argb' => 'FFFFE0'],
                    ]
                ]);
            },
        ];
    }
}
