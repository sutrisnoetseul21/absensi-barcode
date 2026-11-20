<?php

namespace App\Filament\Akademik\Pages;

use App\Filament\Akademik\Resources\Ekstrakurikuler\EkstrakurikulerResource;
use App\Filament\Akademik\Resources\Enrollment\EnrollmentResource;
use App\Filament\Akademik\Resources\Guru\GuruResource;
use App\Filament\Akademik\Resources\GuruWali\JurnalGuruWaliResource;
use App\Filament\Akademik\Resources\GuruWali\KelompokGuruWaliResource;
use App\Filament\Akademik\Resources\JurnalPembelajaranResource;
use App\Filament\Akademik\Resources\Kelas\KelasResource;
use App\Filament\Akademik\Resources\PindahKelasResource;
use App\Filament\Akademik\Resources\Siswa\SiswaResource;
use App\Filament\Akademik\Resources\SiswaLulusResource;
use App\Filament\Akademik\Resources\SiswaMutasiResource;
use App\Models\Ekstrakurikuler;
use App\Models\Guru;
use App\Models\HariLibur;
use App\Models\JurnalGuruWali;
use App\Models\JurnalPembelajaran;
use App\Models\Kelas;
use App\Models\KelompokGuruWali;
use App\Models\PengaturanSekolah;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use App\Services\KalenderSekolahService;
use Filament\Pages\Dashboard as BaseDashboard;
use Illuminate\Contracts\Support\Htmlable;

class AkademikDashboard extends BaseDashboard
{
    protected static ?string $navigationLabel = 'Dashboard Akademik';

    protected static ?string $title = 'Dashboard Akademik';

    protected static string $routePath = '/';

    protected string $view = 'filament.akademik.pages.dashboard';

    public function getHeading(): string|Htmlable
    {
        return '';
    }

    protected function getViewData(): array
    {
        $user = auth()->user();
        $sekolah = PengaturanSekolah::current();
        $activeYear = TahunAjaran::where('status', 'aktif')->first();
        $now = now('Asia/Jakarta');

        $kalenderService = app(KalenderSekolahService::class);
        $isHariSekolah = $kalenderService->isHariSekolah($now);
        $holiday = ! $isHariSekolah ? HariLibur::hariIni($now->toDateString())->first() : null;
        $holidayLabel = $holiday ? $holiday->description : ($now->isWeekend() ? 'Akhir Pekan' : 'Hari Libur');

        // Statistik Akademik
        $totalSiswaAktif = Siswa::where('status', 'aktif')->count();
        $totalSiswaLulus = Siswa::where('status', 'lulus')->count();
        $totalSiswaMutasi = Siswa::where('status', 'mutasi')->count();
        $totalGuru = Guru::count();
        $totalKelas = Kelas::count();
        $totalJurnalHariIni = JurnalPembelajaran::whereDate('tanggal', $now->toDateString())->count();
        $totalJurnalSemua = JurnalPembelajaran::count();
        $totalKelompokGuruWali = KelompokGuruWali::where('status_aktif', true)->count();
        $totalLogGuruWali = JurnalGuruWali::count();
        $totalEkskulAktif = Ekstrakurikuler::when($activeYear, fn ($q) => $q->where('academic_year_id', $activeYear->id))
            ->where('status_aktif', true)
            ->count();

        // Tema Dinamis
        $themePrimary = $sekolah?->theme_primary ?: '#d97706';
        $themeSecondary = $sekolah?->theme_secondary ?: ($sekolah?->theme_primary ?: '#b45309');

        // Izin Akses & URL
        $canGuru = GuruResource::canViewAny();
        $canGuruCreate = GuruResource::canCreate();
        $canSiswa = SiswaResource::canViewAny();
        $canSiswaCreate = SiswaResource::canCreate();
        $canKelas = KelasResource::canViewAny();
        $canKelasCreate = KelasResource::canCreate();
        $canEnrollment = EnrollmentResource::canViewAny();
        $canPindahKelas = PindahKelasResource::canViewAny();
        $canSiswaLulus = SiswaLulusResource::canViewAny();
        $canSiswaMutasi = SiswaMutasiResource::canViewAny();

        $canJurnalData = JurnalPembelajaranResource::canViewAny();
        $canJurnalMonitoring = MonitoringGuruKelasJurnalPage::canAccess();
        $canJurnalAnalisa = AnalisaPresensiKbmSiswaPage::canAccess();

        $canGwKelompok = KelompokGuruWaliResource::canViewAny();
        $canGwMonitoring = MonitoringGuruWaliPage::canAccess();
        $canGwAnalisa = AnalisaGuruWaliSiswaPage::canAccess();
        $canGwJurnal = JurnalGuruWaliResource::canViewAny();

        $canEkskulData = EkstrakurikulerResource::canViewAny();
        $canEkskulCreate = EkstrakurikulerResource::canCreate();
        $canEkskulMonitoring = MonitoringEkstrakurikulerPage::canAccess();

        return [
            'userName' => $user?->display_name ?? $user?->name ?? 'Admin Akademik',
            'todayFormatted' => $now->translatedFormat('l, d F Y'),
            'activeYearName' => $activeYear?->name ?? 'Belum Disetel',
            'activeSemester' => ucfirst($sekolah?->active_semester ?? 'ganjil'),
            'isHariSekolah' => $isHariSekolah,
            'holidayLabel' => $holidayLabel,
            'themePrimary' => $themePrimary,
            'themeSecondary' => $themeSecondary,

            // Statistik
            'totalSiswaAktif' => $totalSiswaAktif,
            'totalSiswaLulus' => $totalSiswaLulus,
            'totalSiswaMutasi' => $totalSiswaMutasi,
            'totalGuru' => $totalGuru,
            'totalKelas' => $totalKelas,
            'totalJurnalHariIni' => $totalJurnalHariIni,
            'totalJurnalSemua' => $totalJurnalSemua,
            'totalKelompokGuruWali' => $totalKelompokGuruWali,
            'totalLogGuruWali' => $totalLogGuruWali,
            'totalEkskulAktif' => $totalEkskulAktif,

            // Data Master & Kesiswaan
            'canGuru' => $canGuru,
            'canGuruCreate' => $canGuruCreate,
            'urlGuruIndex' => GuruResource::getUrl('index', panel: 'akademik'),
            'urlGuruCreate' => GuruResource::getUrl('create', panel: 'akademik'),

            'canSiswa' => $canSiswa,
            'canSiswaCreate' => $canSiswaCreate,
            'urlSiswaIndex' => SiswaResource::getUrl('index', panel: 'akademik'),
            'urlSiswaCreate' => SiswaResource::getUrl('create', panel: 'akademik'),

            'canKelas' => $canKelas,
            'canKelasCreate' => $canKelasCreate,
            'urlKelasIndex' => KelasResource::getUrl('index', panel: 'akademik'),
            'urlKelasCreate' => KelasResource::getUrl('create', panel: 'akademik'),

            'canEnrollment' => $canEnrollment,
            'urlEnrollment' => EnrollmentResource::getUrl('index', panel: 'akademik'),

            'canPindahKelas' => $canPindahKelas,
            'urlPindahKelas' => PindahKelasResource::getUrl('index', panel: 'akademik'),

            'canSiswaLulus' => $canSiswaLulus,
            'urlSiswaLulus' => SiswaLulusResource::getUrl('index', panel: 'akademik'),

            'canSiswaMutasi' => $canSiswaMutasi,
            'urlSiswaMutasi' => SiswaMutasiResource::getUrl('index', panel: 'akademik'),

            // Jurnal Mengajar
            'canJurnalGroup' => $canJurnalData || $canJurnalMonitoring || $canJurnalAnalisa,
            'canJurnalData' => $canJurnalData,
            'urlJurnalData' => JurnalPembelajaranResource::getUrl('index', panel: 'akademik'),
            'canJurnalMonitoring' => $canJurnalMonitoring,
            'urlJurnalMonitoring' => MonitoringGuruKelasJurnalPage::getUrl(panel: 'akademik'),
            'canJurnalAnalisa' => $canJurnalAnalisa,
            'urlJurnalAnalisa' => AnalisaPresensiKbmSiswaPage::getUrl(panel: 'akademik'),

            // Guru Wali
            'canGwGroup' => $canGwKelompok || $canGwMonitoring || $canGwAnalisa || $canGwJurnal,
            'canGwKelompok' => $canGwKelompok,
            'urlGwKelompok' => KelompokGuruWaliResource::getUrl('index', panel: 'akademik'),
            'canGwMonitoring' => $canGwMonitoring,
            'urlGwMonitoring' => MonitoringGuruWaliPage::getUrl(panel: 'akademik'),
            'canGwAnalisa' => $canGwAnalisa,
            'urlGwAnalisa' => AnalisaGuruWaliSiswaPage::getUrl(panel: 'akademik'),
            'canGwJurnal' => $canGwJurnal,
            'urlGwJurnal' => JurnalGuruWaliResource::getUrl('index', panel: 'akademik'),

            // Ekstrakurikuler
            'canEkskulGroup' => $canEkskulData || $canEkskulMonitoring,
            'canEkskulData' => $canEkskulData,
            'canEkskulCreate' => $canEkskulCreate,
            'urlEkskulIndex' => EkstrakurikulerResource::getUrl('index', panel: 'akademik'),
            'urlEkskulCreate' => EkstrakurikulerResource::getUrl('create', panel: 'akademik'),
            'canEkskulMonitoring' => $canEkskulMonitoring,
            'urlEkskulMonitoring' => MonitoringEkstrakurikulerPage::getUrl(panel: 'akademik'),

            // Portal Setup
            'canPortalSetup' => $user && ($user->isSuperAdmin() || $user->hasRole('admin_portal_setup')),
        ];
    }
}
