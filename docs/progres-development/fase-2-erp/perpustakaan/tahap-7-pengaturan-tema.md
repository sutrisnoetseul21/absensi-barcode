# Modul Perpustakaan ERP - Tahap 7: Pengaturan Tema Dinamis

Tahap ini berfokus pada pembuatan fitur administrasi kustomisasi tema visual website secara dinamis (tanpa re-build aset statis Tailwind CSS), yang dikhususkan untuk halaman publik (`/perpustakaan` dan potensialnya `/presensi`).

## Konsep dan Arsitektur

Fitur ini memanfaatkan nilai *hex* warna dasar (seperti `brand-primary`, `brand-secondary`) yang ditimpa pada saat *runtime* (ketika halaman di-render) lewat variabel CSS (`:root`).

### 1. Database (Singleton Configuration)
Tidak menggunakan tabel baru. Kami memilih menambahkan 6 kolom tema ke dalam tabel `school_settings` yang sudah ada:
- `theme_primary`
- `theme_secondary`
- `theme_accent`
- `theme_warning`
- `theme_danger`
- `theme_info`

**Alasan**: `school_settings` adalah satu-satunya konfigurasi *singleton* untuk identitas sekolah. Karena parameter warna tema merupakan bagian dari identitas *branding* sekolah, menyimpannya di sini mencegah query berlebihan. *Cache* untuk tabel ini (`public_pengaturan_sekolah`) juga sudah diaplikasikan secara terpusat.

### 2. Filament Admin Interface
- Halaman konfigurasi baru **Pengaturan Tema** (`ThemeSettingsPage`) dibuat menggunakan `Page` khusus yang ditempatkan di bawah grup menu **Pengaturan Sistem**.
- Input divalidasi dan dibangun menggunakan komponen antarmuka native `ColorPicker` bawaan Filament. Hal ini menjamin:
  - Kemudahan penggunaan bagi user awam (Visual Palette).
  - Keamanan dari *CSS Injection*, karena `ColorPicker` memvalidasi input sebagai string format hex eksak `#xxxxxx`.
- **Fitur Reset Tema**: Tersedia sebuah _Action Button_ "Reset ke Tema Bawaan" di bagian atas halaman (Header Action) berwarna merah. Jika ditekan, tombol ini akan meminta konfirmasi, lalu mengosongkan semua isian kustomisasi tema dan mengembalikan tampilan website ke warna standar/default seketika itu juga.

### 3. Rendering dan Cache Invalidation
Pada saat admin menekan tombol "Simpan" di halaman pengaturan tema:
1. Data akan di-update ke *database*.
2. Metode `save()` memanggil fungsi invalidasi secara manual: `Cache::forget('public_pengaturan_sekolah');`. Ini penting agar halaman `/perpustakaan` yang membaca cache *settings* tersebut segera memperbarui tampilannya *detik itu juga*.

Pada `resources/views/components/layouts/app.blade.php`, kode disisipkan di dalam blok `<head>` setelah pemanggilan aset statis Vite:

```blade
@php
    $themeSettings = \App\Models\PengaturanSekolah::current();
@endphp
@if($themeSettings)
    <style>
        :root {
            @if($themeSettings->theme_primary) --color-brand-primary: {{ $themeSettings->theme_primary }}; @endif
            /* dan seterusnya... */
        }
    </style>
@endif
```

Bila *admin* mengosongkan nilai *color picker*, blok kondisi *if* tidak akan mencetak atribut tersebut. Sistem lalu melakukan _fallback_ kepada definisi baku di dalam `resources/css/app.css` (yang mana telah dipetakan dengan standar hex bawaan Tailwind).

### 4. Migrasi Halaman Presensi Publik (`/presensi`)
Halaman `/presensi` (`livewire/public-dashboard.blade.php` dan `components/public-dashboard/hero.blade.php`) telah **100% dimigrasikan** ke sistem tema terpusat ini (`brand-*`). Semua kelas warna *hardcode* (`indigo-*`, `violet-*`, `emerald-*`, dll.) telah digantikan dengan token tema `brand-primary`, `brand-secondary`, `brand-accent`, `brand-warning`, `brand-danger`, `brand-info` dan varian `tint/shade`-nya (`-light`, `-dark`, `-50`, `-100`, `-950`).

Dengan demikian, baik halaman `/perpustakaan` maupun `/presensi` kini merespons secara otomatis dan seragam terhadap Pengaturan Tema dari Panel Admin.
