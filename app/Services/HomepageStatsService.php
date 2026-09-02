<?php

namespace App\Services;

use App\Models\Presensi;
use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\Guru;
use App\Models\WebSetting;
use Illuminate\Support\Facades\Cache;

class HomepageStatsService
{
    /**
     * Mendapatkan jumlah siswa yang sudah melakukan scan presensi hari ini.
     * Menggunakan cache 90 detik.
     */
    public function getPresensiHariIni(): int
    {
        $tanggal = today()->toDateString();
        $cacheKey = "homepage:presensi:{$tanggal}";

        return Cache::remember($cacheKey, 90, function () use ($tanggal) {
            return Presensi::whereDate('date', $tanggal)
                ->whereNotNull('scan_time')
                ->count();
        });
    }

    /**
     * Mendapatkan statistik data master (Siswa aktif, Rombel, Pendidik, Tendik).
     * Menggunakan cache 30 menit.
     */
    public function getStatistikMaster(): array
    {
        return Cache::remember('homepage:statistik', 1800, function () {
            $pengaturan = \App\Models\PengaturanSekolah::current();
            $activeYearId = $pengaturan ? $pengaturan->academic_year_id_active : null;

            // Jika tahun ajaran aktif belum diset, siswa dan rombel dianggap 0
            $siswaCount = 0;
            $rombelCount = 0;

            if ($activeYearId) {
                $siswaCount = Siswa::whereHas('enrollments', function ($q) use ($activeYearId) {
                    $q->where('academic_year_id', $activeYearId)
                      ->where('status', 'aktif');
                })->count();

                $rombelCount = \App\Models\KelasAjaran::where('academic_year_id', $activeYearId)->count();
            }

            return [
                'siswa' => $siswaCount,
                'rombel' => $rombelCount,
                'pendidik' => Guru::count(),
                'tendik' => WebSetting::instance()->stat_tenaga_kependidikan,
            ];
        });
    }

    /**
     * Facade tunggal untuk BerandaController — menggabungkan semua stats.
     */
    public function getStats(): array
    {
        $master = $this->getStatistikMaster();

        return [
            'hadir_hari_ini' => $this->getPresensiHariIni(),
            'total_siswa'    => $master['siswa'],
            'total_rombel'   => $master['rombel'],
            'total_guru'     => $master['pendidik'],
            'total_tendik'   => $master['tendik'],
        ];
    }
}
