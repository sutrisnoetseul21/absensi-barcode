# 02. Roles & Permissions

**Role yang digunakan (4 jenis akun + 1 publik):**

| Role | Deskripsi | Portal/Login | Tabel/Guard |
|---|---|---|---|
| Super Admin | Akses penuh: setting sekolah, tahun ajaran, kenaikan kelas, arsip, kelola admin lain | Login Admin (Filament) | `users` / guard `web` |
| Admin/Operator | Input absensi (scan), kelola siswa & kalender libur, import/export data. Tidak bisa setting tahun ajaran & kenaikan kelas | Login Admin (Filament) | `users` / guard `web` |
| Wali Kelas | Lihat data kelas yang diampu (bisa > 1 kelas), input manual absensi (Izin/Sakit/Alpa) | Login Wali Kelas (custom) | `teachers` / guard `wali_kelas` |
| Siswa | Hanya lihat riwayat absensinya sendiri, read-only | Login Siswa (custom) | `students` / guard `siswa` |
| Publik (guest) | Lihat dashboard publik agregat, tanpa login | — | — |

> **Catatan:** Admin dan Super Admin sama-sama menggunakan tabel `users` bawaan Filament (guard `web`). Pembedaan hak akses Super Admin vs Admin dilakukan via kolom boolean `is_super_admin` dan pembatasan `canAccess()` di level resource/halaman (bukan menggunakan Filament Shield atau tabel terpisah).

---

**Kenapa 3 form login terpisah (bukan 1 form untuk semua role)?**
- Admin butuh panel CRUD lengkap → pakai Filament apa adanya.
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
