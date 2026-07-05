<?php

namespace App\Exports;

use App\Models\Presensi;
use App\Models\EnrollmentSiswa;
use App\Models\HariLibur;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PresensiMatrixExport implements FromArray, WithHeadings, WithStyles
{
    protected string $classId;
    protected string $yearId;
    protected string $month;
    protected string $year;
    
    protected array $daysInMonth;
    protected array $holidays;

    public function __construct(string $classId, string $yearId, string $month, string $year)
    {
        $this->classId = $classId;
        $this->yearId = $yearId;
        $this->month = $month;
        $this->year = $year;
        
        $daysInMonthCount = Carbon::create($year, $month, 1)->daysInMonth;
        $this->daysInMonth = range(1, $daysInMonthCount);
    }

    public function headings(): array
    {
        $headings = ['No', 'NISN', 'Nama'];
        
        foreach ($this->daysInMonth as $day) {
            $headings[] = (string) $day;
        }
        
        return array_merge($headings, ['Total H', 'Total T', 'Total S', 'Total I', 'Total A']);
    }

    public function array(): array
    {
        // Ambil siswa yang aktif di kelas ini pada tahun ajaran ini
        $enrollments = EnrollmentSiswa::with('siswa')
            ->where('class_id', $this->classId)
            ->where('academic_year_id', $this->yearId)
            ->where('status', 'aktif')
            ->get()
            ->sortBy(function($enrollment) {
                return $enrollment->siswa->name ?? '';
            });

        $matrix = [];
        $no = 1;

        $kalenderService = app(\App\Services\KalenderSekolahService::class);

        foreach ($enrollments as $enrollment) {
            $siswa = $enrollment->siswa;
            if (!$siswa) continue;

            $row = [
                $no++,
                $siswa->nisn,
                $siswa->name,
            ];

            // Ambil semua presensi siswa di bulan dan tahun ini
            $presensiData = Presensi::where('student_id', $siswa->id)
                ->whereYear('date', $this->year)
                ->whereMonth('date', $this->month)
                ->get()
                ->keyBy(function($item) {
                    return Carbon::parse($item->date)->format('Y-m-d');
                });

            $totals = [
                'Hadir' => 0,
                'Terlambat' => 0,
                'Sakit' => 0,
                'Izin' => 0,
                'Alpa' => 0,
            ];

            foreach ($this->daysInMonth as $day) {
                $dateStr = sprintf('%04d-%02d-%02d', $this->year, $this->month, $day);
                $carbonDate = Carbon::createFromFormat('Y-m-d', $dateStr);
                
                // Cek hari libur / weekend via service terpusat
                if (!$kalenderService->isHariSekolah($carbonDate, $this->classId)) {
                    $row[] = 'L'; // Menggunakan tanda L sesuai instruksi terbaru
                    continue;
                }

                if (isset($presensiData[$dateStr])) {
                    $status = $presensiData[$dateStr]->status;
                    $initial = match ($status) {
                        'hadir' => 'H',
                        'terlambat' => 'T',
                        'sakit' => 'S',
                        'izin' => 'I',
                        'alpa' => 'A',
                        default => '?'
                    };
                    $row[] = $initial;
                    
                    if ($status === 'hadir') $totals['Hadir']++;
                    elseif ($status === 'terlambat') $totals['Terlambat']++;
                    elseif ($status === 'sakit') $totals['Sakit']++;
                    elseif ($status === 'izin') $totals['Izin']++;
                    elseif ($status === 'alpa') $totals['Alpa']++;
                } else {
                    $row[] = 'A'; // Jika tidak ada record dan bukan libur, default Alpa
                    $totals['Alpa']++;
                }
            }

            // Append totals
            $row[] = $totals['Hadir'];
            $row[] = $totals['Terlambat'];
            $row[] = $totals['Sakit'];
            $row[] = $totals['Izin'];
            $row[] = $totals['Alpa'];

            $matrix[] = $row;
        }

        return $matrix;
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
