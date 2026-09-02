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
use App\Http\Controllers\BerandaController;

// Beranda Profil Sekolah (Halaman Publik Utama)
Route::get('/', [BerandaController::class, 'index'])->name('beranda');
Route::get('/home', [BerandaController::class, 'index'])->name('home');
Route::get('/guru', [BerandaController::class, 'guru'])->name('guru.all');
Route::get('/direktori-guru', [BerandaController::class, 'guru'])->name('beranda.guru');
Route::get('/berita/{slug}', [BerandaController::class, 'artikel'])->name('beranda.artikel');
Route::get('/pengaduan', [BerandaController::class, 'pengaduan'])->name('pengaduan.index');
Route::post('/pengaduan', [BerandaController::class, 'pengaduanStore'])->name('pengaduan.store')->middleware('throttle:30,1');

// ERP Portal Route (lama, dipertahankan)
Route::get('/erp-portal', fn() => view('erp-portal'))->name('erp.portal');

// Hub Pemilihan Portal untuk pengguna yang sudah login
Route::get('/pilih-portal', function () {
    $user = Auth::user();
    if (!$user) {
        return redirect()->route('login');
    }
    $accessiblePortals = $user->getAccessiblePortals();
    if (count($accessiblePortals) === 1) {
        return redirect($accessiblePortals[0]['url']);
    }
    return view('pilih-portal', compact('accessiblePortals'));
})->middleware('auth')->name('pilih-portal');

// Route fallback untuk redirect unauthenticated users ke Filament admin login
Route::redirect('/pilih-admin', '/admin/login')->name('pilih-admin');
Route::get('/presensi', PublicDashboard::class)->name('public.dashboard');
Route::get('/presensi/dashboardv1', PublicDashboardV1::class)->name('public.dashboard.v1');
Route::get('/presensi/display', PublicDashboard::class)->name('public.display');

// API Client-Side JS Cache Katalog Buku
Route::get('/api/katalog-buku-cache', [\App\Http\Controllers\Api\KatalogApiController::class, 'getCatalogJson'])
    ->middleware('throttle:120,1')
    ->name('api.katalog-buku.cache');

// Dashboard Publik Perpustakaan
Route::get('/perpustakaan', KatalogPerpustakaan::class)
    ->middleware('throttle:60,1')
    ->name('perpustakaan.dashboard');

// E-Library: Baca Buku Online
Route::get('/perpustakaan/buku/{buku}/baca', function (\App\Models\Buku $buku) {
    if (!$buku->file_pdf) {
        abort(404, 'Buku ini tidak memiliki file PDF untuk dibaca online.');
    }
    return view('baca-buku', compact('buku'));
})->name('perpustakaan.baca-buku');

// Redirect URL panel lama ke panel /admin terpadu
Route::redirect('/admin-akademik', '/admin');
Route::redirect('/admin-presensi', '/admin');
Route::redirect('/admin-perpustakaan', '/admin');
Route::any('/admin-akademik/{any}', fn() => redirect('/admin'))->where('any', '.*');
Route::any('/admin-presensi/{any}', fn() => redirect('/admin'))->where('any', '.*');

// Unified Login Route
Route::get('/login', \App\Livewire\Auth\UnifiedLogin::class)->name('login')->middleware('guest');

Route::post('/logout', function () {
    Auth::guard('web')->logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect('/');
})->name('logout');


// Kiosk Absensi Routes - Portal Presensi Kiosk
Route::prefix('portal-presensi')->group(function () {
    // Route dashboard Portal Presensi (sementara menggunakan tampilan kosong)
    Route::get('/', \App\Livewire\PortalPresensiDashboard::class)->middleware('auth.presensi')->name('portal-presensi.dashboard');

    Route::get('/login', \App\Livewire\PetugasPresensiLogin::class)->middleware('guest')->name('portal-presensi.login');
    
    Route::middleware('auth.presensi')->group(function () {
        Route::get('/input-manual', \App\Livewire\PortalPresensi\InputPresensiManual::class)->name('portal-presensi.input-manual');
        Route::get('/rekap-kelas', \App\Livewire\PortalPresensi\RekapAbsensiKelas::class)->name('portal-presensi.rekap-kelas');
        Route::get('/rekap-sekolah', \App\Livewire\PortalPresensi\RekapAbsensiSekolah::class)->name('portal-presensi.rekap-sekolah');
        Route::get('/cetak-laporan', \App\Livewire\PortalPresensi\CetakLaporanPresensi::class)->name('portal-presensi.cetak-laporan');
        Route::get('/cetak-kartu', \App\Livewire\PortalPresensi\CetakKartuSiswa::class)->name('portal-presensi.cetak-kartu');
        Route::get('/setting-notifikasi', \App\Livewire\PortalPresensi\SettingNotifikasi::class)->name('portal-presensi.setting-notifikasi');

        Route::post('/logout', function () {
            \Illuminate\Support\Facades\Auth::guard('web')->logout();
            request()->session()->invalidate();
            request()->session()->regenerateToken();
            return redirect('/');
        })->name('portal-presensi.logout');

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

        // Route legacy / fallback untuk scanner presensi
        Route::get('/scanner', \App\Livewire\AttendanceKiosk::class);
        Route::get('/scanner-nis', \App\Livewire\AttendanceKioskNIS::class);
        Route::get('/kiosk', \App\Livewire\AttendanceKiosk::class);

        // Route fallback untuk API lama jika ada
        Route::post('/scan/process', function (\Illuminate\Http\Request $request, \App\Actions\ProcessAttendanceAction $action) {
            $barcode = $request->input('barcode');
            $session = $request->input('session', 'masuk');
            return response()->json($action->execute((string) $barcode, (string) $session));
        })->middleware('throttle:60,1')->name('portal-presensi.scan.process');
    });
});

Route::middleware('auth')->group(function () {

    // Cetak Kartu Routes
    Route::get('/admin/siswa/{siswa}/cetak-kartu', [\App\Http\Controllers\SiswaCetakController::class, 'cetakKartu'])->name('siswa.cetak-kartu');
    Route::get('/admin/siswa/{siswa}/cetak-kartu-login', [\App\Http\Controllers\SiswaCetakController::class, 'cetakKartuLogin'])->name('siswa.cetak-kartu-login');
    Route::get('/admin/siswa/cetak-kartu-massal', [\App\Http\Controllers\SiswaCetakController::class, 'cetakKartuMassal'])->name('siswa.cetak-kartu-massal');
    Route::get('/admin/siswa/cetak-kartu-login-massal', [\App\Http\Controllers\SiswaCetakController::class, 'cetakKartuLoginMassal'])->name('siswa.cetak-kartu-login-massal');

    // Cetak Barcode Perpustakaan Routes
    Route::get('/admin/perpustakaan/buku/{buku}/cetak-barcode', [\App\Http\Controllers\EksemplarCetakController::class, 'cetakBarcode'])->name('perpustakaan.cetak-barcode');
    Route::get('/admin/perpustakaan/eksemplar/{eksemplar}/cetak-barcode', [\App\Http\Controllers\EksemplarCetakController::class, 'cetakBarcodeEksemplar'])->name('perpustakaan.cetak-barcode-eksemplar');
    Route::get('/admin/perpustakaan/eksemplar/cetak-barcode-massal', [\App\Http\Controllers\EksemplarCetakController::class, 'cetakBarcodeMassal'])->name('perpustakaan.cetak-barcode-massal');
    
    // Cetak Label Spine Buku Routes
    Route::get('/admin/perpustakaan/buku/{buku}/cetak-label-spine', [\App\Http\Controllers\EksemplarCetakController::class, 'cetakLabelSpine'])->name('perpustakaan.cetak-label-spine');
    Route::get('/admin/perpustakaan/eksemplar/{eksemplar}/cetak-label-spine', [\App\Http\Controllers\EksemplarCetakController::class, 'cetakLabelSpineEksemplar'])->name('perpustakaan.cetak-label-spine-eksemplar');
    Route::get('/admin/perpustakaan/buku/cetak/label-spine-massal', [\App\Http\Controllers\EksemplarCetakController::class, 'cetakLabelSpineMassal'])->name('perpustakaan.cetak-label-spine-massal');
    
    // Cetak Label Gabungan Buku Routes
    Route::get('/admin/perpustakaan/buku/{buku}/cetak-label-gabungan', [\App\Http\Controllers\EksemplarCetakController::class, 'cetakLabelGabungan'])->name('perpustakaan.cetak-label-gabungan');
    Route::get('/admin/perpustakaan/eksemplar/{eksemplar}/cetak-label-gabungan', [\App\Http\Controllers\EksemplarCetakController::class, 'cetakLabelGabunganEksemplar'])->name('perpustakaan.cetak-label-gabungan-eksemplar');
    Route::get('/admin/perpustakaan/buku/cetak/label-gabungan-massal', [\App\Http\Controllers\EksemplarCetakController::class, 'cetakLabelGabunganMassal'])->name('perpustakaan.cetak-label-gabungan-massal');
    
    // Kiosk Sirkulasi Perpustakaan Routes
    Route::get('/admin/perpustakaan/sirkulasi', \App\Livewire\SirkulasiKiosk::class)->name('perpustakaan.sirkulasi');
    Route::post('/admin/perpustakaan/sirkulasi/process', function (\Illuminate\Http\Request $request, \App\Actions\ProcessSirkulasiAction $action) {
        return response()->json($action->execute($request->all(), auth()->id()));
    })->name('perpustakaan.sirkulasi.process');

    // Kiosk Kunjungan Perpustakaan Routes
    Route::get('/perpustakaan/kunjungan', \App\Livewire\KunjunganKiosk::class)->middleware('auth.perpus')->name('perpustakaan.kunjungan');
    Route::post('/perpustakaan/kunjungan/process', function (\Illuminate\Http\Request $request, \App\Actions\ProcessKunjunganAction $action) {
        $barcode = $request->input('barcode');
        $tujuan = $request->input('tujuan_kunjungan', 'Membaca / Belajar');
        return response()->json($action->execute((string) $barcode, auth()->id(), (string) $tujuan));
    })->middleware(['throttle:60,1', 'auth.perpus'])->name('perpustakaan.kunjungan.process');

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

    // Route Download Template & Data Excel
    Route::get('/admin/siswa/download-template', function () {
        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\SiswaBaruTemplateExport,
            'template_siswa_baru.xlsx'
        );
    })->name('admin.siswa.download-template');

    Route::get('/admin/siswa/download-data', function () {
        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\SiswaUpdateDataExport,
            'update_data_siswa.xlsx'
        );
    })->name('admin.siswa.download-data');

    Route::get('/admin/siswa/download-nohp', function () {
        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\SiswaUpdateNoHpExport,
            'update_kontak_siswa.xlsx'
        );
    })->name('admin.siswa.download-nohp');

    Route::get('/admin/guru/download-template', function () {
        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\GuruTemplateExport,
            'template_guru.xlsx'
        );
    })->name('admin.guru.download-template');

    Route::get('/admin/kelas/download-template', function () {
        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\KelasTemplateExport,
            'template_kelas.xlsx'
        );
    })->name('admin.kelas.download-template');

    // Katalog Buku: Unduh PDF & Excel (dengan filter koleksi & mapel)
    Route::get('/admin/perpustakaan/katalog-buku/pdf', [\App\Http\Controllers\KatalogBukuController::class, 'downloadPdf'])
        ->name('perpustakaan.katalog-buku.pdf');
    Route::get('/admin/perpustakaan/katalog-buku/excel', [\App\Http\Controllers\KatalogBukuController::class, 'downloadExcel'])
        ->name('perpustakaan.katalog-buku.excel');

    // Inventaris Buku: Unduh PDF & Excel (dengan filter status)
    Route::get('/admin/perpustakaan/inventaris-buku/pdf', [\App\Http\Controllers\KatalogBukuController::class, 'downloadInventarisPdf'])
        ->name('perpustakaan.inventaris-buku.pdf');
    Route::get('/admin/perpustakaan/inventaris-buku/excel', [\App\Http\Controllers\KatalogBukuController::class, 'downloadInventarisExcel'])
        ->name('perpustakaan.inventaris-buku.excel');

    // Peminjaman Buku: Unduh PDF & Excel
    Route::get('/admin/perpustakaan/peminjaman-buku/pdf', [\App\Http\Controllers\KatalogBukuController::class, 'downloadPeminjamanPdf'])
        ->name('perpustakaan.peminjaman-buku.pdf');
    Route::get('/admin/perpustakaan/peminjaman-buku/excel', [\App\Http\Controllers\KatalogBukuController::class, 'downloadPeminjamanExcel'])
        ->name('perpustakaan.peminjaman-buku.excel');

    // Kunjungan Perpustakaan: Unduh PDF & Excel
    Route::get('/admin/perpustakaan/kunjungan-perpustakaan/pdf', [\App\Http\Controllers\KatalogBukuController::class, 'downloadKunjunganPdf'])
        ->name('perpustakaan.kunjungan.pdf');
    Route::get('/admin/perpustakaan/kunjungan-perpustakaan/excel', [\App\Http\Controllers\KatalogBukuController::class, 'downloadKunjunganExcel'])
        ->name('perpustakaan.kunjungan.excel');
});


// Wali Kelas Routes (Portal Guru)
Route::prefix('portal-guru')->middleware('maintenance:guru')->group(function () {
    Route::get('/login', WaliKelasLogin::class)->middleware('guest')->name('portal-guru.login');
    
    Route::middleware('auth.wali')->group(function () {
        Route::get('/', \App\Livewire\GuruMainDashboard::class)->name('portal-guru.dashboard');
        Route::get('/akademik', WaliKelasDashboard::class)->name('portal-guru.akademik');
        Route::get('/perpustakaan', \App\Livewire\GuruPerpustakaan::class)->name('portal-guru.perpustakaan');
        Route::get('/siswa/{id}', \App\Livewire\WaliKelasStudentDetail::class)->name('portal-guru.student-detail');
        Route::get('/data-siswa', \App\Livewire\PortalGuru\DataSiswaList::class)->name('portal-guru.data-siswa');
        
        Route::get('/ijin-kehadiran', \App\Livewire\PortalGuru\IjinKehadiranList::class)->name('portal-guru.ijin');
        Route::get('/ijin-kehadiran/{id}', \App\Livewire\PortalGuru\IjinKehadiranDetail::class)->name('portal-guru.ijin.detail');
        Route::get('/profil', \App\Livewire\GuruProfil::class)->name('portal-guru.profil');

        Route::post('/logout', function () {
            Auth::guard('web')->logout();
            request()->session()->invalidate();
            request()->session()->regenerateToken();
            return redirect('/');
        })->name('portal-guru.logout');
    });
});

// Siswa Routes (Portal Siswa)
Route::prefix('portal-siswa')->middleware('maintenance:siswa')->group(function () {
    Route::get('/login', SiswaLogin::class)->middleware('guest')->name('portal-siswa.login');
    
    Route::middleware('auth.siswa')->group(function () {
        Route::get('/', \App\Livewire\SiswaMainDashboard::class)->name('portal-siswa.dashboard');
        Route::get('/akademik', SiswaDashboard::class)->name('portal-siswa.akademik');
        Route::get('/profil', SiswaProfil::class)->name('portal-siswa.profil');
        Route::get('/perpustakaan', \App\Livewire\SiswaPerpustakaan::class)->name('portal-siswa.perpustakaan');
        Route::get('/cetak-kartu', [\App\Http\Controllers\SiswaCetakController::class, 'cetakKartuMandiri'])->name('portal-siswa.cetak-kartu');
        
        Route::get('/ijin-kehadiran', \App\Livewire\PortalSiswa\IjinKehadiranList::class)->name('portal-siswa.ijin');
        Route::get('/ijin-kehadiran/form/{id?}', \App\Livewire\PortalSiswa\IjinKehadiranForm::class)->name('portal-siswa.ijin.form');
        
        Route::post('/logout', function () {
            Auth::guard('web')->logout();
            request()->session()->invalidate();
            request()->session()->regenerateToken();
            return redirect('/');
        })->name('portal-siswa.logout');
    });
});

// Petugas Perpustakaan Routes (Portal Perpustakaan)
Route::prefix('portal-perpustakaan')->middleware('maintenance:perpustakaan')->group(function () {
    Route::get('/login', \App\Livewire\PetugasPerpusLogin::class)->middleware('guest')->name('portal-perpustakaan.login');
    
    Route::middleware('auth.perpus')->group(function () {
        Route::get('/', \App\Livewire\PetugasPerpusDashboard::class)->name('portal-perpustakaan.dashboard');
        Route::get('/buku', \App\Livewire\PetugasPerpusBuku::class)->name('portal-perpustakaan.buku');
        Route::get('/riwayat-hapus-buku', \App\Livewire\PortalPerpustakaan\RiwayatHapusBuku::class)->name('portal-perpustakaan.riwayat-hapus-buku');
        Route::get('/inventaris', \App\Livewire\PetugasPerpusInventaris::class)->name('portal-perpustakaan.inventaris');
        Route::get('/sirkulasi', \App\Livewire\PetugasPerpusSirkulasi::class)->name('portal-perpustakaan.sirkulasi');
        Route::get('/peminjaman', \App\Livewire\PetugasPerpusPeminjaman::class)->name('portal-perpustakaan.peminjaman');
        Route::get('/kunjungan', \App\Livewire\PetugasPerpusKunjungan::class)->name('portal-perpustakaan.kunjungan');
        Route::get('/cetak-kartu', \App\Livewire\PortalPresensi\CetakKartuSiswa::class)->name('portal-perpustakaan.cetak-kartu');
        Route::get('/klasifikasi-ddc', \App\Livewire\PortalPerpustakaan\KlasifikasiDdc::class)->name('portal-perpustakaan.klasifikasi-ddc');
        
        // Kiosk Sirkulasi (Mode Layar Penuh) 
        Route::get('/sirkulasi-kiosk', \App\Livewire\SirkulasiKiosk::class)->name('portal-perpustakaan.sirkulasi-kiosk');
        Route::post('/sirkulasi-kiosk/process', function (\Illuminate\Http\Request $request, \App\Actions\ProcessSirkulasiAction $action) {
            return response()->json($action->execute($request->all(), auth()->id()));
        })->name('portal-perpustakaan.sirkulasi-kiosk.process');
        
        Route::post('/logout', function () {
            Auth::guard('web')->logout();
            request()->session()->invalidate();
            request()->session()->regenerateToken();
            return redirect('/');
        })->name('portal-perpustakaan.logout');
    });
});
