<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Mobile\SchoolInfoController;
use App\Http\Controllers\Api\Mobile\AuthController;
use App\Http\Controllers\Api\Mobile\GuruPresensiController;
use App\Http\Controllers\Api\Mobile\GuruSholatDhuhurController;
use App\Http\Controllers\Api\Mobile\GuruJurnalMengajarController;
use App\Http\Controllers\Api\Mobile\GuruIjinKehadiranController;
use App\Http\Controllers\Api\Mobile\GuruWaliApiController;

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
        Route::get('/guru-wali/dashboard', [GuruWaliApiController::class, 'dashboard']);
        Route::get('/guru-wali/jurnal', [GuruWaliApiController::class, 'jurnalIndex']);
        Route::post('/guru-wali/jurnal/individual', [GuruWaliApiController::class, 'jurnalStoreIndividual']);
        Route::post('/guru-wali/jurnal/klasikal', [GuruWaliApiController::class, 'jurnalStoreKlasikal']);
        Route::get('/guru-wali/jurnal/{id}', [GuruWaliApiController::class, 'jurnalShow']);
        Route::put('/guru-wali/jurnal/{id}', [GuruWaliApiController::class, 'jurnalUpdate']);
        Route::delete('/guru-wali/jurnal/{id}', [GuruWaliApiController::class, 'jurnalDestroy']);
        Route::post('/guru-wali/jurnal/{id}/rujuk-bk', [GuruWaliApiController::class, 'jurnalRujukBk']);

        Route::get('/guru-wali/konsultasi', [GuruWaliApiController::class, 'konsultasiIndex']);
        Route::get('/guru-wali/konsultasi/{id}', [GuruWaliApiController::class, 'konsultasiShow']);
        Route::post('/guru-wali/konsultasi/{id}/pesan', [GuruWaliApiController::class, 'konsultasiKirimPesan']);
        Route::post('/guru-wali/konsultasi/{id}/jadwalkan', [GuruWaliApiController::class, 'konsultasiJadwalkan']);
        Route::post('/guru-wali/konsultasi/{id}/balas', [GuruWaliApiController::class, 'konsultasiBalas']);
        Route::post('/guru-wali/konsultasi/{id}/tolak', [GuruWaliApiController::class, 'konsultasiTolak']);
        Route::post('/guru-wali/konsultasi/{id}/konversi-jurnal', [GuruWaliApiController::class, 'konsultasiKonversiJurnal']);

        Route::get('/guru-wali/pemantauan', [GuruWaliApiController::class, 'pemantauanIndex']);
        Route::get('/guru-wali/cetak/pdf', [GuruWaliApiController::class, 'cetakPdf']);
    });

    // 4. Endpoint Terproteksi & Autentikasi Tambahan
    Route::post('/auth/fcm-token', [AuthController::class, 'registerFcmToken']);
    Route::delete('/auth/fcm-token', [AuthController::class, 'deleteFcmToken']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/auth/me', [AuthController::class, 'me']);
        Route::post('/auth/logout', [AuthController::class, 'logout']);
    });
});
