<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Mobile\SchoolInfoController;
use App\Http\Controllers\Api\Mobile\AuthController;
use App\Http\Controllers\Api\Mobile\GuruPresensiController;
use App\Http\Controllers\Api\Mobile\GuruSholatDhuhurController;
use App\Http\Controllers\Api\Mobile\GuruJurnalMengajarController;
use App\Http\Controllers\Api\Mobile\GuruIjinKehadiranController;
use App\Http\Controllers\Api\Mobile\GuruWali\GuruWaliDashboardController;
use App\Http\Controllers\Api\Mobile\GuruWali\GuruWaliJurnalController;
use App\Http\Controllers\Api\Mobile\GuruWali\GuruWaliKonsultasiController;
use App\Http\Controllers\Api\Mobile\GuruWali\GuruWaliLaporanController;
use App\Http\Controllers\Api\Mobile\DataSiswa\DataSiswaListController;
use App\Http\Controllers\Api\Mobile\DataSiswa\DataSiswaEditController;
use App\Http\Controllers\Api\Mobile\DataSiswa\DataSiswaCetakController;
use App\Http\Controllers\Api\Mobile\Spikap\SpikapInboxController;
use App\Http\Controllers\Api\Mobile\Spikap\SpikapDetailController;
use App\Http\Controllers\Api\Mobile\Spikap\SpikapAnalitikController;
use App\Http\Controllers\Api\Mobile\Perpustakaan\PerpustakaanKatalogController;
use App\Http\Controllers\Api\Mobile\Perpustakaan\PerpustakaanPeminjamanController;
use App\Http\Controllers\Api\Mobile\Perpustakaan\PerpustakaanKunjunganController;
use App\Http\Controllers\Api\Mobile\Perpustakaan\PerpustakaanSiswaController;
use App\Http\Controllers\Api\Mobile\GuruProfilController;
use App\Http\Controllers\Api\Mobile\Siswa\SiswaDashboardController;
use App\Http\Controllers\Api\Mobile\Siswa\SiswaIjinKehadiranController;
use App\Http\Controllers\Api\Mobile\Siswa\SiswaGuruWaliController;
use App\Http\Controllers\Api\Mobile\Siswa\SiswaSpikapController;
use App\Http\Controllers\Api\Mobile\Siswa\SiswaPerpustakaanController;
use App\Http\Controllers\Api\Mobile\Siswa\SiswaKartuController;
use App\Http\Controllers\Api\Mobile\Siswa\SiswaProfilController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Mobile API Gateway (v1)
Route::prefix('v1')->group(function () {
    // 1. Informasi Identitas Sekolah (Publik, Dinamis dari tabel school_settings)
    Route::get('/school-info', [SchoolInfoController::class, 'index']);
    Route::get('/school-logo', [SchoolInfoController::class, 'logo']);

    // 2. Otentikasi Terpadu (Unified Login: NISN / NIP / Email)
    Route::post('/auth/login', [AuthController::class, 'login']);

    // 3. Modul Portal Guru (Mendukung Sanctum Token & Fallback Token)
    Route::prefix('guru')->group(function () {
        Route::get('/dashboard-summary', [GuruPresensiController::class, 'dashboardSummary']);

        // Menu 1: Presensi Kelas (/portal-guru/akademik)
        Route::get('/presensi/dashboard', [GuruPresensiController::class, 'dashboard']);
        Route::get('/presensi/manual-input', [GuruPresensiController::class, 'getManualInput']);
        Route::post('/presensi/manual-input', [GuruPresensiController::class, 'saveManualInput']);
        Route::post('/presensi/send-wa', [GuruPresensiController::class, 'sendWaDirect']);
        Route::get('/presensi/export', [GuruPresensiController::class, 'exportReport']);
        Route::get('/presensi/student/{id}', [GuruPresensiController::class, 'studentDetail']);

        // Menu 2: Presensi Sholat Dhuhur (/portal-guru/sholat-dhuhur)
        Route::get('/sholat-dhuhur/dashboard', [GuruSholatDhuhurController::class, 'dashboard']);
        Route::get('/sholat-dhuhur/manual-input', [GuruSholatDhuhurController::class, 'getManualInput']);
        Route::post('/sholat-dhuhur/manual-input', [GuruSholatDhuhurController::class, 'saveManualInput']);
        Route::post('/sholat-dhuhur/sync-morning', [GuruSholatDhuhurController::class, 'syncFromMorning']);
        Route::post('/sholat-dhuhur/send-wa', [GuruSholatDhuhurController::class, 'sendWaDirect']);
        Route::get('/sholat-dhuhur/export', [GuruSholatDhuhurController::class, 'exportReport']);

        // Menu 3: Jurnal Mengajar (/portal-guru/jurnal-mengajar)
        Route::get('/jurnal-mengajar', [GuruJurnalMengajarController::class, 'index']);
        Route::get('/jurnal-mengajar/form-options', [GuruJurnalMengajarController::class, 'formOptions']);
        Route::get('/jurnal-mengajar/students', [GuruJurnalMengajarController::class, 'students']);
        Route::get('/jurnal-mengajar/export', [GuruJurnalMengajarController::class, 'export']);
        Route::post('/jurnal-mengajar', [GuruJurnalMengajarController::class, 'store']);
        Route::get('/jurnal-mengajar/{id}', [GuruJurnalMengajarController::class, 'show']);
        Route::put('/jurnal-mengajar/{id}', [GuruJurnalMengajarController::class, 'update']);
        Route::delete('/jurnal-mengajar/{id}', [GuruJurnalMengajarController::class, 'destroy']);

        // Menu 4: Persetujuan Ijin (/portal-guru/ijin-kehadiran)
        Route::get('/ijin-kehadiran', [GuruIjinKehadiranController::class, 'index']);
        Route::get('/ijin-kehadiran/notification-check', [GuruIjinKehadiranController::class, 'notificationCheck']);
        Route::get('/ijin-kehadiran/file-stream', [GuruIjinKehadiranController::class, 'streamFile']);
        Route::get('/ijin-kehadiran/{id}', [GuruIjinKehadiranController::class, 'show']);
        Route::post('/ijin-kehadiran/{id}/approve', [GuruIjinKehadiranController::class, 'approve']);
        Route::post('/ijin-kehadiran/{id}/reject', [GuruIjinKehadiranController::class, 'reject']);
        Route::post('/ijin-kehadiran/{id}/reset', [GuruIjinKehadiranController::class, 'reset']);

        // Menu 5: Guru Wali (SI-WALI) (/portal-guru/guru-wali/*)
        Route::prefix('guru-wali')->group(function () {
            // 1. Dashboard & Siswa Asuh
            Route::get('/dashboard', [GuruWaliDashboardController::class, 'dashboard']);

            // 2. Jurnal Bimbingan
            Route::get('/jurnal', [GuruWaliJurnalController::class, 'index']);
            Route::post('/jurnal/individual', [GuruWaliJurnalController::class, 'storeIndividual']);
            Route::post('/jurnal/klasikal', [GuruWaliJurnalController::class, 'storeKlasikal']);
            Route::get('/jurnal/{id}', [GuruWaliJurnalController::class, 'show']);
            Route::put('/jurnal/{id}', [GuruWaliJurnalController::class, 'update']);
            Route::delete('/jurnal/{id}', [GuruWaliJurnalController::class, 'destroy']);
            Route::post('/jurnal/{id}/rujuk-bk', [GuruWaliJurnalController::class, 'rujukBk']);

            // 3. Ruang Aman Cerita / Konsultasi
            Route::get('/konsultasi', [GuruWaliKonsultasiController::class, 'index']);
            Route::get('/konsultasi/{id}', [GuruWaliKonsultasiController::class, 'show']);
            Route::post('/konsultasi/{id}/pesan', [GuruWaliKonsultasiController::class, 'kirimPesan']);
            Route::post('/konsultasi/{id}/jadwalkan', [GuruWaliKonsultasiController::class, 'jadwalkan']);
            Route::post('/konsultasi/{id}/balas', [GuruWaliKonsultasiController::class, 'balas']);
            Route::post('/konsultasi/{id}/tolak', [GuruWaliKonsultasiController::class, 'tolak']);
            Route::post('/konsultasi/{id}/konversi-jurnal', [GuruWaliKonsultasiController::class, 'konversiJurnal']);

            // 4. Pemantauan & Cetak Laporan
            Route::get('/pemantauan', [GuruWaliLaporanController::class, 'pemantauan']);
            Route::get('/cetak/pdf', [GuruWaliLaporanController::class, 'cetakPdf']);
        });

        // Menu 6: Data Siswa (/portal-guru/data-siswa)
        Route::prefix('data-siswa')->group(function () {
            Route::get('/options', [DataSiswaListController::class, 'options']);
            Route::get('/students', [DataSiswaListController::class, 'students']);
            Route::get('/student/{id}', [DataSiswaEditController::class, 'detail']);
            Route::put('/student/{id}/biodata', [DataSiswaEditController::class, 'updateBiodata']);
            Route::patch('/student/{id}/no-hp', [DataSiswaEditController::class, 'updateNoHp']);
            Route::post('/student/{id}/ganti-password', [DataSiswaEditController::class, 'gantiPassword']);
            Route::post('/student/{id}/upload-foto', [DataSiswaEditController::class, 'uploadFoto']);
            Route::get('/student/{id}/cetak-kartu', [DataSiswaCetakController::class, 'cetakKartu']);
            Route::get('/student/{id}/cetak-biodata', [DataSiswaCetakController::class, 'cetakBiodata']);
            Route::get('/cetak-kartu-massal', [DataSiswaCetakController::class, 'cetakKartuMassal']);
            Route::get('/cetak-biodata-massal', [DataSiswaCetakController::class, 'cetakBiodataMassal']);
        });

        // Menu 7: SPIKAP (/portal-guru/spikap) - Proteksi Khusus BK, Wali Kelas, dan Kepala Sekolah
        Route::prefix('spikap')->group(function () {
            Route::get('/check-access', [SpikapInboxController::class, 'checkAccess']);
            Route::get('/inbox', [SpikapInboxController::class, 'inbox']);
            Route::get('/laporan/{id}', [SpikapDetailController::class, 'detail']);
            Route::post('/laporan/{id}/status', [SpikapDetailController::class, 'updateStatus']);
            Route::get('/lampiran/{id}', [SpikapDetailController::class, 'lampiran']);
            Route::get('/analitik', [SpikapAnalitikController::class, 'analitik']);
        });

        // Menu 8: Perpustakaan (/portal-guru/perpustakaan)
        Route::prefix('perpustakaan')->group(function () {
            Route::get('/summary', [PerpustakaanKatalogController::class, 'summary']);
            Route::get('/kategori', [PerpustakaanKatalogController::class, 'kategori']);
            Route::get('/katalog', [PerpustakaanKatalogController::class, 'katalog']);
            Route::get('/katalog/{id}', [PerpustakaanKatalogController::class, 'detail']);

            Route::get('/pinjaman-saya', [PerpustakaanPeminjamanController::class, 'pinjamanSaya']);
            Route::get('/riwayat-pinjaman', [PerpustakaanPeminjamanController::class, 'riwayatPinjaman']);

            Route::get('/kunjungan-saya', [PerpustakaanKunjunganController::class, 'kunjunganSaya']);

            Route::get('/kelas-ampu', [PerpustakaanSiswaController::class, 'kelasAmpu']);
            Route::get('/pinjaman-siswa', [PerpustakaanSiswaController::class, 'pinjamanSiswa']);
        });

        // Menu 9: Profil Saya (/portal-guru/profil)
        Route::prefix('profil')->group(function () {
            Route::get('/', [GuruProfilController::class, 'getProfile']);
            Route::post('/kontak', [GuruProfilController::class, 'updateContact']);
            Route::post('/foto', [GuruProfilController::class, 'updatePhoto']);
            Route::delete('/foto', [GuruProfilController::class, 'removePhoto']);
            Route::post('/password', [GuruProfilController::class, 'updatePassword']);
        });
    });

    // 4. Modul Portal Siswa (Mendukung Sanctum Token & Fallback Token)
    Route::prefix('siswa')->group(function () {
        // Menu 1: Dashboard & Presensi / Kalender Akademik
        Route::get('/dashboard-summary', [SiswaDashboardController::class, 'dashboardSummary']);
        Route::get('/akademik/dashboard', [SiswaDashboardController::class, 'akademikDashboard']);

        // Menu 2: Pengajuan Ijin Kehadiran
        Route::prefix('ijin-kehadiran')->group(function () {
            Route::get('/', [SiswaIjinKehadiranController::class, 'index']);
            Route::get('/check-holidays', [SiswaIjinKehadiranController::class, 'checkHolidays']);
            Route::post('/', [SiswaIjinKehadiranController::class, 'store']);
            Route::get('/{id}', [SiswaIjinKehadiranController::class, 'show']);
            Route::delete('/{id}', [SiswaIjinKehadiranController::class, 'destroy']);
        });

        // Menu 3: Guru Wali (SI-WALI)
        Route::prefix('guru-wali')->group(function () {
            Route::get('/dashboard', [SiswaGuruWaliController::class, 'dashboard']);
            Route::get('/konsultasi', [SiswaGuruWaliController::class, 'konsultasi']);
            Route::post('/konsultasi', [SiswaGuruWaliController::class, 'store']);
            Route::get('/konsultasi/{id}', [SiswaGuruWaliController::class, 'show']);
            Route::post('/konsultasi/{id}/pesan', [SiswaGuruWaliController::class, 'storePesan']);
            Route::delete('/konsultasi/{id}', [SiswaGuruWaliController::class, 'destroy']);
            Route::post('/konsultasi/{id}/feedback', [SiswaGuruWaliController::class, 'storeFeedback']);
            Route::get('/pendampingan-klasikal', [SiswaGuruWaliController::class, 'pendampinganKlasikal']);
        });

        // Menu 4: SPIKAP (Anti-Perundungan & Karakter)
        Route::prefix('spikap')->group(function () {
            Route::get('/dashboard', [SiswaSpikapController::class, 'dashboard']);
            Route::get('/laporan', [SiswaSpikapController::class, 'index']);
            Route::post('/laporan', [SiswaSpikapController::class, 'store']);
            Route::get('/laporan/{id}', [SiswaSpikapController::class, 'show']);
            Route::get('/lampiran/{id}', [SiswaSpikapController::class, 'streamLampiran']);
            Route::get('/lampiran/{id}/download', [SiswaSpikapController::class, 'downloadLampiran']);
        });

        // Menu 5: Perpustakaan Siswa (Pinjaman Aktif, Riwayat, & Kunjungan)
        Route::prefix('perpustakaan')->group(function () {
            Route::get('/summary', [SiswaPerpustakaanController::class, 'summary']);
            Route::get('/pinjaman-aktif', [SiswaPerpustakaanController::class, 'pinjamanAktif']);
            Route::get('/riwayat-pinjam', [SiswaPerpustakaanController::class, 'riwayatPinjam']);
            Route::get('/riwayat-kunjungan', [SiswaPerpustakaanController::class, 'riwayatKunjungan']);
        });

        // Menu 6: Kartu Pelajar Digital & Unduh PDF
        Route::prefix('kartu')->group(function () {
            Route::get('/digital-info', [SiswaKartuController::class, 'digitalInfo']);
            Route::get('/download-pdf', [SiswaKartuController::class, 'downloadPdf']);
            Route::get('/biodata-pdf', [SiswaKartuController::class, 'biodataPdf']);
        });

        // Menu 7: Profil Saya & Akun Pelajar
        Route::prefix('profil')->group(function () {
            Route::get('/info', [SiswaProfilController::class, 'info']);
            Route::post('/update-kontak', [SiswaProfilController::class, 'updateKontak']);
            Route::post('/update-foto', [SiswaProfilController::class, 'updateFoto']);
            Route::delete('/hapus-foto', [SiswaProfilController::class, 'hapusFoto']);
            Route::post('/update-password', [SiswaProfilController::class, 'updatePassword']);
        });
    });

    // 5. Endpoint Terproteksi & Autentikasi Tambahan
    Route::post('/auth/fcm-token', [AuthController::class, 'registerFcmToken']);
    Route::delete('/auth/fcm-token', [AuthController::class, 'deleteFcmToken']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/auth/me', [AuthController::class, 'me']);
        Route::post('/auth/logout', [AuthController::class, 'logout']);
    });
});
