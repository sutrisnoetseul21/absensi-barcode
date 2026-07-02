# TAHAP 9 — Kalender Hari Libur

> **Goal**: Admin bisa menandai hari libur sehingga tidak ada alpa otomatis di hari tersebut.

- `[ ]` Install FullCalendar via npm atau CDN
- `[ ]` **Filament Resource**: `HolidayResource` (CRUD Hari Libur)
  - Tipe: Nasional / Cuti Bersama / Khusus Kelas
  - Jika "Khusus Kelas": pilih kelas mana yang libur
- `[ ]` Tampilan kalender interaktif (klik tanggal untuk tambah/lihat libur)
- `[ ]` Integrasi ke logika kios: cek `holidays` sebelum proses scan

**Verifikasi Tahap 9 Selesai:**
- [ ] Admin bisa tambah hari libur via kalender
- [ ] Saat siswa scan di hari libur → sistem menolak atau memberi info "hari ini libur"
- [ ] Hari libur dikecualikan dari perhitungan % kehadiran

**Status: Belum dimulai ⬜**
