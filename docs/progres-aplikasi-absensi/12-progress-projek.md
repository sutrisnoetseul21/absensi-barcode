# 12. Progress Proyek — Panduan Implementasi Bertahap untuk AI Agent

> **PENTING UNTUK AI AGENT:**
> Baca file ini dari awal sebelum menulis kode apapun.
> File ini adalah **panduan eksekusi bertahap** — kerjakan satu tahap, verifikasi, baru lanjut ke tahap berikutnya.
> Jangan skip tahap. Jangan kerjakan lebih dari satu tahap dalam satu sesi tanpa konfirmasi pengguna.

---

## 🗺️ Peta Dokumen Wajib Baca

Sebelum memulai sesi coding apapun, AI agent WAJIB membaca dokumen berikut:

| Prioritas | File | Tujuan |
|-----------|------|--------|
| 1 | [`README-DOCS.md`](../../README-DOCS.md) | Tech stack, aturan larangan, cara jalankan dev |
| 2 | [`docs/stack/PROJECT-STACK-MYSQL.md`](../stack/PROJECT-STACK-MYSQL.md) | Konvensi MySQL: UUID, migration, model |
| 3 | [`docs/stack/LIVEWIRE-V3-GUIDE.md`](../stack/LIVEWIRE-V3-GUIDE.md) | Livewire v3 — wajib setiap bikin komponen |
| 4 | [`docs/stack/ALPINE-V3-GUIDE.md`](../stack/ALPINE-V3-GUIDE.md) | Alpine.js v3 — khusus interaktivitas kios |
| 5 | [`docs/stack/FILAMENT-V4-INTEGRATION.md`](../stack/FILAMENT-V4-INTEGRATION.md) | Filament v4 — wajib sebelum buat Resource |
| 6 | [`docs/blueprint/05-database.md`](05-database.md) | Skema database dan relasi |
| 7 | [`docs/blueprint/06-business-rules.md`](06-business-rules.md) | Aturan bisnis (jam absen, debounce, formula) |
| 8 | [`docs/progres-aplikasi-absensi/`](../progres-aplikasi-absensi/) | Detail eksekusi progres & keputusan teknis Tahap 1 s/d 12 |

---

## ⚙️ Tech Stack yang Digunakan

```
Framework     : Laravel 12
Frontend      : Livewire v3 · Tailwind CSS v3 · Alpine.js v3
Admin Panel   : Filament v4
Database      : MySQL 8+ (primary key UUID, bukan auto-increment)
Grafik        : ApexCharts atau Chart.js
Kalender      : FullCalendar
PDF           : barryvdh/laravel-dompdf
Excel         : Maatwebsite/Laravel-Excel
Barcode PHP   : picqer/php-barcode-generator
Dev Command   : composer run dev (bukan php artisan serve)
```

---

## 📋 Aturan Wajib AI Agent Sebelum Coding

> [!CAUTION]
> Pelanggaran aturan ini menyebabkan bug yang sulit di-trace. Baca dan patuhi.

| ❌ DILARANG | ✅ GANTI DENGAN |
|------------|----------------|
| `$table->id()` | `$table->uuid('id')->primary()` |
| `php artisan serve` | `composer run dev` |
| Vue.js / React | Livewire v3 |
| Logic di Controller | Action Classes di `app/Actions/` |
| `$table->json()` | Tetap `json()` untuk MySQL (bukan jsonb) |
| `wire:model` tanpa modifier | `wire:model.live` atau `wire:model.blur` |
| Filament v5 syntax | Gunakan Filament v4 docs |
| File > 250 baris | Pecah jadi Action Class / View Component |
| `timestamps()` dengan timezone | gunakan `timestamps()` biasa untuk MySQL |

---

## 🚀 Tahap-Tahap Implementasi

Setiap tahap harus **selesai dan diverifikasi** sebelum lanjut ke tahap berikutnya.
Status: `[ ]` = belum | `[/]` = sedang dikerjakan | `[x]` = selesai

---

Detail checklist dan panduan eksekusi untuk setiap tahap telah dipindahkan ke folder [`docs/progres-aplikasi-absensi`](../progres-aplikasi-absensi/).

Silakan klik tautan pada tabel **Status Progres Keseluruhan** di bawah ini untuk melihat detail masing-masing tahap.

---

## 📊 Status Progres Keseluruhan

| Tahap | Nama | Status |
|-------|------|--------|
| 0 | [Inisiasi Proyek & Setup](../progres-aplikasi-absensi/tahap-0.md) | ✅ Selesai |
| 1 | [Skema Database](../progres-aplikasi-absensi/tahap-1.md) | ✅ Selesai |
| 2 | [Multi-Guard Authentication](../progres-aplikasi-absensi/tahap-2.md) | ✅ Selesai |
| 3 | [Data Master & Modul Admin](../progres-aplikasi-absensi/tahap-3.md) | ✅ Selesai |
| 4 | [Kios Scanner Absensi](../progres-aplikasi-absensi/tahap-4.md) | ⬜ Belum dimulai |
| 5 | [Dashboard Publik](../progres-aplikasi-absensi/tahap-5.md) | ⬜ Belum dimulai |
| 6 | [Portal Wali Kelas](../progres-aplikasi-absensi/tahap-6.md) | ⬜ Belum dimulai |
| 7 | [Portal Siswa](../progres-aplikasi-absensi/tahap-7.md) | ⬜ Belum dimulai |
| 8 | [Import/Export Excel & Kartu OSIS](../progres-aplikasi-absensi/tahap-8.md) | ⬜ Belum dimulai |
| 9 | [Kalender Hari Libur](../progres-aplikasi-absensi/tahap-9.md) | ⬜ Belum dimulai |
| 10 | [Laporan & Dashboard Admin](../progres-aplikasi-absensi/tahap-10.md) | ⬜ Belum dimulai |
| 11 | [Multi-Tahun Ajaran & Kenaikan Kelas](../progres-aplikasi-absensi/tahap-11.md) | ⬜ Belum dimulai |
| 12 | [Auto-Mark Alpa (Scheduler)](../progres-aplikasi-absensi/tahap-12.md) | ⬜ Belum dimulai |

---

## 🔁 Instruksi untuk AI Agent Saat Memulai Sesi Baru

1. **Baca file ini (`12-progress-projek.md`) dari awal**.
2. **Cek tabel Status Progres** — lihat tahap mana yang terakhir selesai.
3. **Baca dokumen stack** yang relevan untuk tahap yang akan dikerjakan (lihat tabel di atas).
4. **Kerjakan hanya 1 tahap** dalam satu sesi — selesaikan semua checklist-nya.
5. **Update status** di tabel "Status Progres" setelah tahap selesai (ganti ⬜ jadi ✅).
6. **Laporkan ke pengguna** apa saja yang sudah dikerjakan dan minta konfirmasi sebelum lanjut.

---

*Dokumen ini dibuat 1 Juli 2026. Update status setiap kali satu tahap selesai dikerjakan.*
