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
            'NIS',
            'Nama Siswa',
            'Tempat Lahir',
            'Tanggal Lahir (YYYY-MM-DD)',
            'Alamat',
            'Password (Kosongkan untuk default: NISN)',
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

                // Column H: Kelas Validation
                for ($i = 2; $i <= $rowCount; $i++) {
                    $validationG = $sheet->getCell('H' . $i)->getDataValidation();
                    $validationG->setType(DataValidation::TYPE_LIST);
                    $validationG->setErrorStyle(DataValidation::STYLE_STOP);
                    $validationG->setAllowBlank(true);
                    $validationG->setShowInputMessage(true);
                    $validationG->setShowErrorMessage(true);
                    $validationG->setShowDropDown(true);
                    $validationG->setErrorTitle('Input Error');
                    $validationG->setError('Kelas tidak ditemukan di daftar.');
                    $validationG->setPromptTitle('Pilih Kelas');
                    $validationG->setPrompt('Silakan pilih kelas dari daftar.');
                    $validationG->setFormula1('\'ClassesList\'!$A$1:$A$' . $this->classesCount);
                }

                // Set Column Widths
                $sheet->getColumnDimension('A')->setWidth(20);
                $sheet->getColumnDimension('B')->setWidth(20);
                $sheet->getColumnDimension('C')->setWidth(30);
                $sheet->getColumnDimension('D')->setWidth(20);
                $sheet->getColumnDimension('E')->setWidth(20);
                $sheet->getColumnDimension('F')->setWidth(40);
                $sheet->getColumnDimension('G')->setWidth(40);
                $sheet->getColumnDimension('H')->setWidth(20);
            },
        ];
    }
}
