# TAHAP 6 — Portal Wali Kelas

> **Goal**: Wali kelas bisa login, lihat rekap kelasnya, input absensi manual, dan lihat alert siswa bermasalah.
> Referensi: `02-roles-permissions.md`, `04-user-flow.md`.

- `[x]` Install package `spatie/laravel-activitylog` untuk audit log input manual
- `[x]` Buat Livewire component `WaliKelasLogin` (di `/wali-kelas/login`)
- `[x]` Buat Livewire component `WaliKelasDashboard` (di `/wali-kelas`) — dirancang REUSABLE untuk 2 mode akses (lihat Task 2 & 3)
  - Mode Wali Kelas: auto-load kelas yang diampu berdasarkan `class_academic_year.teacher_id` (guard `wali_kelas`)
  - Mode Admin: dropdown pilih kelas manapun, tanpa batasan assignment (guard `web`, Super Admin/Admin)
  - Rekap hari ini: tabel daftar siswa + status hadir/tidak
  - Filter rekap per bulan
  - **Alert Pelanggaran** (dihitung per bulan berjalan, reset tiap bulan baru — TIDAK akumulatif lintas bulan):
    - Label merah: siswa dengan >= 3x Alpa BULAN INI, ATAU total `late_minutes` >= 100 menit BULAN INI
- `[x]` **Validasi server-side wajib** (jangan percaya `class_id` dari client):
  - Guard `wali_kelas`: cek `class_id` yang diakses ADA di daftar kelas yang diampu teacher tsb (via `class_academic_year.teacher_id`). Kalau tidak, tolak akses (403 atau redirect, bukan expose data)
  - Guard `web` (Admin/Super Admin): boleh akses kelas manapun tanpa perlu validasi assignment
- `[x]` Tangani edge case: wali kelas login tapi belum ter-assign ke kelas apapun (tampilkan empty state, bukan error)
- `[x]` Buat Filament Custom Page (misal `RekapAbsensiKelas`) untuk Admin/Super Admin — reuse `WaliKelasDashboard` & `ManualAttendanceInput` yang sama, dengan dropdown pilih kelas di atasnya (tidak auto-restricted seperti mode Wali Kelas)
- `[x]` Buat Livewire component `ManualAttendanceInput` (modal di dalam dashboard, dipakai baik di Portal Wali Kelas maupun Filament Page Admin)
  - Pencarian siswa: bisa ketik **Nama** atau **NISN** (live search)
  - **Pencarian WAJIB di-scope**: mode Wali Kelas hanya cari siswa di kelas yang diampu (bukan seluruh sekolah). Mode Admin boleh cari siswa kelas manapun.
  - Pilih status: Sakit / Izin / Alpa
  - **Validasi sebelum simpan**: cek dulu apakah siswa+tanggal itu SUDAH punya record dengan `is_manual_input = false` (dari scan asli, status Hadir/Telat). Kalau ada, TOLAK dengan pesan jelas ("Siswa ini sudah tercatat Hadir/Telat hari ini via scan, tidak bisa diubah manual") — jangan sampai record scan asli ketimpa.
  - Simpan dengan flag `is_manual_input = true`, `manual_input_by_id` + `manual_input_by_type` (polymorphic, bisa Teacher atau User/Admin)
  - Wali Kelas BOLEH edit input manual miliknya sendiri (bukan create-only)
  - **Setiap create DAN edit input manual WAJIB tercatat via `spatie/laravel-activitylog`**: siapa (actor), kapan, status sebelum → sesudah
- `[x]` Wali kelas TIDAK BISA edit data scan "Hadir"/"Telat" asli yang sudah masuk dari kios scan (`is_manual_input = false`) — baik lewat form baru maupun lewat fitur edit

**Verifikasi Tahap 6 Selesai:**
- [ ] Login wali kelas berhasil dan redirect ke dashboard kelasnya
- [ ] Wali kelas tidak bisa akses kelas lain (dicoba manipulasi class_id via URL/DevTools tetap ditolak server)
- [ ] Admin/Super Admin bisa akses & input manual absensi untuk kelas manapun lewat Filament Page
- [ ] Input manual absensi tersimpan dengan flag benar (`is_manual_input`, `manual_input_by_id/type`)
- [ ] Input manual DITOLAK kalau siswa sudah punya record Hadir/Telat asli hari itu
- [ ] Wali kelas bisa edit input manual miliknya sendiri, dan perubahan tercatat di activity log
- [ ] Alert pelanggaran (>=3x Alpa atau >=100 menit telat) dihitung per bulan berjalan, reset tiap bulan baru
- [ ] Pencarian siswa bisa pakai nama dan NISN, ter-scope sesuai mode akses (wali kelas vs admin)

**Status: Selesai Diimplementasikan ⏳ Menunggu Verifikasi Manual**
