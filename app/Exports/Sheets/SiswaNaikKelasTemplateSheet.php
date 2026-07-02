<?php

namespace App\Exports\Sheets;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SiswaNaikKelasTemplateSheet implements FromArray, WithHeadings, WithTitle, WithEvents
{
    protected array $students;
    protected int $classesCount;
    protected string $targetYearName;

    public function __construct(array $students, int $classesCount, string $targetYearName = '')
    {
        $this->students = $students;
        $this->classesCount = max(1, $classesCount);
        $this->targetYearName = $targetYearName;
    }

    public function array(): array
    {
        return $this->students;
    }

    public function headings(): array
    {
        $kelasBaru = $this->targetYearName ? "Kelas Baru ({$this->targetYearName})" : 'Kelas Baru';

        return [
            'NISN',
            'Nama Siswa',
            'Kelas Tingkat 7',
            'Kelas Tingkat 8',
            'Kelas Tingkat 9',
            $kelasBaru,
        ];
    }

    public function title(): string
    {
        return 'Template Naik Kelas';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                
                // Aktifkan proteksi lembar kerja
                $sheet->getProtection()->setSheet(true);

                // Determine row limit (max 500 rows, or at least length of students + 10)
                $studentsCount = count($this->students);
                $rowCount = max(500, $studentsCount + 10);

                // Buka kunci khusus untuk Kolom F (Kelas Baru) agar bisa di-input / dipilih dropdown-nya oleh admin
                $sheet->getStyle('F2:F' . $rowCount)->getProtection()->setLocked(\PhpOffice\PhpSpreadsheet\Style\Protection::PROTECTION_UNPROTECTED);

                // Column F: Kelas Baru Validation
                for ($i = 2; $i <= $rowCount; $i++) {
                    $validationF = $sheet->getCell('F' . $i)->getDataValidation();
                    $validationF->setType(DataValidation::TYPE_LIST);
                    $validationF->setErrorStyle(DataValidation::STYLE_STOP);
                    $validationF->setAllowBlank(true);
                    $validationF->setShowInputMessage(true);
                    $validationF->setShowErrorMessage(true);
                    $validationF->setShowDropDown(true);
                    $validationF->setErrorTitle('Input Error');
                    $validationF->setError('Kelas tidak ditemukan di daftar.');
                    $validationF->setPromptTitle('Pilih Kelas Baru');
                    $validationF->setPrompt('Silakan pilih kelas baru dari daftar.');
                    $validationF->setFormula1('\'ClassesList\'!$A$1:$A$' . $this->classesCount);
                }

                // Set Column Widths
                $sheet->getColumnDimension('A')->setWidth(20);
                $sheet->getColumnDimension('B')->setWidth(30);
                $sheet->getColumnDimension('C')->setWidth(20);
                $sheet->getColumnDimension('D')->setWidth(20);
                $sheet->getColumnDimension('E')->setWidth(20);
                $sheet->getColumnDimension('F')->setWidth(20);
            },
        ];
    }
}
