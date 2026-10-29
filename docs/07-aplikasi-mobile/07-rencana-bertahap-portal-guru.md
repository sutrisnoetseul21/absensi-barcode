# 🗺️ 07. Rencana Kerja Bertahap (Step-by-Step Plan) Portal Guru Android

Dokumen ini merupakan **Master Plan** pengerjaan **Portal Guru** pada aplikasi mobile Flutter (`apk-android-presensi`) yang mengacu pada fitur `localhost:8001/portal-guru` di backend Laravel (`projek-absensi-barcode`).

---

## 🎯 1. Konsep Utama UI/UX Android (Tanpa Navigasi Kiri)

Berbeda dengan tampilan Web Desktop yang memiliki *Sidebar Navigasi Kiri*, tampilan Android menggunakan konsep **Command Center Kotak-Kotak (Card/Grid Menu)**:

1. **Tanpa Sidebar / Drawer Kiri**:
   - Seluruh akses modul ditampilkan langsung berupa **Kotak-Kotak Menu (Grid Cards)** di halaman utama [`DashboardGuruScreen`](file:///home/sutris-remote/Documents/projek-laravel/apk-android-presensi/lib/features/guru/screens/dashboard_guru_screen.dart).
   - Setiap kotak menu menampilkan **Ikon**, **Badge Status Live** (misal: jumlah kelas, jumlah izin *pending*, jurnal hari ini), **Judul Menu**, dan **Deskripsi Singkat**.
2. **Header Ringkas & Kartu Sambutan**:
   - Bagian atas menampilkan nama sekolah dinamis, sapaan nama guru (`Selamat Datang, Bapak/Ibu Guru!`), serta tombol keluar (*Logout*).
3. **Pengerjaan Bertahap (Satu per Satu)**:
   - Setiap modul dikerjakan secara berurutan (Backend API Endpoint $\rightarrow$ UI Screen Flutter $\rightarrow$ Pengujian di Chrome/Android) sebelum melangkah ke nomor berikutnya.

---

## 📋 2. Urutan 9 Menu Kotak-Kotak Portal Guru

Berikut adalah 9 menu utama yang telah disepakati beserta rincian fitur dan rencana tahapan pengerjaannya:

### 📌 Tahap 0: Beranda Kotak-Kotak Portal Guru (`DashboardGuruScreen`)
* **Tujuan**: Merombak beranda Portal Guru menjadi tampilan **9 Kotak Menu** yang rapi, responsif, dan menampilkan *badge* ringkasan data dari endpoint `GET /api/v1/guru/dashboard-summary`.
* **Daftar 9 Kotak Menu**:
  1. **Presensi** *(Fokus murni Presensi, bukan Presensi & Akademik)*
  2. **Presensi Sholat Dhuhur**
  3. **Jurnal Mengajar**
  4. **Persetujuan Ijin**
  5. **Guru Wali (SI-WALI)**
  6. **Data Siswa**
  7. **SPIKAP (Anti-Perundungan & Pengaduan Siswa)**
  8. **Perpustakaan**
  9. **Profil Saya**

---

### 📌 Tahap 1: Menu 1 — Presensi (Fokus Murni Presensi)
* **Referensi Web**: `/portal-guru/akademik` ([`WaliKelasDashboard.php`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Livewire/WaliKelasDashboard.php)) & Scan Presensi.
* **Penyesuaian Android**: Label menu diubah menjadi **"Presensi"** (tanpa kata *& Akademik* agar fokus pada kehadiran siswa).
* **Cakupan Fitur**:
  1. **Pilih Kelas Rombel & Tanggal**: Memilih kelas binaan/ampuan dan tanggal kehadiran.
  2. **Ringkasan Statistik Harian**: Kartu jumlah siswa *Hadir*, *Terlambat*, *Sakit*, *Izin*, dan *Alpa*.
  3. **Input / Koreksi Presensi Manual Cepat**: Daftar siswa satu kelas dengan tombol cepat (**H** / **S** / **I** / **A**) dan catatan wali kelas.
  4. **Scan Barcode / Kamera HP (Opsional/Cepat)**: Pintasan pemindaian kartu NIS/NISN siswa.
  5. **Rekap Kehadiran Kelas**: Melihat persentase kehadiran siswa dalam kelas tersebut.
* **Endpoint API Backend**:
  - `GET /api/v1/guru/presensi/classes` (Daftar kelas binaan/ampuan)
  - `GET /api/v1/guru/presensi/students?class_id={id}&date={YYYY-MM-DD}`
  - `POST /api/v1/guru/presensi/save` (Simpan status presensi siswa satuan/massal)

---

### 📌 Tahap 2: Menu 2 — Presensi Sholat Dhuhur
* **Referensi Web**: `/portal-guru/sholat-dhuhur` ([`WaliKelasSholatDhuhur.php`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Livewire/PortalGuru/SholatDhuhur/WaliKelasSholatDhuhur.php)).
* **Cakupan Fitur**:
  1. **Filter Kelas & Tanggal Ibadah**: Otomatis menyesuaikan hari (Sholat Dhuhur atau Sholat Jumat).
  2. **Aksi Cepat "Semua Jamaah"**: Satu tombol untuk menandai seluruh siswa Muslim di kelas sebagai *Jamaah*.
  3. **Status Per Siswa**: Pilihan cepat *Jamaah*, *Masbuk*, *Munfarid*, *Berhalangan/Haid* (khusus siswi perempuan), atau *Tidak Sholat*.
  4. **Rekap Matriks Kepatuhan**: Ringkasan kepatuhan sholat siswa di kelas binaan.
* **Endpoint API Backend**:
  - `GET /api/v1/guru/sholat-dhuhur?class_id={id}&date={YYYY-MM-DD}`
  - `POST /api/v1/guru/sholat-dhuhur/bulk-save`

---

### 📌 Tahap 3: Menu 3 — Jurnal Mengajar
* **Referensi Web**: `/portal-guru/jurnal-mengajar` & `/portal-guru/jurnal-mengajar/tambah`.
* **Cakupan Fitur**:
  1. **Daftar Riwayat Jurnal Mengajar**: Menampilkan daftar jurnal yang sudah diisi guru (filter bulan/kelas) beserta badge jumlah jurnal hari ini.
  2. **Form Tambah Jurnal Mengajar (`+ Isi Jurnal`)**:
     - Tanggal KBM & Mata Pelajaran (otomatis terdeteksi dari penugasan guru).
     - Pilih Kelas Rombel.
     - Jam Mulai (Ke-1 s/d 15) & Durasi Pembelajaran (1 JP s/d 10 JP) beserta kalkulasi otomatis rentang waktu KBM.
     - Materi Pokok / Topik KBM, Kegiatan Pembelajaran, dan Catatan Kelas.
     - Pencatatan ketidakhadiran siswa pada jam pelajaran tersebut.
* **Endpoint API Backend**:
  - `GET /api/v1/guru/jurnal-mengajar`
  - `GET /api/v1/guru/jurnal-mengajar/form-options` (Daftar mapel & kelas yang diampu guru)
  - `POST /api/v1/guru/jurnal-mengajar`

---

### 📌 Tahap 4: Menu 4 — Persetujuan Ijin
* **Referensi Web**: `/portal-guru/ijin-kehadiran` ([`IjinKehadiranList.php`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Livewire/PortalGuru/IjinKehadiranList.php)).
* **Cakupan Fitur**:
  1. **Badge Statistik Status**: Menampilkan jumlah permohonan *Menunggu (Pending)*, *Diterima*, dan *Ditolak*.
  2. **Daftar Permohonan Izin / Sakit Siswa**: Menampilkan nama siswa, kelas, jenis izin (Sakit/Izin), rentang tanggal, keterangan orang tua, dan lampiran foto surat/bukti.
  3. **Tindakan Verifikasi (Setujui / Tolak)**:
     - Saat disetujui, otomatis menyinkronkan status kehadiran siswa ke tabel `attendances`.
     - Menyertakan catatan konfirmasi dari wali kelas.
* **Endpoint API Backend**:
  - `GET /api/v1/guru/ijin-kehadiran?status={pending|approved|rejected}`
  - `POST /api/v1/guru/ijin-kehadiran/{id}/verify`

---

### 📌 Tahap 5: Menu 5 — Guru Wali (SI-WALI)
* **Referensi Web**: `/portal-guru/guru-wali/*` (Permendikdasmen No. 11/2025).
* **Cakupan Fitur**:
  1. **Kelompok Binaan Saya**: Daftar peserta didik dalam kelompok asuh karakter guru wali.
  2. **Jurnal Pendampingan Guru Wali**: Mencatat bimbingan karakter, akademik, dan sosial siswa asuh.
  3. **Ruang Aman Cerita (Konsultasi Siswa)**: Melihat dan menanggapi pengajuan cerita/konsultasi dari siswa binaan.
  4. **Pemantauan Bulanan**: Ringkasan perkembangan siswa asuh.
* **Endpoint API Backend**:
  - `GET /api/v1/guru/guru-wali/kelompok`
  - `GET /api/v1/guru/guru-wali/jurnal` & `POST /api/v1/guru/guru-wali/jurnal`
  - `GET /api/v1/guru/guru-wali/konsultasi` & `POST /api/v1/guru/guru-wali/konsultasi/{id}/reply`

---

### 📌 Tahap 6: Menu 6 — Data Siswa
* **Referensi Web**: `/portal-guru/data-siswa` ([`DataSiswaList.php`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Livewire/PortalGuru/DataSiswaList.php)).
* **Cakupan Fitur**:
  1. **Pencarian & Filter Kelas Binaan**: Mencari siswa berdasarkan nama, NIS, atau NISN.
  2. **Kartu & Biodata Siswa**: Menampilkan foto, NISN, NIS, jenis kelamin, kelas, kontak orang tua (dengan tombol hubungi WA cepat), serta tampilan QR/Barcode kartu pelajar siswa.
* **Endpoint API Backend**:
  - `GET /api/v1/guru/data-siswa?class_id={id}&search={keyword}`
  - `GET /api/v1/guru/data-siswa/{id}`

---

### 📌 Tahap 7: Menu 7 — SPIKAP (Anti-Perundungan & Pengaduan Siswa)
* **Referensi Web**: `/portal-guru/{spikapSlug}` ([`SpikapInbox.php`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Livewire/PortalGuru/SpikapInbox.php)).
* **Cakupan Fitur**:
  1. **Kotak Masuk Laporan / Aduan**: Menampilkan daftar tiket aduan siswa (status: *Darurat*, *Aktif/Investigasi*, *Selesai*).
  2. **Detail & Tindak Lanjut Aduan**: Membaca kronologi laporan, bukti lampiran, dan memperbarui status penanganan/catatan tindak lanjut sesuai hak akses Satgas/BK/Wali Kelas.
* **Endpoint API Backend**:
  - `GET /api/v1/guru/spikap/reports`
  - `GET /api/v1/guru/spikap/reports/{id}`
  - `POST /api/v1/guru/spikap/reports/{id}/follow-up`

---

### 📌 Tahap 8: Menu 8 — Perpustakaan
* **Referensi Web**: `/portal-guru/perpustakaan` ([`GuruPerpustakaan.php`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Livewire/GuruPerpustakaan.php)).
* **Cakupan Fitur**:
  1. **Katalog Buku Sekolah**: Pencarian koleksi buku perpustakaan beserta status ketersediaan eksemplar di rak.
  2. **Pinjaman Aktif & Riwayat Peminjaman Guru**: Memantau daftar buku yang sedang dipinjam oleh guru beserta tanggal jatuh tempo pengembalian.
* **Endpoint API Backend**:
  - `GET /api/v1/guru/perpustakaan/katalog?search={keyword}`
  - `GET /api/v1/guru/perpustakaan/pinjaman-saya`

---

### 📌 Tahap 9: Menu 9 — Profil Saya
* **Referensi Web**: `/portal-guru/profil` ([`GuruProfil.php`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Livewire/GuruProfil.php)).
* **Cakupan Fitur**:
  1. **Biodata & Penugasan Akademik**: Menampilkan foto profil, Nama Lengkap, NIP/NUPTK, Email, Nomor HP/WhatsApp, serta daftar tugas mengajar & wali kelas aktif.
  2. **Pengaturan Akun & Keamanan**: Form pembaruan kontak/email dan **Ganti Kata Sandi (Password)**.
* **Endpoint API Backend**:
  - `GET /api/v1/guru/profil`
  - `POST /api/v1/guru/profil/update`
  - `POST /api/v1/guru/profil/password`

---

## 🚀 3. Strategi Eksekusi Bertahap

1. **Langkah Awal (Tahap 0)**: Membangun **Tampilan Kotak-Kotak 9 Menu di `DashboardGuruScreen`** lengkap dengan integrasi angka ringkasan (*badge counter*) dari database.
2. **Langkah Berikutnya (Tahap 1 s/d 9)**: Mengerjakan isi halaman detailnya **satu per satu mulai dari Nomor 1 (Presensi)** hingga **Nomor 9 (Profil Saya)**.

