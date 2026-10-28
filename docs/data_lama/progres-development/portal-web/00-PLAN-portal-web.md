# PLAN: Portal Web Sekolah (`/portal-web`)

> Dibuat: 2026-09-04  
> Status: **APPROVED - SIAP EKSEKUSI**  
> Konvensi: Mengikuti pola portal yang sudah ada (portal-presensi, portal-guru, dll.)

---

## 1. Tujuan

Membuat portal khusus pengelola **konten web publik sekolah** (`/portal-web`). Portal ini menjadi *command center* untuk admin web tanpa perlu masuk ke Filament admin. Mencakup:

- Dashboard statistik konten web (artikel, galeri, prestasi, alumni)
- CRUD penuh: Berita, Pengumuman, Prestasi, Galeri, Data Alumni, Pelayanan Publik
- Pengaturan web profil sekolah (hero, visi, misi, sosmed, dsb.)
- Terintegrasi ke sistem manajemen akses portal yang sudah ada

---

## 2. Keputusan Arsitektur

| Keputusan | Pilihan |
|---|---|
| Auth / Login | Guard `web` yang sudah ada. Role baru: `admin_portal_web`. Akses via toggle di Manajemen Akses Portal Filament |
| CRUD Level | **CRUD Penuh** (tambah/edit/hapus langsung di portal, tidak redirect ke Filament) |
| Layout | `components.layouts.portal` (sidebar kiri + konten kanan) — sama dengan portal-presensi |
| Engine | **Livewire** (sesuai pola proyek, reaktif) |
| Zero CDN | Semua aset via Vite/NPM lokal |

---

## 3. Route Structure

```
GET  /portal-web              → portal-web.dashboard   (PortalWeb\Dashboard)
GET  /portal-web/login        → portal-web.login       (PortalWeb\Login)
POST /portal-web/logout       → portal-web.logout
GET  /portal-web/artikel      → portal-web.artikel     (PortalWeb\Artikel) [Berita & Pengumuman]
GET  /portal-web/prestasi     → portal-web.prestasi    (PortalWeb\Prestasi)
GET  /portal-web/galeri       → portal-web.galeri      (PortalWeb\Galeri)
GET  /portal-web/alumni       → portal-web.alumni      (PortalWeb\Alumni)
GET  /portal-web/pelayanan    → portal-web.pelayanan   (PortalWeb\Pelayanan) [Layanan Publik]
GET  /portal-web/pengaturan   → portal-web.pengaturan  (PortalWeb\Pengaturan) [WebSetting]
```

Middleware: `['auth', 'role:super_admin|admin_portal_web']`

---

## 4. Sidebar Menu Portal Web

- Dashboard Web
- Artikel & Pengumuman
- Prestasi Sekolah
- Galeri Foto
- Data Alumni
- Pelayanan Publik
- Pengaturan Web

---

## 5. File yang Akan Dibuat / Dimodifikasi

### A. Routes
- [MODIFY] `routes/web.php` — tambah grup `portal-web.*`

### B. Livewire Components (`app/Livewire/PortalWeb/`)
- [NEW] `Dashboard.php`
- [NEW] `Login.php`
- [NEW] `Artikel.php` (CRUD berita + pengumuman)
- [NEW] `Prestasi.php` (CRUD prestasi)
- [NEW] `Galeri.php` (CRUD galeri foto)
- [NEW] `Alumni.php` (CRUD data alumni)
- [NEW] `Pelayanan.php` (CRUD layanan publik)
- [NEW] `Pengaturan.php` (form WebSetting)

### C. Views (`resources/views/livewire/portal-web/`)
- [NEW] `dashboard.blade.php`
- [NEW] `login.blade.php`
- [NEW] `artikel.blade.php`
- [NEW] `prestasi.blade.php`
- [NEW] `galeri.blade.php`
- [NEW] `alumni.blade.php`
- [NEW] `pelayanan.blade.php`
- [NEW] `pengaturan.blade.php`

### D. Auth / Access
- [MODIFY] `app/Filament/Resources/ManajemenAksesPortalResource.php`
  — tambah Section "Akses Portal Web (/portal-web)" dengan toggle `akses_portal_web`
- [MODIFY] `app/Filament/Resources/ManajemenAksesPortalResource/Pages/EditManajemenAksesPortal.php`
  — logika mutateFormDataBeforeFill + afterSave untuk role `admin_portal_web`
- [MODIFY] `app/Models/User.php`
  — tambah portal-web ke `getAccessiblePortals()`

### E. Layout & ERP Portal
- [MODIFY] `resources/views/components/layouts/portal.blade.php`
  — deteksi route `portal-web*`, sidebar menu portal-web
- [MODIFY] `resources/views/erp-portal.blade.php`
  — tambah kartu "Portal Web Sekolah"

### F. Role
- Role baru: `admin_portal_web` (dibuat via `Role::firstOrCreate` di `afterSave`)
- Middleware route: `role:super_admin|admin_portal_web`

---

## 6. Data Sources per Halaman

| Halaman | Model | Keterangan |
|---|---|---|
| Dashboard | `WebArtikel`, `WebGaleri`, `WebAlumni`, `Presensi` | Statistik + presensi hari ini |
| Artikel | `WebArtikel` (tipe: berita, pengumuman) | CRUD full |
| Prestasi | `WebArtikel` (tipe: prestasi) | CRUD full |
| Galeri | `WebGaleri` | CRUD + upload foto |
| Alumni | `WebAlumni` | CRUD + filter angkatan/tahun |
| Pelayanan | `WebSetting` (link_pengaduan) atau model baru | TBD |
| Pengaturan | `WebSetting::instance()` | Form hero, visi, misi, sosmed |

---

## 7. Status Progres

| Tahap | Status |
|---|---|
| Research & Plan | DONE |
| Routes | TODO |
| Livewire: Dashboard | TODO |
| Livewire: Login | TODO |
| Livewire: Artikel CRUD | TODO |
| Livewire: Prestasi CRUD | TODO |
| Livewire: Galeri CRUD | TODO |
| Livewire: Alumni CRUD | TODO |
| Livewire: Pelayanan CRUD | TODO |
| Livewire: Pengaturan | TODO |
| ManajemenAksesPortal (toggle) | TODO |
| User model (getAccessiblePortals) | TODO |
| Layout portal sidebar | TODO |
| ERP Portal card | TODO |
| npm run build | TODO |
| Verifikasi akhir | TODO |

---

## 8. Catatan Penting untuk AI Agent

- **Alumni**: Cek apakah model `WebAlumni` sudah ada di `app/Models/`. Jika ada filter per tahun/angkatan cek juga migrasinya.
- **Pelayanan Publik**: Kemungkinan perlu model/tabel baru `web_pelayanans` jika lebih dari sekedar link. Alternatif: pakai field di `WebSetting`.
- **Role**: `admin_portal_web` dibuat otomatis via `Role::firstOrCreate` saat toggle disimpan pertama kali — tidak perlu seeder/migration terpisah.
- **php artisan optimize:clear** WAJIB dijalankan setelah setiap perubahan struktural (route, view, model).
- **Pattern EditManajemenAksesPortal**: Ikuti pola yang sudah ada — `mutateFormDataBeforeFill` (baca role → isi form), `afterSave` (form → assign/remove role).
- **Pattern getAccessiblePortals**: Tambah entri portal-web dengan cek `hasRole('admin_portal_web')` atau `isSuper`.

---

## 9. Klarifikasi Model (Update 2026-09-04)

Setelah investigasi lebih lanjut:

- **Alumni**: Model sudah ada di `app/Models/Alumni.php` dengan field: `nisn`, `nama`, `jenis_kelamin`, `tahun_lulus`, `melanjutkan`, `jenjang_id`, `nama_sekolah`, `no_hp`, `foto`. Relasi ke `AlumniJenjang`.
- **Pelayanan Publik**: Menggunakan model `WebQuickLink` yang sudah ada. Field: `title`, `description`, `url`, `icon`, `color_class`, `is_active`, `order`. Ini yang tampil di navbar web publik sebagai "Layanan Publik".
- **Galeri**: `WebGaleri` (sudah ada)
- **Artikel**: `WebArtikel` — tipe: `berita`, `pengumuman`, `prestasi` (sudah ada)
- **Tidak perlu migration/model baru**
