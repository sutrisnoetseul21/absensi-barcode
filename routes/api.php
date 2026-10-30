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
    });

    // 4. Endpoint Terproteksi & Autentikasi Tambahan
    Route::post('/auth/fcm-token', [AuthController::class, 'registerFcmToken']);
    Route::delete('/auth/fcm-token', [AuthController::class, 'deleteFcmToken']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/auth/me', [AuthController::class, 'me']);
        Route::post('/auth/logout', [AuthController::class, 'logout']);
    });
});
