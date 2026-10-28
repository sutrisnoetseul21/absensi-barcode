# TAHAP 7 — Portal Siswa

> **Goal**: Siswa bisa login dengan NISN dan lihat riwayat absensinya sendiri.

- `[ ]` Buat Livewire component `SiswaLogin` (di `/siswa/login`)
  - `[ ]` Implementasikan *rate limiting* pada proses *login* untuk keamanan
  - `[ ]` Cek status *enrollment* aktif sebelum izinkan login (lulus/pindah = tidak bisa login)
- `[ ]` Buat Livewire component `SiswaDashboard` (di `/siswa`)
  - `[ ]` *Server-side scoping* via `Auth::guard('siswa')->user()`, JANGAN pernah percaya ID yang dikirim dari *client* (URL/Payload)
  - `[ ]` Tampilkan: Nama, NISN, Kelas, Tahun Ajaran aktif
  - `[ ]` Riwayat presensi *scope* ke tahun ajaran aktif + filter bulan
  - `[ ]` Ringkasan (Total H,T,I,S,A) & akumulasi menit telat ikut bulan yang difilter
- `[ ]` Siswa murni bersifat read-only (TIDAK BISA mutasi data apapun)

**Verifikasi Tahap 7 Selesai:**
- [ ] Login siswa via NISN berhasil dengan proteksi rate limit
- [ ] Siswa dengan status enrollment tidak aktif otomatis ditolak login
- [ ] Siswa HANYA melihat data absensinya sendiri (tidak bocor)
- [ ] Filter bulan bekerja dengan benar memengaruhi data tabel & ringkasan

**Status: Belum dimulai ⬜**
