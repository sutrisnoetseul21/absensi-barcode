# Rancangan Arsitektur Modular ERP Sekolah

Dokumen ini berisi rangkuman diskusi awal mengenai rencana pengembangan ERP SMP Negeri 3 Kedungreja ke depan, khususnya jika sistem ini akan didistribusikan ke sekolah-sekolah lain dengan kebutuhan modul yang berbeda-beda (SaaS / Multi-Tenancy).

## 1. Latar Belakang Masalah
Sistem saat ini merupakan **Monolith** (satu aplikasi utuh) dengan fitur multi-panel Filament. 
Kebutuhan di masa depan:
- Bagaimana jika Sekolah A hanya membeli modul "Presensi"?
- Bagaimana jika Sekolah B hanya membeli modul "Perpustakaan"?
- Apakah perlu memecah aplikasi menjadi 3 aplikasi terpisah (Microservices) yang terhubung via API?

## 2. Kenapa Microservices (API) Kurang Cocok?
Memecah sistem menjadi 3 aplikasi (Aplikasi Master Data, Aplikasi Presensi, Aplikasi Perpustakaan) via API **kurang disarankan** karena:
1. **Kinerja (Latency):** Kiosk scan barcode dituntut sangat cepat. Jika harus mengambil data siswa lintas server via API, proses scan akan melambat drastis.
2. **Infrastruktur Rumit:** Memerlukan pemeliharaan 3 server/aplikasi berbeda dan sistem *Single Sign-On* (SSO) agar user tidak perlu login berulang kali.
3. **Inkonsistensi Data:** Sulit untuk membuat relasi tabel yang kuat antar database jika datanya terpisah secara fisik.

## 3. Solusi: "Modular Monolith" (Laravel Modules)
Pendekatan terbaik untuk kasus ini adalah **Modular Monolith** (seperti yang digunakan oleh ERP besar seperti Odoo/SAP). Aplikasi fisik dan database tetap SATU, namun secara kode dipisah ke dalam modul-modul independen (contohnya menggunakan *package* `nWidart/laravel-modules`).

Struktur bayangan:
```text
app/
 ├── Modules/
 │    ├── MasterData/ (Wajib ada di tiap sekolah)
 │    ├── Presensi/   (Bisa di-install / di-uninstall)
 │    └── Perpus/     (Bisa di-install / di-uninstall)
```

## 4. Studi Kasus: Modul Perpustakaan di `/portal-guru`
Jika menggunakan pendekatan Modular, bagaimana penanganan menu di portal utama?

### Isolasi Rute & Controller
Seluruh controller (Livewire `GuruPerpustakaan`) dan rutenya akan dipindah ke dalam `Modules/Perpustakaan`. Jika sekolah tidak membeli modul perpus, modul ini tidak akan di-install, sehingga server menjadi lebih ringan dan tabel database perpustakaan tidak akan ter-create.

### Penanganan Menu UI (Penting!)
Di file *layout* utama (misal `portal.blade.php`), kita **tidak boleh** melakukan *hardcode* menu seperti ini:
```html
<a href="{{ route('portal-guru.perpustakaan') }}">Perpustakaan</a>
```
Sebab, jika modul perpustakaan tidak di-install, fungsi `route()` akan memicu *Error (RouteNotFoundException)* dan membuat seluruh aplikasi *crash*.

**Solusinya:**
Panggil menu berdasarkan pengecekan eksistensi modul:
```blade
@if (Module::collections()->has('Perpustakaan'))
    <a href="{{ route('portal-guru.perpustakaan') }}">
        Perpustakaan
    </a>
@endif
```
Atau lebih baik lagi menggunakan **Dynamic Menu Builder**, di mana setiap modul yang aktif akan secara otomatis mendaftarkan menunya ke sebuah *Array Global* saat sistem di-load (booting).

## Kesimpulan
Pendekatan Modular memungkinkan kita menjual ERP ini secara "ketengan" (per modul) dengan sangat elegan, tanpa mengorbankan performa kecepatan (seperti kelemahan API) dan tetap menjaga kode terisolasi rapi.
