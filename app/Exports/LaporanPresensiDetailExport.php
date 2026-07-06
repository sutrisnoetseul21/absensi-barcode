<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;

class LaporanPresensiDetailExport implements FromQuery, WithHeadings, WithMapping, WithChunkReading
{
    use Exportable;

    public function __construct(public EloquentBuilder|QueryBuilder|\Laravel\Scout\Builder $query)
    {
    }

    public function query()
    {
        return $this->query;
    }

    public function headings(): array
    {
        return [
            'Tanggal',
            'NISN',
            'Nama Siswa',
            'Kelas',
            'Status',
            'Keterangan',
        ];
    }

    public function map($presensi): array
    {
        return [
            $presensi->date->format('d/m/Y'),
            $presensi->siswa->nisn ?? '-',
            $presensi->siswa->name ?? '-',
            $presensi->kelas->name ?? '-',
            ucfirst($presensi->status),
            $presensi->note ?? '-',
        ];
    }

    public function chunkSize(): int
    {
        return 500;
    }
}
