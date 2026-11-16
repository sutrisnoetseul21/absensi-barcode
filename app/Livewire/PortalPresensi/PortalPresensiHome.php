<?php

namespace App\Livewire\PortalPresensi;

use App\Models\EnrollmentSiswa;
use App\Models\HariLibur;
use App\Models\Kelas;
use App\Models\PengaturanSekolah;
use App\Models\Presensi;
use App\Models\PresensiSholatDhuhur;
use App\Models\TahunAjaran;
use App\Services\KalenderSekolahService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.portal')]
class PortalPresensiHome extends Component
{
    public function render()
    {
        $user = Auth::user();
        $today = Carbon::today('Asia/Jakarta');
        $todayStr = $today->toDateString();
        $todayFormatted = $today->copy()->locale('id')->translatedFormat('l, d F Y');

        $settings = PengaturanSekolah::current();
        $activeYear = TahunAjaran::where('status', 'aktif')->first()
            ?? ($settings?->academic_year_id_active ? TahunAjaran::find($settings->academic_year_id_active) : null)
            ?? TahunAjaran::orderBy('start_year', 'desc')->first();

        $academicYearId = $activeYear?->id;

        $kalenderService = app(KalenderSekolahService::class);
        $isHariSekolah = $kalenderService->isHariSekolah($today, null);
        $isHoliday = !$isHariSekolah;
        $holidayDesc = null;
        if ($isHoliday) {
            $holidayDesc = HariLibur::hariIni($todayStr, null)->first()?->description ?? 'Hari Libur Rutin';
        }

        // Ringkasan kecil Presensi Kehadiran Hari Ini (fokus absen datang)
        $totalSiswa = 0;
        $kehadiranStats = [
            'total'          => 0,
            'sudah_presensi' => 0,
            'hadir'          => 0,
            'telat'          => 0,
            'izin_sakit_alpa'=> 0,
            'belum_hadir'    => 0,
            'persentase'     => 0,
        ];

        $enableSholatDhuhur = (bool) ($settings?->enable_sholat_dhuhur ?? true);
        $dhuhurStats = [
            'wajib'          => 0,
            'hadir'          => 0,
            'izin_halangan'  => 0,
            'tidak_hadir'    => 0,
            'belum'          => 0,
            'persentase'     => 0,
        ];

        if ($academicYearId) {
            $totalSiswa = EnrollmentSiswa::where('academic_year_id', $academicYearId)
                ->where('status', 'aktif')
                ->count();

            $attCounts = Presensi::where('academic_year_id', $academicYearId)
                ->whereDate('date', $todayStr)
                ->selectRaw('status, COUNT(*) as total')
                ->groupBy('status')
                ->pluck('total', 'status');

            $hadir = (int) ($attCounts['hadir'] ?? 0);
            $telat = (int) ($attCounts['telat'] ?? 0);
            $izin = (int) ($attCounts['izin'] ?? 0);
            $sakit = (int) ($attCounts['sakit'] ?? 0);
            $alpa = (int) ($attCounts['alpa'] ?? 0);

            $sudahPresensi = $hadir + $telat;
            $izinSakitAlpa = $izin + $sakit + $alpa;
            $belumHadir = max(0, $totalSiswa - ($sudahPresensi + $izinSakitAlpa));

            $kehadiranStats = [
                'total'           => $totalSiswa,
                'sudah_presensi'  => $sudahPresensi,
                'hadir'           => $hadir,
                'telat'           => $telat,
                'izin_sakit_alpa' => $izinSakitAlpa,
                'belum_hadir'     => $belumHadir,
                'persentase'      => $totalSiswa > 0 ? round(($sudahPresensi / $totalSiswa) * 100) : 0,
            ];

            if ($enableSholatDhuhur) {
                $totalWajibSholat = EnrollmentSiswa::where('academic_year_id', $academicYearId)
                    ->where('status', 'aktif')
                    ->whereHas('siswa', function ($q) {
                        $q->whereNull('religion')
                            ->orWhere('religion', '')
                            ->orWhereRaw("LOWER(TRIM(religion)) = 'islam'");
                    })
                    ->count();

                $dhuhurCounts = PresensiSholatDhuhur::where('academic_year_id', $academicYearId)
                    ->whereDate('date', $todayStr)
                    ->selectRaw('status, COUNT(*) as total')
                    ->groupBy('status')
                    ->pluck('total', 'status');

                $dHadir = (int) ($dhuhurCounts['hadir'] ?? 0);
                $dIzinHalangan = (int) ($dhuhurCounts['ijin'] ?? 0);
                $dTidakHadir = (int) ($dhuhurCounts['tidak_hadir'] ?? 0);
                $dBelum = max(0, $totalWajibSholat - ($dHadir + $dIzinHalangan + $dTidakHadir));

                $dhuhurStats = [
                    'wajib'         => $totalWajibSholat,
                    'hadir'         => $dHadir,
                    'izin_halangan' => $dIzinHalangan,
                    'tidak_hadir'   => $dTidakHadir,
                    'belum'         => $dBelum,
                    'persentase'    => $totalWajibSholat > 0 ? round(($dHadir / $totalWajibSholat) * 100) : 0,
                ];
            }
        }

        $totalKelas = Kelas::count();
        $kioskKehadiranUrl = ($settings?->barcode_scan_mode ?? 'nisn') === 'nis'
            ? route('kiosk.scan-nis')
            : route('kiosk.scan');

        return view('livewire.portal-presensi.portal-presensi-home', [
            'userName'           => $user?->name ?? 'Admin Presensi',
            'todayFormatted'     => $todayFormatted,
            'activeYearName'     => $activeYear?->name ?? '-',
            'isHoliday'          => $isHoliday,
            'holidayDesc'        => $holidayDesc,
            'kehadiranStats'     => $kehadiranStats,
            'dhuhurStats'        => $dhuhurStats,
            'enableSholatDhuhur' => $enableSholatDhuhur,
            'enableCheckout'     => (bool) ($settings?->enable_checkout ?? true),
            'checkinTime'        => substr((string) ($settings?->checkin_time ?? '07:00'), 0, 5),
            'workDaysLabel'      => ($settings?->work_days_type ?? '5_hari') === '6_hari' ? '6 Hari Kerja' : '5 Hari Kerja',
            'totalKelas'         => $totalKelas,
            'kioskKehadiranUrl'  => $kioskKehadiranUrl,
        ])->title('Dashboard Utama - Portal Presensi');
    }
}
