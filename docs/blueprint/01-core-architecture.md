# 01. Arsitektur Inti Sistem (ERP Sekolah)

## Latar Belakang & Visi Proyek
Sistem ini dimulai sebagai Aplikasi Presensi Barcode (Fase 1). Setelah sukses diluncurkan pada `v14.0`, visi proyek bergeser menjadi **Sistem ERP (Enterprise Resource Planning) Sekolah** (Fase 2). Sistem dirancang menggunakan arsitektur modular yang memisahkan **Master Data** (entitas murni seperti Siswa, Kelas, Guru) dari **Operasional** (transaksi seperti Pendaftaran, Presensi, Pengajaran).

Dengan pendekatan arsitektur *Loose Coupling* dan integrasi Filament Multi-Panel, sistem ini siap menampung modul masa depan seperti E-Learning (LMS) atau Sistem Pembayaran SPP tanpa merusak fitur yang sudah berjalan.

---

## Arsitektur 3-Layer

### 1. Layer Master Data (Fondasi / Referensi)
- Bertanggung jawab murni pada identitas inti dan data referensi abadi.
- Contoh: Data Identitas Siswa, Data Guru, Jabatan, Data Tahun Ajaran, Data Kelas, Mata Pelajaran.
- Data di sini *agnostik* (tidak terikat pada proses transaksional tertentu).

### 2. Layer Operasional (Transaksional & Pivot)
- Modul-modul bisnis yang menghubungkan/memetakan entitas dari Master Data menggunakan relasi pivot.
- Contoh Modul: 
  - **Pendaftaran (Enrollment):** Menghubungkan Siswa dengan Kelas.
  - **Pengajaran (Jadwal):** Menghubungkan Guru dengan Mata Pelajaran dan Kelas.
  - **Presensi:** Mencatat kehadiran harian.
- Setiap modul operasional merespon perubahan pada Master Data (contoh: Kenaikan Kelas otomatis memperbarui status).

### 3. Layer UI / Presentasi (Filament Multi-Panel)
Antarmuka *resource* dipisahkan secara jelas melalui arsitektur **Multi-Panel** untuk *Separation of Concern*:
- **Portal Super Admin (`/admin`)**: Manajemen Root, Pengaturan Sistem, & Audit.
- **Portal Master Data (`/admin-master`)**: Manajemen Tahun Ajaran, Kelas, Mata Pelajaran, Jabatan, Siswa & Guru.
- **Portal Akademik (`/admin-akademik`)**: Manajemen Distribusi (Enrollment Kelas, Plotting Jadwal/Pengajaran).
- **Portal Presensi (`/admin-presensi`)**: Input Presensi, Rekapitulasi & Libur.
- *(Future)* **Portal Wali Kelas (`/wali-kelas`)**: Portal khusus bagi Guru yang menjabat Wali Kelas.
- *(Future)* **Portal Siswa (`/siswa`)**: Portal bagi Siswa melihat rapot dan presensi.

---

## Konsep Multi-Guard Authentication

Sistem menggunakan banyak tabel otentikasi (Multi-Guard) untuk memisahkan domain user:

| Guard | Model | Tabel | Tujuan Akses |
|---|---|---|---|
| `web` (default Filament) | `User` | `users` | Admin, Super Admin, Staff TU. Memiliki kontrol terhadap Panel Admin (`/admin`, `/admin-master`, dll). |
| `wali_kelas` | `Teacher` | `teachers` | Guru dan Wali Kelas. Akan login melalui portal khusus (`/wali-kelas`). |
| `siswa` | `Student` | `students` | Portal Siswa. Login menggunakan NISN. |

> Pintu masuk *Login* disatukan melalui *Portal Selection* (Gerbang Utama) di `/login`.
