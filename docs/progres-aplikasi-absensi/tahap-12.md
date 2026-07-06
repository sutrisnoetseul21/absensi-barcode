# TAHAP 12 — Auto-Mark Alpa (Scheduler)

> **Goal**: Siswa yang tidak scan sampai jam tertentu otomatis dicatat Alpa.

- `[x]` Buat tombol `Tandai Alpa (Hari Ini)` di Dashboard Admin.
- `[x]` Tambahkan warning/konfirmasi dinamis dengan `KalenderSekolahService::isHariSekolah()`.
- `[x]` Logic: cari semua `student_enrollments` aktif yang belum punya `attendances` hari ini → insert dengan status 'alpa'.

**Verifikasi Tahap 12 Selesai:**
- [x] Tombol berfungsi dan memunculkan peringatan kuning jika di-klik pada hari libur/Minggu.
- [x] Siswa tanpa scan otomatis ter-mark Alpa ketika tombol di-klik (tidak double-insert jika di-klik 2x).

**Status: Selesai ✅**
