<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Livewire\WaliKelasLogin;
use App\Livewire\WaliKelasDashboard;
use App\Livewire\SiswaLogin;
use App\Livewire\SiswaDashboard;
use App\Livewire\ForceChangePassword;

use App\Livewire\PublicDashboard;
use App\Livewire\PublicDashboardV1;
use App\Livewire\Public\KatalogPerpustakaan;

// ERP Portal Route
Route::get('/', fn() => view('erp-portal'))->name('erp.portal');

// Dashboard Publik Presensi Routes
Route::get('/presensi', PublicDashboard::class)->name('public.dashboard');
Route::get('/presensi/dashboardv1', PublicDashboardV1::class)->name('public.dashboard.v1');
Route::get('/presensi/display', PublicDashboard::class)->name('public.display');

// Dashboard Publik Perpustakaan
Route::get('/perpustakaan', KatalogPerpustakaan::class)
    ->middleware('throttle:60,1')
    ->name('perpustakaan.dashboard');
// Route fallback untuk redirect unauthenticated users ke Filament admin login
Route::get('/login', fn() => view('auth.portal-selection'))->name('login');


// Kiosk Absensi Routes - Protected by 'auth' middleware so only Admin can access
Route::middleware('auth')->group(function () {
    Route::get('/scan', \App\Livewire\AttendanceKiosk::class)->name('kiosk.scan');
    Route::post('/scan', function (\Illuminate\Http\Request $request, \App\Actions\ProcessScanAction $action) {
        $barcode = $request->input('barcode');
        if (!$barcode) {
            return response()->json(['status' => 'not_found']);
        }
        return response()->json($action->execute($barcode, $request->ip(), 'nisn'));
    })->middleware('throttle:60,1')->name('kiosk.process');

    Route::get('/scan-nis', \App\Livewire\AttendanceKioskNis::class)->name('kiosk.scan-nis');
    Route::post('/scan-nis', function (\Illuminate\Http\Request $request, \App\Actions\ProcessScanAction $action) {
        $barcode = $request->input('barcode');
        if (!$barcode) {
            return response()->json(['status' => 'not_found']);
        }
        return response()->json($action->execute($barcode, $request->ip(), 'nis'));
    })->middleware('throttle:60,1')->name('kiosk.process-nis');

    // Cetak Kartu Routes
    Route::get('/admin/siswa/{siswa}/cetak-kartu', [\App\Http\Controllers\SiswaCetakController::class, 'cetakKartu'])->name('siswa.cetak-kartu');
    Route::get('/admin/siswa/{siswa}/cetak-kartu-login', [\App\Http\Controllers\SiswaCetakController::class, 'cetakKartuLogin'])->name('siswa.cetak-kartu-login');
    Route::get('/admin/siswa/cetak-kartu-massal', [\App\Http\Controllers\SiswaCetakController::class, 'cetakKartuMassal'])->name('siswa.cetak-kartu-massal');
    Route::get('/admin/siswa/cetak-kartu-login-massal', [\App\Http\Controllers\SiswaCetakController::class, 'cetakKartuLoginMassal'])->name('siswa.cetak-kartu-login-massal');

    // Cetak Barcode Perpustakaan Routes
    Route::get('/admin-perpustakaan/buku/{buku}/cetak-barcode', [\App\Http\Controllers\EksemplarCetakController::class, 'cetakBarcode'])->name('perpustakaan.cetak-barcode');
    Route::get('/admin-perpustakaan/eksemplar/cetak-barcode-massal', [\App\Http\Controllers\EksemplarCetakController::class, 'cetakBarcodeMassal'])->name('perpustakaan.cetak-barcode-massal');
    
    // Kiosk Sirkulasi Perpustakaan Routes
    Route::get('/admin-perpustakaan/sirkulasi', \App\Livewire\SirkulasiKiosk::class)->name('perpustakaan.sirkulasi');
    Route::post('/admin-perpustakaan/sirkulasi/process', function (\Illuminate\Http\Request $request, \App\Actions\ProcessSirkulasiAction $action) {
        return response()->json($action->execute($request->all(), auth()->id()));
    })->name('perpustakaan.sirkulasi.process');

    // Download laporan hasil import siswa (PPDB)
    Route::get('/admin/import/download-laporan', function () {
        $reportKey = 'import_laporan_' . auth()->id();
        $results   = cache()->get($reportKey);

        if (empty($results)) {
            abort(404, 'Laporan tidak ditemukan atau sudah kedaluwarsa (maks. 30 menit).');
        }

        // Hapus dari cache setelah didownload
        cache()->forget($reportKey);

        $filename = 'laporan-import-siswa-' . now()->format('Ymd-His') . '.xlsx';
        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\SiswaImportLaporanExport($results),
            $filename
        );
    })->name('admin.import.download-laporan');
});


// Wali Kelas Routes
Route::prefix('wali-kelas')->group(function () {
    Route::get('/login', WaliKelasLogin::class)->middleware('guest')->name('wali-kelas.login');
    
    Route::middleware('auth.wali')->group(function () {
        Route::get('/', WaliKelasDashboard::class)->name('wali-kelas.dashboard');
        Route::get('/siswa/{id}', \App\Livewire\WaliKelasStudentDetail::class)->name('wali-kelas.student-detail');
        
        Route::post('/logout', function () {
            Auth::guard('web')->logout();
            request()->session()->invalidate();
            request()->session()->regenerateToken();
            return redirect('/');
        })->name('wali-kelas.logout');
    });
});

// Siswa Routes
Route::prefix('siswa')->group(function () {
    Route::get('/login', SiswaLogin::class)->middleware('guest')->name('siswa.login');
    
    Route::middleware('auth.siswa')->group(function () {
        Route::get('/', SiswaDashboard::class)->name('siswa.dashboard');
        
        Route::post('/logout', function () {
            Auth::guard('web')->logout();
            request()->session()->invalidate();
            request()->session()->regenerateToken();
            return redirect('/');
        })->name('siswa.logout');
    });
});
