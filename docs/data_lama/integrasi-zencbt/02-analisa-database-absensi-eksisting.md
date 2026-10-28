# 02. Analisis Database Absensi Saat Ini (Eksisting)

Dokumen ini membedah kondisi riil basis data pada project `projek-absensi-barcode` (MySQL), menginventarisir tabel yang sudah ada, serta mengidentifikasi bagian-bagian yang **masih kurang/belum lengkap** untuk mendukung modul Nilai dan integrasi CBT.

---

## 1. Inventaris Tabel Terkait Akademik yang Sudah Ada

Berdasarkan pengecekan berkas model dan migrasi di `projek-absensi-barcode`:

| Tabel MySQL | Model Terkait | Keterangan & Karakteristik |
| :--- | :--- | :--- |
| `academic_years` | `TahunAjaran` | Menggunakan UUID. Kolom: `id`, `name` (e.g. "2025/2026"), `start_year`, `end_year`, `status` (`aktif`/`arsip`). |
| `classes` | `Kelas` | Menggunakan UUID. Kolom: `id`, `name` (e.g. "7A", "8B"), `grade_level` (7, 8, 9, 10, dll). |
| `class_academic_year` | `KelasAjaran` | Pivot relasi kelas ke tahun ajaran & penunjukan wali kelas. |
| `students` | `Siswa` | Menggunakan UUID. Kolom: `nisn`, `nis`, `name`, `birth_place`, `birth_date`, `address`, `religion`, `gender`, `status`. Password ter-hash (`bcrypt`). |
| `student_enrollments` | `EnrollmentSiswa` | Menggunakan UUID. Menghubungkan `student_id`, `class_id`, `academic_year_id`, dengan status `aktif`/`naik`/`tinggal`/`lulus`. |
| `teachers` | `Guru` | Menggunakan UUID. Kolom: `user_id`, `name`, `nip`, `jenis_kelamin`, `no_hp`, `photo_path`. |
| `mata_pelajarans` | `MataPelajaran` | Menggunakan ID integer biasa. Kolom: `nama_mapel`, `kode_mapel`. |
| `school_settings` | `PengaturanSekolah` | Menyimpan `academic_year_id_active`, nama sekolah, kop surat, logo, dll. |

---

## 2. Poin-Poin yang Masih Kurang di Database Absensi

Berikut adalah kekurangan struktur database yang perlu kita lengkapi:

### 🔴 Kekurangan 1: Tidak Ada Data "Semester" yang Pasti
* **Kondisi Saat Ini**: Semester hanya dihitung secara logika tanggal di PHP (`(int)date('m') >= 7 ? 'ganjil' : 'genap'`) di beberapa controller cetak laporan.
* **Masalah**: Ujian sekolah dan nilai rapor terikat kuat dengan semester (**Ganjil** atau **Genap**). Jika hanya mengandalkan tanggal server, admin tidak bisa membuka/melihat kembali rekap nilai semester ganjil saat tahun ajaran sudah masuk bulan Januari (semester genap).
* **Solusi**: Tambahkan kolom `active_semester` di tabel `school_settings` (opsi: `'ganjil'`, `'genap'`).

---

### 🔴 Kekurangan 2: Belum Ada Tabel Master Ujian / Asesmen
* **Kondisi Saat Ini**: Belum ada tabel sama sekali untuk mencatat kegiatan ujian/asesmen.
* **Masalah**: Tidak ada tempat untuk mencatat nama ujian (misal: "STS Ganjil Matematika"), tanggal ujian, KKM, durasi, dan mata pelajaran yang diujikan.
* **Solusi**: Buat tabel `ujian_akademiks` (atau `cbt_exams`) yang mengikat `academic_year_id`, `semester`, `mata_pelajaran_id`, dan `class_id` sasaran.

---

### 🔴 Kekurangan 3: Belum Ada Tabel Nilai Siswa
* **Kondisi Saat Ini**: Tidak ada tabel untuk menyimpan skor angka hasil ujian siswa.
* **Masalah**: Nilai dari ZenCBT tidak memiliki wadah untuk disimpan di aplikasi absensi.
* **Solusi**: Buat tabel `nilai_ujians` yang merekam perolehan skor siswa per ujian, mencakup:
  - Nilai akhir (skala 0 - 100)
  - Detail butir soal: Jumlah Benar, Jumlah Salah, Jumlah Kosong
  - Status keikutsertaan: `hadir`, `tidak_hadir`, `susulan`
  - Rekaman kecurangan/pelanggaran dari CBT: `violation_count` (berapa kali siswa mencoba keluar layar/buka tab lain)
  - Waktu pengerjaan: `started_at`, `submitted_at`

---

### 🔴 Kekurangan 4: Ketiadaan Kredensial Cetak Kartu Ujian CBT Siswa
* **Kondisi Saat Ini**:
  - Kolom password di tabel `students` disimpan dalam bentuk hash `bcrypt` (satu arah, tidak bisa dibaca kembali).
  - ZenCBT membutuhkan `password_plain` (teks terbaca) agar siswa bisa login di ruang ujian dan agar panitia bisa mencetak kartu peserta tes.
* **Masalah**: Jika ingin mencetak kartu peserta tes dari Filament Laravel, kita tidak tahu password asli siswa jika tidak ada kolom atau format penamaan baku.
* **Solusi**: 
  - Tambahkan kolom `cbt_password` (teks biasa / auto-generated) di tabel `students` atau `student_enrollments`.
  - Atau gunakan formula standar otomatis yang konsisten (misalnya default: `NISN` atau `6 digit tanggal lahir DDMMYY`).

---

### 🔴 Kekurangan 5: Pengaturan Sesi & Ruang Ujian Siswa
* **Kondisi Saat Ini**: Belum ada penandaan sesi ujian bagi siswa.
* **Kondisi di ZenCBT**: ZenCBT memiliki kolom `sesi` (contoh: `'Sesi 1'`, `'Sesi 2'`, s/d `'Sesi 8'`).
* **Masalah**: Jika sekolah mengadakan ujian bergantian (pagi & siang karena komputer terbatas), ZenCBT membutuhkan informasi sesi ini untuk memfilter siapa yang boleh login di jam tertentu.
* **Solusi**: Tambahkan kolom `sesi_ujian` (default: `'Sesi 1'`) di tabel siswa/enrollment agar saat sync ke ZenCBT, pembagian sesi langsung terbentuk otomatis.

---

## 3. Kesimpulan Analisis

Database `projek-absensi-barcode` sudah memiliki fondasi data induk yang sangat baik (**Siswa, Kelas, Tahun Ajaran, Guru, Mapel**). 

Yang perlu kita tambahkan adalah **Layer Akademik & Nilai** (Tabel Master Ujian & Tabel Transaksi Nilai) serta sedikit penyesuaian kolom pada siswa/pengaturan sekolah untuk mendukung kredensial login CBT dan semester aktif.
