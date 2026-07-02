<?php

namespace App\Exports;

use App\Exports\Sheets\ClassesListSheet;
use App\Exports\Sheets\SiswaNaikKelasTemplateSheet;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class SiswaNaikKelasExport implements WithMultipleSheets
{
    use Exportable;

    protected string $sourceAcademicYearId;
    protected string $targetAcademicYearId;

    public function __construct(string $sourceAcademicYearId, string $targetAcademicYearId)
    {
        $this->sourceAcademicYearId = $sourceAcademicYearId;
        $this->targetAcademicYearId = $targetAcademicYearId;
    }

    public function sheets(): array
    {
        // Query siswa yang aktif di TP asal dan bukan kelas 9
        $query = Siswa::query()
            ->whereHas('enrollments', function ($q) {
                $q->where('academic_year_id', $this->sourceAcademicYearId)
                  ->where('status', 'aktif')
                  ->whereHas('kelas', function ($qk) {
                      $qk->where('grade_level', '!=', 9);
                  });
            });

        // Kecualikan siswa yang sudah terdaftar di target tahun ajaran
        $query->whereDoesntHave('enrollments', function ($q) {
            $q->where('academic_year_id', $this->targetAcademicYearId);
        });

        $students = $query->get()->map(function (Siswa $student) {
            $enrollment7 = $student->enrollments()->with(['kelas', 'tahunAjaran'])->whereHas('kelas', fn($q) => $q->where('grade_level', 7))->first();
            $enrollment8 = $student->enrollments()->with(['kelas', 'tahunAjaran'])->whereHas('kelas', fn($q) => $q->where('grade_level', 8))->first();
            $enrollment9 = $student->enrollments()->with(['kelas', 'tahunAjaran'])->whereHas('kelas', fn($q) => $q->where('grade_level', 9))->first();

            $formatKelas = function ($enrollment) {
                if (!$enrollment || !$enrollment->kelas) return '—';
                $year = $enrollment->tahunAjaran?->name ?? '';
                return $year ? "{$enrollment->kelas->name} ({$year})" : $enrollment->kelas->name;
            };

            return [
                'nisn'    => $student->nisn,
                'name'    => $student->name,
                'class_7' => $formatKelas($enrollment7),
                'class_8' => $formatKelas($enrollment8),
                'class_9' => $formatKelas($enrollment9),
            ];
        })->toArray();

        $classes = Kelas::pluck('name')->toArray();
        $targetYearName = TahunAjaran::find($this->targetAcademicYearId)?->name ?? '';

        return [
            new SiswaNaikKelasTemplateSheet($students, count($classes), $targetYearName),
            new ClassesListSheet($classes),
        ];
    }
}
