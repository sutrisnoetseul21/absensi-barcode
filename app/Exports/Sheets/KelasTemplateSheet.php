<?php

namespace App\Exports\Sheets;

use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;

class KelasTemplateSheet implements WithHeadings, WithTitle, WithEvents
{
    protected int $guruCount;

    public function __construct(int $guruCount)
    {
        $this->guruCount = $guruCount;
    }

    public function headings(): array
    {
        return [
            'Nama Kelas',
            'Tingkat (7, 8, 9)',
            'Wali Kelas (Opsional)',
        ];
    }

    public function title(): string
    {
        return 'Template Kelas';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $rowCount = 34; // Apply validation to the first 34 rows (33 kelas + 1 header)

                // Column B: Tingkat Validation
                for ($i = 2; $i <= $rowCount; $i++) {
                    $validationB = $sheet->getCell('B' . $i)->getDataValidation();
                    $validationB->setType(DataValidation::TYPE_LIST);
                    $validationB->setErrorStyle(DataValidation::STYLE_STOP);
                    $validationB->setAllowBlank(true);
                    $validationB->setShowInputMessage(true);
                    $validationB->setShowErrorMessage(true);
                    $validationB->setShowDropDown(true);
                    $validationB->setErrorTitle('Input Error');
                    $validationB->setError('Hanya boleh mengisi angka 7, 8, atau 9.');
                    $validationB->setPromptTitle('Pilih Tingkat');
                    $validationB->setPrompt('Silakan pilih tingkat kelas dari daftar.');
                    $validationB->setFormula1('"7,8,9"');
                }

                // Column C: Wali Kelas Validation (from hidden TeachersList sheet)
                if ($this->guruCount > 0) {
                    for ($i = 2; $i <= $rowCount; $i++) {
                        $validationC = $sheet->getCell('C' . $i)->getDataValidation();
                        $validationC->setType(DataValidation::TYPE_LIST);
                        $validationC->setErrorStyle(DataValidation::STYLE_STOP);
                        $validationC->setAllowBlank(true);
                        $validationC->setShowInputMessage(true);
                        $validationC->setShowErrorMessage(true);
                        $validationC->setShowDropDown(true);
                        $validationC->setErrorTitle('Input Error');
                        $validationC->setError('Nama guru tidak ditemukan di daftar.');
                        $validationC->setPromptTitle('Pilih Wali Kelas');
                        $validationC->setPrompt('Silakan pilih dari dropdown (berisi nama guru di sistem).');
                        
                        // Reference the TeachersList sheet A1:A{count}
                        $validationC->setFormula1('\'TeachersList\'!$A$1:$A$' . $this->guruCount);
                    }
                }

                // Set Column Widths
                $sheet->getColumnDimension('A')->setWidth(20);
                $sheet->getColumnDimension('B')->setWidth(20);
                $sheet->getColumnDimension('C')->setWidth(35);
            },
        ];
    }
}
