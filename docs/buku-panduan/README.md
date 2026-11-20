# 📖 Rencana & Daftar Isi Buku Panduan Pengguna

Folder ini berisi **Buku Panduan Operasional Pengguna** (*User Guide*) untuk aplikasi **Smart School ERP & Presensi Barcode**. Selain format Markdown per bab, tersedia juga versi web HTML interaktif dengan pengalih tampilan **Mode Web (Desktop)** dan **Mode HP (Mobile)**.

- 🌐 **Buka Versi Web HTML Interaktif**: [`index.html`](index.html)
- 🖼️ **Folder Aset Screenshot**: [`assets/`](assets/) *(dikelompokkan per bab: `bab-00-pendahuluan/`, `bab-01-portal-setup/`, dst.)*

> **Alur Pembelajaran Disarankan**: Ikuti berurutan mulai dari **Bab 1 (`/portal-setup`)** karena portal operasional lain terkunci hingga identitas sekolah, tahun ajaran aktif, kelas, jabatan, dan mata pelajaran selesai disiapkan.

---

## 🗺️ Rencana Susunan Bab Buku Panduan

| Bab | File Panduan | Folder Aset Foto | URL Portal / Fokus | Target Pembaca | Status |
| :---: | :--- | :--- | :--- | :--- | :---: |
| **00** | [`00-pendahuluan-login-dan-pilih-portal.md`](00-pendahuluan-login-dan-pilih-portal.md) | `assets/bab-00-pendahuluan/` | `/login`, `/pilih-portal` | Semua Pengguna | ✅ Ditulis |
| **01** | [`01-portal-setup.md`](01-portal-setup.md) | `assets/bab-01-portal-setup/` | `/portal-setup` *(Titik Mulai)* | Super Admin, Operator Awal | ✅ Ditulis |
| **02** | `02-portal-sistem.md` | `assets/bab-02-portal-sistem/` | `/portal-sistem` | Super Admin, Admin Sistem/IT | 📝 Rencana |
| **03** | `03-portal-akademik.md` | `assets/bab-03-portal-akademik/` | `/portal-akademik` | Kurikulum, Tata Usaha, Kepsek | 📝 Rencana |
| **04** | `04-portal-presensi.md` | `assets/bab-04-portal-presensi/` | `/portal-presensi` & Kiosk | Petugas Presensi / Piket | 📝 Rencana |
| **05** | `05-portal-guru.md` | `assets/bab-05-portal-guru/` | `/portal-guru` | Guru Mapel, Wali Kelas, Guru Wali, Pembina, BK | 📝 Rencana |
| **06** | `06-portal-siswa.md` | `assets/bab-06-portal-siswa/` | `/portal-siswa` | Siswa & Orang Tua | 📝 Rencana |
| **07** | `07-portal-perpustakaan.md` | `assets/bab-07-portal-perpustakaan/` | `/portal-perpustakaan` | Pustakawan / Petugas Perpus | 📝 Rencana |
| **08** | `08-portal-web-dan-publik.md` | `assets/bab-08-portal-web-dan-publik/` | `/portal-web` & `/` | Admin Humas / Website, PPID | 📝 Rencana |
| **09** | `09-lampiran-checklist-dan-faq.md` | `assets/bab-09-lampiran/` | Operasional & Kendala Umum | Semua Admin & Operator | 📝 Rencana |

---

## 📋 Daftar File Screenshot per Folder (Bab 00 & Bab 01)

Setiap langkah menyediakan pasangan screenshot **`web-*.png`** (Desktop `1440×900`) dan **`hp-*.png`** (Mobile `390×844`):

- `docs/buku-panduan/assets/bab-00-pendahuluan/`
  - `web-01-login.png` & `hp-01-login.png` — Halaman Login (`/login`)
  - `web-02-pilih-portal.png` & `hp-02-pilih-portal.png` — Hub Pilih Portal (`/pilih-portal`)
- `docs/buku-panduan/assets/bab-01-portal-setup/`
  - `web-01-dashboard.png` & `hp-01-dashboard.png` — Dashboard Checklist Setup (`/portal-setup`)
  - `web-02-setup-dasar-sekolah.png` & `hp-02-setup-dasar-sekolah.png` — Tab 1 Identitas & Jenjang (`/portal-setup/dasar`)
  - `web-03-setup-dasar-tahun-ajaran.png` & `hp-03-setup-dasar-tahun-ajaran.png` — Tab 2 Tahun Ajaran (`/portal-setup/dasar?tab=tahun-ajaran`)
  - `web-04-setup-dasar-kelas.png` & `hp-04-setup-dasar-kelas.png` — Tab 3 Master Kelas (`/portal-setup/dasar?tab=kelas`)
  - `web-05-master-jabatan.png` & `hp-05-master-jabatan.png` — Master Jabatan (`/portal-setup/jabatan`)
  - `web-06-master-mapel.png` & `hp-06-master-mapel.png` — Master Mata Pelajaran (`/portal-setup/mapel`)

---

## 📋 Rincian Rencana Materi Setiap Bab

### Bab 00 — Pendahuluan, Login & Hub Pilih Portal *(Selesai)*
- Gambaran portal sesuai hak akses, cara login (`/login`), layar `/pilih-portal`, status terkunci sebelum setup.

### Bab 01 — Portal Setup & Instalasi Awal (`/portal-setup`) *(Selesai)*
- Checklist kesiapan 5 langkah, Inisialisasi Core Seeder, Setup Dasar 3 Tab, Master Jabatan, Master Mata Pelajaran, dan keamanan Gembok Edit Super Admin.

### Bab 02 — Portal Sistem (`/portal-sistem`)
- Manajemen akun admin/staf, reset password, hak akses portal, koneksi & notifikasi WA modular, layanan publik, tracer alumni, SPIKAP, maintenance, dan tema.

### Bab 03 — Portal Akademik (`/portal-akademik`)
- Data Guru (bidang mapel & *Kelas Mengajar*), Data Siswa (import & cetak biodata), Kelas & Pembelajaran (*Plotting dari Guru*), Enrollment/Kenaikan/Kelulusan/Mutasi, Supervisi Jurnal Mengajar, SI-WALI, dan Ekstrakurikuler.

### Bab 04 — Portal Presensi & Kiosk (`/portal-presensi`)
- Cetak kartu barcode, kiosk scanner gerbang, input manual, ijin, sholat dhuhur/jumat, ekstrakurikuler, jam kerja, hari libur, dan laporan.

### Bab 05 — Portal Guru (`/portal-guru`)
- Dashboard & pop-up menu, presensi kelas, verifikasi ijin, Jurnal Mengajar KBM, SI-WALI, Sholat Dhuhur, Ekstrakurikuler, BK, dan SPIKAP.

### Bab 06 — Portal Siswa (`/portal-siswa`)
- Kartu digital, pantau presensi & jurnal, pengajuan ijin, konsultasi Guru Wali, aduan SPIKAP, perpustakaan, dan Tracer Study Alumni.

### Bab 07 — Portal Perpustakaan (`/portal-perpustakaan`)
- Katalog buku & DDC, eksemplar barcode, import SLiMS, kiosk pengunjung & sirkulasi mandiri, peminjaman paket kelas, dan denda.

### Bab 08 — Portal Web & Website Sekolah (`/portal-web` & `/`)
- Publikasi berita/artikel/pengumuman/prestasi/galeri, setup halaman beranda, pengaduan masyarakat (`/pengaduan`), halaman layanan, FAQ, dan direktori alumni (`/alumni`).

### Bab 09 — Lampiran Checklist Siap Operasional & FAQ
- Checklist urutan kerja peluncuran sistem dan panduan troubleshooting kendala harian.
