# TAHAP 11 — Fitur Multi-Tahun Ajaran & Kenaikan Kelas

> **Goal**: Admin bisa arsip tahun ajaran lama dan proses kenaikan kelas massal.
> Referensi: `05-database.md` (catatan kenaikan kelas), `11-roadmap.md` Fase 3.

- `[ ]` **Fitur Arsip**: Tombol "Arsipkan Tahun Ajaran" → ubah status `academic_years` ke 'arsip'
- `[ ]` **Wizard Kenaikan Kelas** (multi-step):
  1. Pilih Tahun Ajaran baru (buat atau pilih yang sudah ada)
  2. Mapping massal: siswa aktif → kelas baru (otomatis naik 1 level)
  3. Review manual: tandai siswa yang tinggal kelas / pindah / lulus
  4. Konfirmasi → buat baris baru di `student_enrollments` untuk tahun baru
- `[ ]` Data absensi dan laporan tahun lama tetap read-only dan dapat dilihat
- `[ ]` Log proses kenaikan kelas tersimpan di `promotion_logs`

**Verifikasi Tahap 11 Selesai:**
- [ ] Proses kenaikan kelas menghasilkan enrollment baru untuk tahun baru
- [ ] Data absensi tahun lama tetap dapat diakses
- [ ] Dashboard bisa difilter per tahun ajaran (aktif maupun arsip)

**Status: Belum dimulai ⬜**
