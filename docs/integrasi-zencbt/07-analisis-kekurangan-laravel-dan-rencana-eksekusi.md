# 07. Analisis Kekurangan Laravel & Rencana Eksekusi

Dokumen ini mencatat evaluasi akhir mengenai kekurangan pada aplikasi Laravel (`projek-absensi-barcode`) untuk menampung data asesmen (Ulangan Harian, ASTS, ASAS) dan integrasi ZenCBT, serta daftar rencana tindakan teknis yang akan dieksekusi.

---

## 1. Rangkuman Kesepakatan Alur Kerja (Workflows)

Sistem menggunakan alur kerja **2 Arah (Bidirectional)**:

```
[ Laravel Absensi (Master) ] 
       │
       │  (1) PUSH Master: Tahun Ajaran, Kelas, Mapel, Akun Siswa (NISN & Password Kartu)
       ▼
[ ZenCBT Engine (Pelaksanaan) ]
       │
       │  (2) Siswa Mengerjakan Ujian di ZenCBT (Event: "ASTS Ganjil 2025/2026")
       ▼
[ ZenCBT Engine (Selesai Ujian) ]
       │
       │  (3) PULL Nilai: Nilai Akhir (0-100), Benar, Salah, Kosong, Pelanggaran
       ▼
[ Laravel Absensi (Penyimpanan Nilai) ] ──► Terkunci ke Tahun Ajaran & Semester Aktif
```

---

## 2. Inventaris Kekurangan Riil di Laravel Absensi Saat Ini

Sebelum integrasi ini dapat berjalan, berikut adalah komponen yang **belum ada** di kode Laravel dan wajib dibuat:

### A. Database (Migration)
1. **Tabel Baru `ujian_akademiks`**:
   * Menampung agenda ujian: Ulangan Harian, ASTS, ASAS, Try Out.
   * Menyimpan `cbt_event_nama` untuk mencocokkan dengan kolom `Event` di ZenCBT.
   * Mengikat `academic_year_id`, `semester`, `mata_pelajaran_id`, `teacher_id`, `kkm`, dan durasi.
2. **Tabel Baru `ujian_akademik_classes`**:
   * Pivot untuk menentukan rombel mana saja yang ditugaskan ikut ujian (misal: hanya kelas 7A untuk UH, atau 7A s/d 7D untuk ASTS).
3. **Tabel Baru `nilai_ujians`**:
   * Menampung rekap perolehan siswa: `nilai_akhir`, `jumlah_benar`, `jumlah_salah`, `jumlah_kosong`, `is_tuntas`, `violation_count`, `started_at`, `submitted_at`, `synced_at`.
4. **Update Tabel `school_settings`**:
   * Tambah kolom `active_semester` (`ganjil` / `genap`).
   * Tambah konfigurasi koneksi CBT (`cbt_driver`, `cbt_api_url`, `cbt_api_key`).
5. **Tabel Baru Pendamping `student_cbt_profiles` (Tabel `students` 0% Disentuh / Aman 100% untuk ERP)**:
   * Mengikuti pola bawaan ERP Anda (`StudentPresensiProfile`), seluruh data pendukung CBT dipisah ke tabel `student_cbt_profiles`.
   * Kolom: `cbt_password` (password kartu ujian), `cbt_sesi` (`Sesi 1`, `Sesi 2`, dst), `cbt_status` (`active`, `blocked`).
   * **Tabel `students` tidak diubah sama sekali** sehingga presensi barcode, portal siswa, BK, dan SPIKAP terjamin aman.
6. **Update Tabel `mata_pelajarans`**:
   * Tambah kolom `kategori` (`Umum`, `Muatan Lokal`).
   * Tambah kolom `kkm_default` (default: 75.00).

---

### B. Antarmuka / Menu Filament (UI Admin & Guru)
1. **Resource Baru `UjianAkademikResource`**:
   * Menu untuk membuat agenda ujian, memilih kelas sasaran, menentukan KKM dan durasi.
2. **Tombol Aksi Sinkronisasi**:
   * *"Sinkronkan Master ke ZenCBT"* (Kirim Tahun Ajaran, Tingkat, Rombel, Mapel).
   * *"Kirim Akun Siswa ke ZenCBT"* (Kirim NISN, password kartu, dan sesi).
   * *"Cetak Kartu Peserta Ujian"* (Format PDF siap cetak massal per kelas).
   * *"Tarik Nilai dari ZenCBT"* (Otomatis mengisi tabel `nilai_ujians`).
3. **Tampilan Rekap Nilai**:
   * Tabel rekap per kelas dan mapel, rata-rata kelas, dan penanda siswa belum tuntas KKM.

---

### C. Jembatan API (ZenCBT Bridge Service)
1. Pembuatan project/service mandiri di folder `zencbt-bridge`.
2. Menyediakan endpoint:
   * `POST /api/v1/sync/master`
   * `POST /api/v1/sync/students`
   * `GET /api/v1/exams`
   * `GET /api/v1/exams/{id}/results`
   * `POST /api/v1/students/{id}/reset-session`

---

## 3. Rencana Tahapan Eksekusi (Action Plan)

1. **Langkah 1**: Buat folder `docs/integrasi-zencbt/` di dalam project `projek-absensi-barcode` sebagai arsip resmi dokumentasi di repository ini.
2. **Langkah 2**: Jalankan migrasi penambahan tabel dan kolom di database `projek-absensi-barcode`.
3. **Langkah 3**: Buat model Eloquent dan relasinya (`UjianAkademik`, `NilaiUjian`, dll).
4. **Langkah 4**: Siapkan folder terpisah `zencbt-bridge` untuk API engine.
5. **Langkah 5**: Bangun antarmuka Filament dan tombol-tombol aksi sinkronisasi.
