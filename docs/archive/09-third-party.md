# 09. Third Party

**Scanner barcode:** pakai scanner USB mode *keyboard wedge* — tidak butuh library khusus, tinggal fokuskan input text field lalu handle event `keypress`/`Enter` di frontend.

---

## Stack yang Digunakan (Opsi A — Laravel + Filament)

```
Framework    : Laravel 12 (PHP 8.2+)
Frontend     : Livewire v3 · Tailwind CSS v3 · Alpine.js v3
Admin Panel  : Filament v4
Database     : MySQL 8+
```

---

## Autentikasi Multi-Guard

| Guard | Tabel | Model | Halaman Login |
|---|---|---|---|
| `web` (default Filament) | `users` | `User` | `/admin` (Filament) |
| `wali_kelas` | `teachers` | `Teacher` | `/wali-kelas/login` (Livewire custom) |
| `siswa` | `students` | `Student` | `/siswa/login` (Livewire custom) |

> Middleware terpisah per guard: wali kelas tidak bisa mengakses route siswa dan sebaliknya.

---

## Library Pendukung

| Keperluan | Library | Keterangan |
|---|---|---|
| Grafik dashboard | `Chart.js` atau `ApexCharts` | Ringan, banyak contoh, cocok untuk dashboard interaktif |
| Kalender hari libur | `FullCalendar` | Klik-untuk-tambah, drag-and-drop |
| Generate barcode kartu siswa | `picqer/php-barcode-generator` | PHP, untuk generate barcode di PDF |
| Barcode di sisi JS (preview) | `JsBarcode` | Opsional, untuk preview kartu di browser |
| Export PDF | `barryvdh/laravel-dompdf` | Untuk cetak kartu OSIS dan laporan |
| Import/Export Excel | `Maatwebsite/Laravel-Excel` | Untuk import siswa & import kenaikan kelas |
| Role/Permission admin panel | Filament Shield (opsional) | Untuk beda hak Super Admin vs Admin di dalam Filament |
| Notifikasi (fase lanjut) | Fonnte/WhatsApp Gateway | Kirim notif ke orang tua — fase 2 |

---

## Catatan Penting Library

- **`Maatwebsite/Laravel-Excel`** dipakai untuk dua alur: import siswa dan **import kenaikan kelas via Excel** (template download → isi kelas baru → upload kembali).
- **`barryvdh/laravel-dompdf`** harus bisa mengakses file logo dari `school_settings.school_logo_path` untuk di-embed ke PDF kartu OSIS.
- **Filament v4** kompatibel dengan Livewire v3. Gunakan Filament docs v4 — bukan v5.
