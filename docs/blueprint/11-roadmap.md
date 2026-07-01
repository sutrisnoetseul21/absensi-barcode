# 11. Roadmap

Pembagian fase agar cepat jalan dan bisa dipakai bertahap:

---

**Fase 1 — MVP (inti absensi jalan dulu)**
- [x] Setup Laravel 12 + Livewire v3 + Filament v4 + MySQL
- [ ] **Seeder kelas** — isi template nama kelas 7A–9C (atau sesuai jenjang) otomatis saat install
- [ ] **Pengaturan Sekolah** (`school_settings`) — nama, logo, kepala sekolah, jam masuk global *(dibutuhkan sejak awal untuk PDF & kartu)*
- [ ] Login Admin/Super Admin (Filament), login Wali Kelas & Siswa (multi-guard custom)
- [ ] Manajemen master data: Tahun Ajaran, Kelas (template), Siswa, Guru
- [ ] Assign wali kelas ke kelas per tahun ajaran
- [ ] Generate barcode kartu + cetak Kartu OSIS (PDF)
- [ ] Halaman scan absensi (kios mode, async, audio feedback)
- [ ] Dashboard publik dasar (% per kelas, Wall of Fame)

---

**Fase 2 — Kelengkapan Operasional**
- [ ] Kalender hari libur (range tanggal, cek saat scan)
- [ ] Auto-mark alpa (scheduler harian setelah jam batas)
- [ ] Laporan & export PDF/Excel presensi per kelas/siswa/bulan
- [ ] Dashboard Admin lebih detail (grafik tren, alert siswa bermasalah)
- [ ] Portal Wali Kelas (rekap kelas, input manual absensi + note, alert pelanggaran)
- [ ] Portal Siswa (riwayat absensi, rincian menit telat)
- [ ] Import & Export siswa via Excel

---

**Fase 3 — Multi-Tahun Ajaran & Kenaikan Kelas**
- [ ] Manajemen tahun ajaran & arsip (read-only setelah arsip)
- [ ] **Wizard Kenaikan Kelas via Excel** (download template → isi kelas baru → upload → proses massal)
- [ ] Alternatif: wizard UI step-by-step di Filament (bulk action)
- [ ] Audit log detail per siswa (`promotion_log_details`)
- [ ] Riwayat siswa lintas tahun ajaran di profil siswa

---

**Fase 4 — Pengembangan Lanjut (Opsional)**
- [ ] Notifikasi WA ke orang tua saat siswa belum absen jam tertentu (Fonnte/WA Gateway)
- [ ] Self-service reset password untuk Wali Kelas & Siswa
- [ ] Statistik lanjutan (per-siswa lintas tahun, heatmap kehadiran)
- [ ] Integrasi RFID selain barcode
