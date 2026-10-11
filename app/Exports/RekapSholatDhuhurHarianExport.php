<?php

namespace App\Exports;

use App\Exports\Sheets\RekapSholatDhuhurBelumSheet;
use App\Exports\Sheets\RekapSholatDhuhurDetailSheet;
use App\Exports\Sheets\RekapSholatDhuhurKelasSheet;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class RekapSholatDhuhurHarianExport implements WithMultipleSheets
{
    public function __construct(
        protected string $selectedDate,
        protected ?string $academicYearId,
        protected array $stats,
        protected array $classesSummary,
        protected array $unattendedStudents,
        protected array $allStudents,
        protected mixed $koordinator = null
    ) {}

    public function sheets(): array
    {
        return [
            new RekapSholatDhuhurKelasSheet(
                $this->selectedDate,
                $this->academicYearId,
                $this->stats,
                $this->classesSummary,
                $this->koordinator
            ),
            new RekapSholatDhuhurBelumSheet(
                $this->selectedDate,
                $this->academicYearId,
                $this->unattendedStudents,
                $this->koordinator
            ),
            new RekapSholatDhuhurDetailSheet(
                $this->selectedDate,
                $this->academicYearId,
                $this->allStudents,
                $this->koordinator
            ),
        ];
    }
}
