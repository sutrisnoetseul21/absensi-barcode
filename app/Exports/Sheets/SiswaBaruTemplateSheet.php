<?php

namespace App\Exports\Sheets;

use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SiswaBaruTemplateSheet implements WithHeadings, WithTitle, WithEvents
{
    protected int $classesCount;

    public function __construct(int $classesCount)
    {
        $this->classesCount = max(1, $classesCount);
    }

    public function headings(): array
    {
        return [
            'NISN',
            'Nama Siswa',
            'Password (Opsional - Default: password)',
            'Kelas',
        ];
    }

    public function title(): string
    {
        return 'Template Siswa Baru';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $rowCount = 500; // Apply validation to 500 rows

                // Column D: Kelas Validation
                for ($i = 2; $i <= $rowCount; $i++) {
                    $validationD = $sheet->getCell('D' . $i)->getDataValidation();
                    $validationD->setType(DataValidation::TYPE_LIST);
                    $validationD->setErrorStyle(DataValidation::STYLE_STOP);
                    $validationD->setAllowBlank(true);
                    $validationD->setShowInputMessage(true);
                    $validationD->setShowErrorMessage(true);
                    $validationD->setShowDropDown(true);
                    $validationD->setErrorTitle('Input Error');
                    $validationD->setError('Kelas tidak ditemukan di daftar.');
                    $validationD->setPromptTitle('Pilih Kelas');
                    $validationD->setPrompt('Silakan pilih kelas dari daftar.');
                    $validationD->setFormula1('\'ClassesList\'!$A$1:$A$' . $this->classesCount);
                }

                // Set Column Widths
                $sheet->getColumnDimension('A')->setWidth(20);
                $sheet->getColumnDimension('B')->setWidth(30);
                $sheet->getColumnDimension('C')->setWidth(35);
                $sheet->getColumnDimension('D')->setWidth(20);
            },
        ];
    }
}
