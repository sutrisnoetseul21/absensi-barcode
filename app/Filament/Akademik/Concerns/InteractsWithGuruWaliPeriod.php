<?php

namespace App\Filament\Akademik\Concerns;

use App\Models\PengaturanSekolah;
use App\Models\TahunAjaran;
use Carbon\Carbon;

trait InteractsWithGuruWaliPeriod
{
    public static function getActiveGuruWaliAcademicYear(): ?TahunAjaran
    {
        $activeId = PengaturanSekolah::current()?->academic_year_id_active;
        if ($activeId) {
            $ay = TahunAjaran::find($activeId);
            if ($ay) {
                return $ay;
            }
        }

        return TahunAjaran::where('status', 'aktif')->first()
            ?? TahunAjaran::orderBy('start_year', 'desc')->first();
    }

    public static function getAllGuruWaliAcademicYears(): array
    {
        $activeId = self::getActiveGuruWaliAcademicYear()?->id;

        return TahunAjaran::orderBy('start_year', 'desc')
            ->get()
            ->mapWithKeys(fn ($ay) => [
                $ay->id => $ay->name . ($ay->id === $activeId || $ay->status === 'aktif' ? ' (Aktif)' : ''),
            ])
            ->toArray();
    }

    public static function buildGuruWaliPeriodOptions(?TahunAjaran $ay, bool $includeTahunan = true, bool $includeLintasTahun = false): array
    {
        $options = [];

        if ($includeLintasTahun) {
            $options['lintas_tahun'] = 'Semua Tahun Ajaran (Lintas Tahun)';
        }

        if ($includeTahunan) {
            $options['tahunan'] = '1 Tahun Ajaran Penuh (Tahunan)';
        }

        $options['semester_1'] = 'Semester 1 / Ganjil (Juli – Desember)';
        $options['semester_2'] = 'Semester 2 / Genap (Januari – Juni)';

        $sy = $ay?->start_year ?: (int) date('Y');
        $ey = $ay?->end_year ?: ($sy + 1);
        $monthNames = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];

        for ($m = 7; $m <= 12; $m++) {
            $options[sprintf('%02d-%d', $m, $sy)] = 'Per Bulan: ' . $monthNames[$m] . ' ' . $sy;
        }
        for ($m = 1; $m <= 6; $m++) {
            $options[sprintf('%02d-%d', $m, $ey)] = 'Per Bulan: ' . $monthNames[$m] . ' ' . $ey;
        }

        return $options;
    }

    /**
     * @return array{0: Carbon, 1: Carbon, 2: string} [startDate, endDate, label]
     */
    public static function resolveGuruWaliDateRange(string $period, ?TahunAjaran $ay): array
    {
        if ($period === 'lintas_tahun') {
            return [
                Carbon::create(2000, 1, 1)->startOfDay(),
                Carbon::create(2099, 12, 31)->endOfDay(),
                'Semua Tahun Ajaran (Lintas Tahun)',
            ];
        }

        $sy = $ay?->start_year ?: (int) date('Y');
        $ey = $ay?->end_year ?: ($sy + 1);

        if (in_array($period, ['semester_1', 'semester_ganjil'], true)) {
            return [
                Carbon::create($sy, 7, 1)->startOfDay(),
                Carbon::create($sy, 12, 31)->endOfDay(),
                'Semester 1 (Ganjil)',
            ];
        }

        if (in_array($period, ['semester_2', 'semester_genap'], true)) {
            return [
                Carbon::create($ey, 1, 1)->startOfDay(),
                Carbon::create($ey, 6, 30)->endOfDay(),
                'Semester 2 (Genap)',
            ];
        }

        if (preg_match('/^(\d{2})-(\d{4})$/', $period, $m)) {
            $start = Carbon::create((int) $m[2], (int) $m[1], 1)->startOfMonth();
            $end = $start->copy()->endOfMonth();

            return [
                $start,
                $end,
                'Bulan ' . $start->translatedFormat('F Y'),
            ];
        }

        return [
            Carbon::create($sy, 7, 1)->startOfDay(),
            Carbon::create($ey, 6, 30)->endOfDay(),
            '1 Tahun Ajaran Penuh (Tahunan)',
        ];
    }
}

