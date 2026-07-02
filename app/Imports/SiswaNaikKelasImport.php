<?php

namespace App\Imports;

use App\Models\EnrollmentSiswa;
use App\Models\Kelas;
use App\Models\Siswa;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class SiswaNaikKelasImport implements ToCollection
{
    protected string $sourceAcademicYearId;
    protected string $targetAcademicYearId;

    public function __construct(string $sourceAcademicYearId, string $targetAcademicYearId)
    {
        $this->sourceAcademicYearId = $sourceAcademicYearId;
        $this->targetAcademicYearId = $targetAcademicYearId;
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $index => $row) {
            if ($index === 0) {
                continue; // skip header
            }

            $nisn = trim((string) ($row[0] ?? ''));
            $className = trim((string) ($row[5] ?? '')); // Kolom Kelas Baru (Kolom F)

            if ($nisn === '' || $className === '') {
                continue; // skip incomplete rows
            }

            // Find existing student
            $student = Siswa::where('nisn', $nisn)->first();
            if (!$student) {
                continue;
            }

            // Find new class
            $kelas = Kelas::where('name', $className)->first();
            if (!$kelas) {
                continue;
            }

            // Update status enrollment lama (TP asal) menjadi 'naik'
            $oldEnrollment = EnrollmentSiswa::where('student_id', $student->id)
                ->where('academic_year_id', $this->sourceAcademicYearId)
                ->first();

            if ($oldEnrollment) {
                $oldEnrollment->update(['status' => 'naik']);
            }

            // Daftarkan siswa ke target tahun ajaran (naik kelas)
            EnrollmentSiswa::updateOrCreate(
                [
                    'student_id'       => $student->id,
                    'academic_year_id' => $this->targetAcademicYearId,
                ],
                [
                    'class_id' => $kelas->id,
                    'status'   => 'aktif',
                ]
            );
        }
    }
}
