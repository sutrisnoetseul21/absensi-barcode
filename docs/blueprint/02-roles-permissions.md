# 02. Roles & Permissions

**Role yang digunakan:**

| Role | Deskripsi | Portal/Login | Tabel/Guard |
|---|---|---|---|
| Super Admin | Akses penuh: pengaturan sistem, hak akses. | Portal Super Admin (`/admin`) | `users` / guard `web` |
| Admin TU (Master Data) | Input data induk: Tahun Ajaran, Kelas, Siswa & Guru. | Portal Master Data (`/admin-master`) | `users` / guard `web` |
| Admin TU (Akademik) | Pembagian kelas, mutasi, dan kenaikan kelas. | Portal Akademik (`/admin-akademik`) | `users` / guard `web` |
| Guru Piket / BK | Input manual presensi, kelola libur, rekapitulasi harian. | Portal Presensi (`/admin-presensi`) | `users` / guard `web` |
| Wali Kelas | Lihat data kelas yang diampu, input manual presensi (Izin/Sakit/Alpa) | Login Wali Kelas (custom) | `teachers` / guard `wali_kelas` |
| Siswa | Hanya lihat riwayat absensinya sendiri, read-only | Login Siswa (custom) | `students` / guard `siswa` |
| Publik (guest) | Lihat dashboard publik agregat, tanpa login | — | — |

> **Catatan:** Seluruh role Admin/TU menggunakan tabel `users` bawaan (guard `web`). Hak akses ke masing-masing panel dapat diatur lebih lanjut via pembatasan peran atau kebijakan akses (Policy). Pintu masuk login mereka disatukan melalui Gerbang Utama (`/login`).

---

**Kenapa form login dipisahkan?**
- Staf/TU butuh panel CRUD spesifik sesuai tanggung jawab mereka (Pemisahan Concern via Multi-Panel Filament). Pilihan portal tersedia di halaman `/login`.
- Wali Kelas & Siswa cukup butuh portal ringan, 1–2 halaman → lebih cepat dibuat custom Livewire daripada dipaksa masuk struktur resource Filament.
- Secara teknis ini didukung lewat **multi-guard authentication** di Laravel — tiap guard (`web`, `wali_kelas`, `siswa`) punya tabel user & session sendiri.

---

**Matrix hak akses** (fitur × role):

| Fitur | Super Admin | Admin | Wali Kelas | Siswa |
|---|---|---|---|---|
| Setting Sekolah (nama, logo, jam masuk) | ✅ | ❌ | ❌ | ❌ |
| Scan absensi (kios) | ✅ | ✅ | ❌ | ❌ |
| Kelola siswa, kelas, import/export | ✅ | ✅ | ❌ | ❌ |
| Kelola master nama kelas (template) | ✅ | ❌ | ❌ | ❌ |
| Setting & arsip tahun ajaran | ✅ | ❌ | ❌ | ❌ |
| Wizard kenaikan kelas + Excel | ✅ | ❌ | ❌ | ❌ |
| Setting hari libur | ✅ | ✅ | ❌ | ❌ |
| Assign wali kelas ke kelas (per tahun ajaran) | ✅ | ❌ | ❌ | ❌ |
| Lihat rekap kelas sendiri | ✅ | ✅ | ✅ (kelasnya saja) | ❌ |
| Input manual absensi (Sakit/Izin/Alpa + note) | ✅ | ✅ | ✅ (kelasnya saja) | ❌ |
| Lihat riwayat absensi pribadi | ✅ | ✅ | ❌ | ✅ (datanya saja) |
| Dashboard publik | ✅ | ✅ | ✅ | ✅ (tanpa login) |

---

**Mekanisme auth:**
- **Admin/Super Admin:** pakai auth bawaan Filament. Tabel `users`, guard `web`.
- **Wali Kelas & Siswa:** login pakai username/NISN + password, guard terpisah (`wali_kelas`, `siswa`). Wajib ganti password default saat login pertama (`must_change_password = true`).
- Reset password Wali Kelas & Siswa → dilakukan oleh Admin lewat panel Filament (self-service bisa jadi fase lanjut).
- Log aktivitas admin (audit trail) — penting untuk mencegah kecurangan.
