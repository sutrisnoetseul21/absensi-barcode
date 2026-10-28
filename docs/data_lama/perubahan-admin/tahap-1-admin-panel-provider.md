# Tahap 1 — Update `AdminPanelProvider` (Daftarkan Semua Resource)

**Status:** ⏳ Belum dikerjakan  
**Estimasi waktu:** ~15 menit  
**File yang diubah:** 1 file saja

---

## Tujuan

Memberitahu panel `/admin` agar mengenali dan mendaftarkan semua Resource & Page
dari folder Akademik, Presensi, dan Perpustakaan — sehingga semua menu bisa muncul
di sidebar panel `/admin`.

---

## File yang Diubah

### [MODIFY] `app/Providers/Filament/AdminPanelProvider.php`

---

## Perubahan Detail

### 1. Tambah `discoverResources()` dan `discoverPages()` untuk Semua Folder

**Sebelum (hanya mendaftarkan resource admin utama):**
```php
->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
```

**Sesudah (mendaftarkan SEMUA resource dari 4 folder):**
```php
// Admin Utama (sudah ada)
->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')

// Akademik
->discoverResources(in: app_path('Filament/Akademik/Resources'), for: 'App\Filament\Akademik\Resources')
->discoverPages(in: app_path('Filament/Akademik/Pages'), for: 'App\Filament\Akademik\Pages')

// Presensi
->discoverResources(in: app_path('Filament/Presensi/Resources'), for: 'App\Filament\Presensi\Resources')
->discoverPages(in: app_path('Filament/Presensi/Pages'), for: 'App\Filament\Presensi\Pages')

// Perpustakaan
->discoverResources(in: app_path('Filament/Perpustakaan/Resources'), for: 'App\Filament\Perpustakaan\Resources')
->discoverPages(in: app_path('Filament/Perpustakaan/Pages'), for: 'App\Filament\Perpustakaan\Pages')
```

---

### 2. Update `navigationGroups`

**Sebelum:**
```php
->navigationGroups([
    'Data Master',
    'Akademik',
    'Presensi',
    'Laporan',
    'Konten',
    'Pengaturan Sistem',
])
```

**Sesudah:**
```php
->navigationGroups([
    'Data Master',
    'Akademik',
    'Presensi',
    'Perpustakaan',
    'Pengaturan Sistem',
])
```

> Grup `Laporan` dan `Konten` dihapus karena sudah tercakup dalam tiap grup fungsional masing-masing.

---

### 3. Tambah Widget dari Panel Lain

**Sebelum:**
```php
->widgets([
    \App\Filament\Widgets\PortalWidget::class,
    QuickLinksWidget::class,
    AccountWidget::class,
])
```

**Sesudah (QuickLinksWidget & PortalWidget dihapus dari sini, diganti di Tahap 3):**
```php
->widgets([
    AccountWidget::class,
])
```

> ⚠️ Widget lengkap baru akan ditambahkan di **Tahap 3** (Dashboard Gabungan).
> Di Tahap 1 ini, kita biarkan widget minimal dulu agar tidak error saat dicek.

---

### 4. Tambah `discoverWidgets()` untuk Semua Folder Widget

**Sebelum:**
```php
->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
```

**Sesudah:**
```php
->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
->discoverWidgets(in: app_path('Filament/Akademik/Widgets'), for: 'App\Filament\Akademik\Widgets')
->discoverWidgets(in: app_path('Filament/Perpustakaan/Widgets'), for: 'App\Filament\Perpustakaan\Widgets')
```

---

### 5. Sembunyikan Menu Shield dari Sidebar

**Sebelum:**
```php
->plugins([
    FilamentShieldPlugin::make(),
])
```

**Sesudah:**
```php
->plugins([
    FilamentShieldPlugin::make()
        ->gridColumns([
            'default' => 1,
            'sm' => 2,
            'lg' => 3
        ])
        ->sectionColumnSpan(1)
        ->checkboxListColumns([
            'default' => 1,
            'sm' => 2,
            'lg' => 4,
        ])
        ->resourceCheckboxListColumns([
            'default' => 1,
            'sm' => 2,
        ]),
])
```

> ℹ️ Untuk menyembunyikan menu Roles dari sidebar, kita akan menambahkan
> `navigationSort` negatif atau override `canViewAny()` di Shield Resource.
> Detail teknis dikerjakan saat tahap ini dieksekusi.

---

## Hasil yang Diharapkan Setelah Tahap 1

Setelah tahap ini selesai dan server di-restart:

- ✅ Semua menu dari Admin Akademik muncul di sidebar `/admin`
- ✅ Semua menu dari Admin Presensi muncul di sidebar `/admin`
- ✅ Semua menu dari Admin Perpustakaan muncul di sidebar `/admin`
- ✅ Sidebar terbagi per grup: Data Master, Akademik, Presensi, Perpustakaan, Pengaturan Sistem
- ⚠️ Urutan menu di dalam tiap grup mungkin masih acak (baru dirapikan di Tahap 2)
- ⚠️ Dashboard mungkin masih menampilkan widget lama (baru digabung di Tahap 3)
- ⚠️ Semua role sudah bisa masuk `/admin` setelah Tahap 4 selesai

---

## Cara Verifikasi Setelah Tahap 1

1. Buka `https://coba.haflaitsolution.my.id/admin` sebagai Super Admin
2. Cek sidebar — pastikan ada menu-menu dari Akademik, Presensi, dan Perpustakaan
3. Klik beberapa menu tersebut — pastikan tidak ada error 404 atau 500
4. Cek `/admin-akademik` — pastikan masih bisa dibuka (belum rusak)
