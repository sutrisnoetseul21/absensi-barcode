<?php

namespace App\Exports;

use App\Exports\Sheets\RekapPresensiHarianBelumSheet;
use App\Exports\Sheets\RekapPresensiHarianDetailSheet;
use App\Exports\Sheets\RekapPresensiHarianKelasSheet;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class RekapPresensiHarianExport implements WithMultipleSheets
{
    public function __construct(
        protected string $selectedDate,
        protected ?string $academicYearId,
        protected array $stats,
        protected array $classesSummary,
        protected array $unattendedStudents,
        protected array $allStudents
    ) {}

    public function sheets(): array
    {
        return [
            new RekapPresensiHarianKelasSheet(
                $this->selectedDate,
                $this->academicYearId,
                $this->stats,
                $this->classesSummary
            ),
            new RekapPresensiHarianBelumSheet(
                $this->selectedDate,
                $this->academicYearId,
                $this->unattendedStudents
            ),
            new RekapPresensiHarianDetailSheet(
                $this->selectedDate,
                $this->academicYearId,
                $this->allStudents
            ),
        ];
    }
}
