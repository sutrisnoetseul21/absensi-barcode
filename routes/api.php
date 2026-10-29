<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Mobile\SchoolInfoController;
use App\Http\Controllers\Api\Mobile\AuthController;

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

    // 3. Endpoint Terproteksi Sanctum Bearer Token
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/auth/me', [AuthController::class, 'me']);
        Route::post('/auth/logout', [AuthController::class, 'logout']);
    });
});
