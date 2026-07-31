<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Livewire\WaliKelasLogin;
use App\Livewire\WaliKelasDashboard;
use App\Livewire\SiswaLogin;
use App\Livewire\SiswaDashboard;
use App\Livewire\SiswaProfil;
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
    
    // Cetak Label Spine Buku Routes
    Route::get('/admin-perpustakaan/buku/{buku}/cetak-label-spine', [\App\Http\Controllers\EksemplarCetakController::class, 'cetakLabelSpine'])->name('perpustakaan.cetak-label-spine');
    Route::get('/admin-perpustakaan/buku/cetak/label-spine-massal', [\App\Http\Controllers\EksemplarCetakController::class, 'cetakLabelSpineMassal'])->name('perpustakaan.cetak-label-spine-massal');
    
    // Kiosk Sirkulasi Perpustakaan Routes
    Route::get('/admin-perpustakaan/sirkulasi', \App\Livewire\SirkulasiKiosk::class)->name('perpustakaan.sirkulasi');
    Route::post('/admin-perpustakaan/sirkulasi/process', function (\Illuminate\Http\Request $request, \App\Actions\ProcessSirkulasiAction $action) {
        return response()->json($action->execute($request->all(), auth()->id()));
    })->name('perpustakaan.sirkulasi.process');

    // Kiosk Kunjungan Perpustakaan Routes
    Route::get('/perpustakaan/kunjungan', \App\Livewire\KunjunganKiosk::class)->name('perpustakaan.kunjungan');
    Route::post('/perpustakaan/kunjungan/process', function (\Illuminate\Http\Request $request, \App\Actions\ProcessKunjunganAction $action) {
        $barcode = $request->input('barcode');
        $tujuan = $request->input('tujuan_kunjungan', 'Membaca / Belajar');
        return response()->json($action->execute((string) $barcode, auth()->id(), (string) $tujuan));
    })->middleware('throttle:60,1')->name('perpustakaan.kunjungan.process');

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


// Wali Kelas Routes (Portal Guru)
Route::prefix('portal-guru')->group(function () {
    Route::get('/login', WaliKelasLogin::class)->middleware('guest')->name('portal-guru.login');
    
    Route::middleware('auth.wali')->group(function () {
        Route::get('/', WaliKelasDashboard::class)->name('portal-guru.dashboard');
        Route::get('/siswa/{id}', \App\Livewire\WaliKelasStudentDetail::class)->name('portal-guru.student-detail');
        
        Route::post('/logout', function () {
            Auth::guard('web')->logout();
            request()->session()->invalidate();
            request()->session()->regenerateToken();
            return redirect('/');
        })->name('portal-guru.logout');
    });
});

// Siswa Routes (Portal Siswa)
Route::prefix('portal-siswa')->group(function () {
    Route::get('/login', SiswaLogin::class)->middleware('guest')->name('portal-siswa.login');
    
    Route::middleware('auth.siswa')->group(function () {
        Route::get('/', SiswaDashboard::class)->name('portal-siswa.dashboard');
        Route::get('/profil', SiswaProfil::class)->name('portal-siswa.profil');
        Route::get('/cetak-kartu', [\App\Http\Controllers\SiswaCetakController::class, 'cetakKartuMandiri'])->name('portal-siswa.cetak-kartu');
        
        Route::post('/logout', function () {
            Auth::guard('web')->logout();
            request()->session()->invalidate();
            request()->session()->regenerateToken();
            return redirect('/');
        })->name('portal-siswa.logout');
    });
});

// Petugas Perpustakaan Routes (Portal Perpustakaan)
Route::prefix('portal-perpustakaan')->group(function () {
    Route::get('/login', \App\Livewire\PetugasPerpusLogin::class)->middleware('guest')->name('portal-perpustakaan.login');
    
    Route::middleware('auth.perpus')->group(function () {
        Route::get('/', \App\Livewire\PetugasPerpusDashboard::class)->name('portal-perpustakaan.dashboard');
        Route::get('/buku', \App\Livewire\PetugasPerpusBuku::class)->name('portal-perpustakaan.buku');
        Route::get('/inventaris', \App\Livewire\PetugasPerpusInventaris::class)->name('portal-perpustakaan.inventaris');
        Route::get('/sirkulasi', \App\Livewire\PetugasPerpusSirkulasi::class)->name('portal-perpustakaan.sirkulasi');
        Route::get('/kunjungan', \App\Livewire\PetugasPerpusKunjungan::class)->name('portal-perpustakaan.kunjungan');
        
        Route::post('/logout', function () {
            Auth::guard('web')->logout();
            request()->session()->invalidate();
            request()->session()->regenerateToken();
            return redirect('/');
        })->name('portal-perpustakaan.logout');
    });
});
