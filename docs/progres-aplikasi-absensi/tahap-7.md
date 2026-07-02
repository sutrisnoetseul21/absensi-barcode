# TAHAP 7 — Portal Siswa

> **Goal**: Siswa bisa login dengan NISN dan lihat riwayat absensinya sendiri.

- `[ ]` Buat Livewire component `SiswaLogin` (di `/siswa/login`)
- `[ ]` Buat Livewire component `SiswaDashboard` (di `/siswa`)
  - Tampilkan: Nama, NISN, Kelas, Tahun Ajaran aktif
  - Tabel riwayat absensi semua bulan (filter per bulan)
  - Ringkasan: total hadir, telat, sakit, izin, alpa
  - Akumulasi total menit keterlambatan bulan ini
- `[ ]` Siswa TIDAK BISA melakukan perubahan data apapun (pure read-only)
- `[ ]` Ganti password default jika `must_change_password = true`

**Verifikasi Tahap 7 Selesai:**
- [ ] Login siswa via NISN berhasil
- [ ] Siswa hanya melihat data dirinya sendiri
- [ ] Filter bulan bekerja dengan benar
- [ ] Siswa dengan `must_change_password` diwajibkan ganti password saat login pertama

**Status: Belum dimulai ⬜**
