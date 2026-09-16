<?php

namespace App\Exceptions;

use Exception;

class InvalidNilaiUjianDataException extends Exception
{
    public static function academicYearMismatch(string $expected, string $actual): self
    {
        return new self("Mismatch academic_year_id: data nilai dikirim dengan academic_year_id '{$actual}', tetapi ujian akademik induk terdaftar pada academic_year_id '{$expected}'. Data ditolak untuk menjaga integritas historis.");
    }

    public static function semesterMismatch(string $expected, string $actual): self
    {
        return new self("Mismatch semester: data nilai dikirim dengan semester '{$actual}', tetapi ujian akademik induk terdaftar pada semester '{$expected}'. Data ditolak untuk menjaga integritas historis.");
    }
}
