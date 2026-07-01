# 06. Business Rules

## Jam Masuk & Kalkulasi Keterlambatan
- Jam batas "Hadir" vs "Telat" diambil dari **`school_settings.checkin_time`** (setting global, sama untuk seluruh sekolah/kelas).
- `late_minutes` = menit antara `scan_time` dengan `checkin_time`. Jika scan sebelum atau tepat waktu → `late_minutes = 0`, status `hadir`. Jika scan melewati batas → status `telat`.
- Akumulasi `late_minutes` bulanan per siswa ditampilkan di portal Wali Kelas dan portal Siswa.

## Papan Peringkat / Gamifikasi (Wall of Fame)
- Sistem menghitung agregat % kehadiran per kelas (jumlah `hadir` + `telat` dibagi total hari efektif siswa di kelas itu).
- 5 kelas dengan % tertinggi tampil di Wall of Fame dashboard publik.
- Hari libur dikecualikan dari pembagi (total hari efektif).

## Batas Pelanggaran & Alert Wali Kelas
- Jika siswa mencapai `>= 3x Alpa` dalam sebulan, **atau** akumulasi `late_minutes` bulan ini `>= 100 menit` → muncul label/peringatan merah di Dashboard Wali Kelas.
- Nilai threshold (3x alpa, 100 menit) sebaiknya dapat dikonfigurasi via `school_settings`.
- Sistem tidak mencetak Surat Peringatan otomatis — hanya memberi notifikasi visual agar Wali Kelas bisa melakukan pembinaan.

## Penanganan Scan Simultan & Debounce
- **Per-Barcode Cooldown:** Barcode yang sama jika discan berulang dalam **3 detik** hanya dihitung 1 kali (debounce per `barcode_code`, bukan global).
- **Concurrent Async Scan:** Front-end dibangun asynchronous — layar langsung siap untuk scan berikutnya tanpa menunggu respons server. Mendukung skenario rush hour (ratusan siswa berurutan).
- Sistem harus bisa menangani 10 scanner beroperasi bersamaan tanpa blocking/deadlock di database.

## Validasi Barcode Tidak Dikenal
- Jika `barcode_code` tidak ditemukan di `students` → tolak, tampilkan peringatan "Barcode tidak terdaftar", simpan ke `invalid_scan_logs`.

## Logika Hari Libur
- Tabel `holidays` mendukung **range tanggal** (`start_date` s/d `end_date`).
- Query cek libur: `WHERE start_date <= :tanggal AND (end_date IS NULL OR end_date >= :tanggal)`.
- Libur dengan `class_id IS NULL` = libur untuk seluruh sekolah. Libur dengan `class_id` tertentu = hanya berlaku kelas itu.
- Saat siswa scan di hari libur → sistem menolak/memberi info, tidak menyimpan `attendance`.
- Hari libur dikecualikan dari total hari efektif untuk kalkulasi % kehadiran.

## Input Absensi Manual (Wali Kelas / Admin)
- Status yang bisa diinput manual: `sakit`, `izin`, `alpa`.
- Wali kelas **tidak bisa** mengubah record `hadir`/`telat` yang sudah masuk dari scan.
- Setiap record manual wajib menyimpan: `is_manual_input = true`, kolom polymorphic `manual_input_by_id` + `manual_input_by_type` (bisa Teacher atau User/Admin), dan kolom `note` (alasan singkat).
- **Morph Map** di `AppServiceProvider`:
  ```php
  Relation::morphMap([
      'admin'      => \App\Models\User::class,
      'wali_kelas' => \App\Models\Teacher::class,
  ]);
  ```

## Kenaikan Kelas
- Proses manual dengan review — tidak otomatis naik semua siswa.
- Alur via Excel:
  1. Export daftar siswa aktif (NISN, Nama, Kelas Saat Ini, kolom "Kelas Baru" kosong).
  2. Admin/operator isi kolom "Kelas Baru" (nama kelas dari `classes.name`, misal "8A").
  3. Import kembali → sistem validasi nama kelas → buat `student_enrollments` baru.
  4. Siswa kolom kosong atau nama kelas tidak valid → tampil di daftar error.
- Setiap proses kenaikan dicatat di `promotion_logs` + `promotion_log_details` (audit per siswa).
- Data absensi dan laporan tahun lama tetap **read-only** setelah tahun ajaran diarsipkan.

## Soft Delete
- `students`, `teachers`, `classes` menggunakan `softDeletes()`.
- Siswa yang keluar / guru yang pindah = soft delete, data absensi historis tetap utuh.
- Dropdown & tabel aktif hanya menampilkan record yang `deleted_at IS NULL`.

## Validasi Kartu Barcode
- Admin bisa menonaktifkan `barcode_active = false` untuk kartu lama dan membuat kode baru.
- Riwayat absensi tetap aman karena terikat ke `student_id`, bukan `barcode_code`.

## Import Excel Siswa
- Patokan keunikan = **NISN**: jika NISN sudah ada → UPDATE; jika baru → INSERT.
- Laporan hasil import: X berhasil, Y gagal (keterangan error per baris).

## Login & Akses
- Sesi absensi (kios) sebaiknya ada re-auth berkala atau PIN kios tersendiri.
- Akses Wali Kelas = hanya kelas yang diampu (bisa > 1 kelas, cek via `class_academic_year.teacher_id`).
- Akses Siswa = hanya data pribadinya sendiri (read-only).
