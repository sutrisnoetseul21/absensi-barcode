# 🔐 Model Role & Hak Akses Pengguna (RBAC)

Dokumen ini menjelaskan struktur Role-Based Access Control (RBAC) menggunakan Spatie Permission dan integrasinya dengan Filament Shield.

---

## 1. Daftar Role Inti Sistem

Sistem membedakan role ke dalam beberapa klaster modul:

### A. Klaster Administratif Utama
- `super_admin`: Memiliki hak penuh atas seluruh modul, pengaturan sekolah, tahun ajaran aktif, dan manajemen database.
- `admin`: Administrator operasional sekolah.

### B. Klaster Portal Guru & Siswa
- `wali_kelas`: Memiliki hak akses terhadap siswa di rombongan belajar yang dibinanya (rekap presensi kelas, input izin, validasi kehadiran sholat, pengisian jurnal bimbingan).
- `guru`: Guru pengajar yang memiliki akses ke modul umum pembelajaran dan dapat diberikan izin `portal_guru:akses_semua_kelas`.
- `siswa`: Siswa aktif (akses ke portal siswa & kartu barcode digital).

### C. Klaster Fungsional Khusus
- `admin_presensi_admin` / `editor` / `view`: Manajemen data presensi, pengaturan jam batas toleransi, dan notifikasi WhatsApp.
- `admin_perpustakaan_admin` / `editor` / `view`: Manajemen sirkulasi buku, katalog, dan kartu anggota perpustakaan.
- `admin_akademik_admin` / `editor` / `view`: Pengelolaan mata pelajaran, KKM, dan data asesmen CBT.
- `spikap_admin`, `spikap_guru_bk`, `spikap_wali_kelas`, `spikap_kepala_sekolah`: Pengelolaan tiket aduan, tindak lanjut, dan sesi konseling BK.

---

## 2. Model User & Multi-Akses

Seorang user dapat memiliki relasi profil guru atau profil siswa:
- **Tabel `users`**: Menampung kredensial login (email, username, password, status `is_super_admin`).
- **Tabel `teachers`**: Data identitas guru, NIP, status wali kelas, dan nomor WhatsApp.
- **Tabel `students`**: Data identitas siswa, NIS, NISN, kelas, nomor barcode, dan kontak orang tua.

Satu akun guru dapat memiliki beberapa role sekaligus (contoh: seorang guru dapat memegang role `guru`, `wali_kelas`, dan `spikap_wali_kelas`).
