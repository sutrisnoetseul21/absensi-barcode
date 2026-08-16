# Tahap 3 — Dashboard `/admin` Gabungan (Tanpa Quick Links)

**Status:** ⏳ Belum dikerjakan (tunggu Tahap 2 selesai & disetujui)  
**Estimasi waktu:** ~20 menit  
**Jumlah file yang diubah:** 1 file utama + update `AdminPanelProvider`

---

## Tujuan

Menyatukan semua widget dari 4 dashboard menjadi 1 dashboard terpadu di `/admin`.
Widget **QuickLinksWidget** dan **PortalWidget** **tidak** dimasukkan ke dashboard gabungan ini.

---

## Widget yang Ada Saat Ini

### Panel `/admin` (Admin Utama)
| Widget | Kelas | Isi |
|---|---|---|
| PortalWidget | `App\Filament\Widgets\PortalWidget` | Link antar portal (❌ tidak dimasukkan) |
| QuickLinksWidget | `App\Filament\Widgets\QuickLinksWidget` | Pintasan cepat (❌ tidak dimasukkan) |
| AccountWidget | Filament bawaan | Info akun yang login |

### Panel `/admin-akademik`
| Widget | Kelas | Isi |
|---|---|---|
| AkademikStatsWidget | `App\Filament\Akademik\Widgets\AkademikStatsWidget` | Stat: Siswa, Guru, Kelas, Tahun Ajaran |

### Panel `/admin-presensi`
| Widget | Kelas | Isi |
|---|---|---|
| AdminStatsOverview | `App\Filament\Widgets\AdminStatsOverview` | Stat presensi hari ini |
| PresensiStatusDonutChart | `App\Filament\Widgets\PresensiStatusDonutChart` | Donut chart status hadir/izin/sakit/alpa |
| AdminAttendanceChart | `App\Filament\Widgets\AdminAttendanceChart` | Chart kehadiran mingguan |
| ProblematicStudentsTable | `App\Filament\Widgets\ProblematicStudentsTable` | Tabel siswa sering alpa |

### Panel `/admin-perpustakaan`
| Widget | Kelas | Isi |
|---|---|---|
| PerpustakaanStatsWidget | `App\Filament\Perpustakaan\Widgets\PerpustakaanStatsWidget` | Stat: pinjam, terlambat, tersedia |
| BukuTerpopulerWidget | `App\Filament\Perpustakaan\Widgets\BukuTerpopulerWidget` | Tabel buku paling sering dipinjam |
| TerlambatKritisWidget | `App\Filament\Perpustakaan\Widgets\TerlambatKritisWidget` | Tabel peminjam melewati jatuh tempo |
| SirkulasiBulananChart | `App\Filament\Perpustakaan\Widgets\SirkulasiBulananChart` | Chart sirkulasi per bulan |

---

## Susunan Widget Dashboard Gabungan (Urut dari Atas ke Bawah)

```
1. AccountWidget                   (bawaan Filament - info akun)
2. AkademikStatsWidget             (stat: siswa, guru, kelas)
3. AdminStatsOverview              (stat presensi hari ini)
4. PerpustakaanStatsWidget         (stat: pinjam, terlambat, tersedia)
   ─── Baris Stats selesai ───────────────────────────────────────
5. PresensiStatusDonutChart        (donut: komposisi kehadiran hari ini)
6. AdminAttendanceChart            (chart kehadiran mingguan)
7. SirkulasiBulananChart           (chart sirkulasi perpus bulanan)
   ─── Charts selesai ─────────────────────────────────────────────
8. TerlambatKritisWidget           (tabel peminjam terlambat)
9. BukuTerpopulerWidget            (tabel buku paling laris)
10. ProblematicStudentsTable       (tabel siswa sering alpa)
```

---

## File yang Diubah

### [MODIFY] `app/Filament/Pages/Dashboard.php`

Dashboard admin utama perlu ditambahkan:
1. Header action "Tandai Alpa" dari Presensi Dashboard
2. `getWidgets()` yang mengembalikan susunan widget gabungan di atas

**Sebelum:**
```php
<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    // Kosong
}
```

**Sesudah:**
```php
<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Actions\Action;
use App\Services\KalenderSekolahService;
use App\Models\EnrollmentSiswa;
use App\Models\Presensi;
use App\Models\PengaturanSekolah;
use Filament\Notifications\Notification;
use Illuminate\Support\HtmlString;

class Dashboard extends BaseDashboard
{
    protected static ?string $title = 'Dashboard';

    protected function getHeaderActions(): array
    {
        // Tombol "Tandai Alpa" dipindahkan dari Presensi Dashboard
        $today = now('Asia/Jakarta');
        $kalenderService = app(KalenderSekolahService::class);
        $isHariSekolahGlobal = $kalenderService->isHariSekolah($today);

        if (!$isHariSekolahGlobal) {
            return [];
        }

        // ... (logika sama persis dengan Presensi/Pages/Dashboard.php)
    }

    public function getWidgets(): array
    {
        return [
            \Filament\Widgets\AccountWidget::class,
            \App\Filament\Akademik\Widgets\AkademikStatsWidget::class,
            \App\Filament\Widgets\AdminStatsOverview::class,
            \App\Filament\Perpustakaan\Widgets\PerpustakaanStatsWidget::class,
            \App\Filament\Widgets\PresensiStatusDonutChart::class,
            \App\Filament\Widgets\AdminAttendanceChart::class,
            \App\Filament\Perpustakaan\Widgets\SirkulasiBulananChart::class,
            \App\Filament\Perpustakaan\Widgets\TerlambatKritisWidget::class,
            \App\Filament\Perpustakaan\Widgets\BukuTerpopulerWidget::class,
            \App\Filament\Widgets\ProblematicStudentsTable::class,
        ];
    }
}
```

---

### [MODIFY] `app/Providers/Filament/AdminPanelProvider.php`

Update bagian `widgets()` di AdminPanelProvider — **hapus** QuickLinksWidget & PortalWidget,
serahkan kontrol widget sepenuhnya ke `Dashboard::getWidgets()`:

```php
->widgets([])   // Dikosongkan — semua widget dikelola di Dashboard::getWidgets()
```

Dan tambah `discoverWidgets()` untuk widget perpustakaan dan akademik
(sudah direncanakan di Tahap 1, dicatat ulang di sini untuk konfirmasi):

```php
->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
->discoverWidgets(in: app_path('Filament/Akademik/Widgets'), for: 'App\Filament\Akademik\Widgets')
->discoverWidgets(in: app_path('Filament/Perpustakaan/Widgets'), for: 'App\Filament\Perpustakaan\Widgets')
```

---

## Cara Verifikasi Setelah Tahap 3

1. Buka `https://coba.haflaitsolution.my.id/admin` (dashboard)
2. Cek ada 10 widget yang muncul dari atas ke bawah
3. Tidak ada widget "Quick Links" atau "Portal Links" yang muncul
4. Tombol "Tandai Alpa (Hari Ini)" muncul di header dashboard (saat hari sekolah)
5. Semua data widget ter-load dengan benar (tidak ada error widget)
