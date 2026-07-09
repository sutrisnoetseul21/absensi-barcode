<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Livewire\WaliKelasLogin;
use App\Livewire\WaliKelasDashboard;
use App\Livewire\SiswaLogin;
use App\Livewire\SiswaDashboard;
use App\Livewire\ForceChangePassword;

use App\Livewire\PublicDashboard;

// Dashboard Publik Routes
Route::get('/', PublicDashboard::class)->name('public.dashboard');
Route::get('/display', PublicDashboard::class)->name('public.display');
// Route fallback untuk redirect unauthenticated users ke Filament admin login
Route::get('/login', fn() => redirect('/admin/login'))->name('login');

// Kiosk Absensi Routes - Protected by 'auth' middleware so only Admin can access
Route::middleware('auth')->group(function () {
    Route::get('/scan', \App\Livewire\AttendanceKiosk::class)->name('kiosk.scan');
    Route::post('/scan', function (\Illuminate\Http\Request $request, \App\Actions\ProcessScanAction $action) {
        $barcode = $request->input('barcode');
        if (!$barcode) {
            return response()->json(['status' => 'not_found']);
        }
        return response()->json($action->execute($barcode, $request->ip()));
    })->middleware('throttle:60,1')->name('kiosk.process');
});

// Wali Kelas Routes
Route::prefix('wali-kelas')->group(function () {
    Route::get('/login', WaliKelasLogin::class)->middleware('guest:wali_kelas')->name('wali-kelas.login');
    
    Route::middleware('auth.wali')->group(function () {
        Route::get('/', WaliKelasDashboard::class)->name('wali-kelas.dashboard');
        Route::get('/siswa/{id}', \App\Livewire\WaliKelasStudentDetail::class)->name('wali-kelas.student-detail');
        
        Route::post('/logout', function () {
            Auth::guard('wali_kelas')->logout();
            request()->session()->invalidate();
            request()->session()->regenerateToken();
            return redirect('/');
        })->name('wali-kelas.logout');
    });
});

// Siswa Routes
Route::prefix('siswa')->group(function () {
    Route::get('/login', SiswaLogin::class)->middleware('guest:siswa')->name('siswa.login');
    
    Route::middleware('auth.siswa')->group(function () {
        Route::get('/', SiswaDashboard::class)->name('siswa.dashboard');
        
        Route::post('/logout', function () {
            Auth::guard('siswa')->logout();
            request()->session()->invalidate();
            request()->session()->regenerateToken();
            return redirect('/');
        })->name('siswa.logout');
    });
});
