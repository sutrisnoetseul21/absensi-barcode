# TAHAP 6 — Portal Wali Kelas

> **Goal**: Wali kelas bisa login, lihat rekap kelasnya, input absensi manual, dan lihat alert siswa bermasalah.
> Referensi: `02-roles-permissions.md`, `04-user-flow.md`.

- `[ ]` Buat Livewire component `WaliKelasLogin` (di `/wali-kelas/login`)
- `[ ]` Buat Livewire component `WaliKelasDashboard` (di `/wali-kelas`)
  - Auto-load kelas yang diampu berdasarkan `class_academic_year.teacher_id`
  - Rekap hari ini: tabel daftar siswa + status hadir/tidak
  - Filter rekap per bulan
  - **Alert Pelanggaran**: Label merah untuk siswa >= 3x Alpa atau late_minutes >= 100 menit sebulan
- `[ ]` Tangani edge case: wali kelas login tapi belum ter-assign ke kelas (tampilkan empty state, bukan error)
- `[ ]` Buat Livewire component `ManualAttendanceInput` (modal di dalam dashboard)
  - Pencarian siswa: bisa ketik **Nama** atau **NISN** (live search)
  - Pilih status: Sakit / Izin / Alpa
  - Simpan dengan flag `is_manual_input = true`, `manual_input_by = teacher_id`, `manual_input_role = 'wali_kelas'`
- `[ ]` Wali kelas TIDAK BISA edit data scan "Hadir" yang sudah masuk

**Verifikasi Tahap 6 Selesai:**
- [ ] Login wali kelas berhasil dan redirect ke dashboard kelasnya
- [ ] Wali kelas tidak bisa akses kelas lain
- [ ] Input manual absensi tersimpan dengan flag benar
- [ ] Alert pelanggaran muncul untuk siswa yang memenuhi kriteria
- [ ] Pencarian siswa bisa pakai nama dan NISN

**Status: Belum dimulai ⬜**
