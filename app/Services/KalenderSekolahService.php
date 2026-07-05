<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\PengaturanSekolah;
use App\Models\HariLibur;
use Illuminate\Support\Facades\Cache;

class KalenderSekolahService
{
    /**
     * Mengecek apakah sebuah tanggal merupakan hari efektif belajar.
     */
    public function isHariSekolah(Carbon $date, ?string $classId = null): bool
    {
        $settings = PengaturanSekolah::current();
        $workDaysType = $settings->work_days_type ?? '5_hari';

        // 1. Cek Hari Libur Rutin (Weekend)
        if ($workDaysType === '5_hari' && $date->isWeekend()) {
            return false;
        }

        if ($workDaysType === '6_hari' && $date->isSunday()) {
            return false;
        }

        // 2. Cek Hari Libur Nasional / Cuti Bersama / Khusus Kelas
        $isHoliday = HariLibur::hariIni($date->toDateString(), $classId)->exists();
        if ($isHoliday) {
            return false;
        }

        return true;
    }

    /**
     * Menghitung total hari efektif dalam rentang waktu tertentu.
     */
    public function getEffectiveDays(Carbon $start, Carbon $end, ?string $classId = null): int
    {
        $effectiveDays = 0;
        $current = $start->copy();

        $settings = PengaturanSekolah::current();
        $workDaysType = $settings->work_days_type ?? '5_hari';

        // Ambil semua hari libur dalam range ini untuk efisiensi query
        $holidays = HariLibur::where('start_date', '<=', $end->toDateString())
            ->where(function ($q) use ($start) {
                $q->whereNull('end_date')->orWhere('end_date', '>=', $start->toDateString());
            })
            ->where(function ($q) use ($classId) {
                $q->whereNull('class_id');
                if ($classId) {
                    $q->orWhere('class_id', $classId);
                }
            })
            ->get();

        while ($current->lessThanOrEqualTo($end)) {
            $isWeekend = ($workDaysType === '5_hari') ? $current->isWeekend() : $current->isSunday();
            
            if (!$isWeekend) {
                $isHoliday = false;
                foreach ($holidays as $holiday) {
                    if ($holiday->meliputiTanggal($current->toDateString())) {
                        $isHoliday = true;
                        break;
                    }
                }

                if (!$isHoliday) {
                    $effectiveDays++;
                }
            }
            $current->addDay();
        }

        return $effectiveDays;
    }
}
