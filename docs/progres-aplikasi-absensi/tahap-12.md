# TAHAP 12 — Auto-Mark Alpa (Scheduler)

> **Goal**: Siswa yang tidak scan sampai jam tertentu otomatis dicatat Alpa.

- `[ ]` Buat Artisan Command `attendance:mark-absent`
- `[ ]` Daftarkan ke `app/Console/Kernel.php` (jadwal: setiap hari, misal jam 08.00)
- `[ ]` Logic: cari semua `student_enrollments` aktif yang belum punya `attendance` hari ini → insert dengan status 'alpa'
- `[ ]` Skip jika hari ini adalah `holiday`

**Verifikasi Tahap 12 Selesai:**
- [ ] Command berjalan manual tanpa error: `php artisan attendance:mark-absent`
- [ ] Siswa tanpa scan hari ini ter-mark Alpa setelah jam yang ditentukan

**Status: Belum dimulai ⬜**

