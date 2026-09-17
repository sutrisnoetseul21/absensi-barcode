<?php

namespace App\Exports;

use App\Models\NilaiUjian;
use App\Models\Siswa;
use App\Models\UjianAkademik;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;

class DaftarNilaiExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize, WithTitle
{
    protected $ujianId;
    protected $classId;
    protected $academicYearId;
    protected $kkm;

    public function __construct($ujianId, $classId, $academicYearId, $kkm)
    {
        $this->ujianId = $ujianId;
        $this->classId = $classId;
        $this->academicYearId = $academicYearId;
        $this->kkm = $kkm;
    }

    public function collection()
    {
        $studentIds = \App\Models\EnrollmentSiswa::where('class_id', $this->classId)
            ->where('academic_year_id', $this->academicYearId)
            ->where('status', 'aktif')
            ->pluck('student_id');

        return Siswa::whereIn('id', $studentIds)
            ->with(['nilaiUjians' => function ($query) {
                $query->where('ujian_akademik_id', $this->ujianId);
            }])
            ->orderBy('name')
            ->get();
    }

    public function headings(): array
    {
        return [
            'No',
            'NISN',
            'NIS',
            'Nama Siswa',
            'Nilai Akhir',
            'KKM',
            'Status',
            'Benar',
            'Salah',
            'Kosong',
            'Pelanggaran'
        ];
    }

    public function map($siswa): array
    {
        static $no = 1;
        $nilai = $siswa->nilaiUjians->first();

        return [
            $no++,
            $siswa->nisn ?? '-',
            $siswa->nis ?? '-',
            $siswa->name,
            $nilai ? number_format($nilai->nilai_akhir, 2) : '-',
            $this->kkm,
            $nilai ? ($nilai->is_tuntas ? 'Tuntas' : 'Belum Tuntas') : 'Belum Ujian',
            $nilai ? $nilai->jumlah_benar : '-',
            $nilai ? $nilai->jumlah_salah : '-',
            $nilai ? $nilai->jumlah_kosong : '-',
            $nilai ? $nilai->violation_count : '-'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    public function title(): string
    {
        return 'Rekap Nilai';
    }
}
