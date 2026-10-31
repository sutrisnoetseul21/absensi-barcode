# Rencana Kerja: Menu 5 - Perpustakaan Portal Siswa

> **Dokumen Rencana Detail**: Menu 5 Layanan Perpustakaan Siswa (Pinjaman Aktif, Riwayat Peminjaman & Kunjungan Fisik)  
> **Catatan Khusus Pengguna**: *"Menu Perpustakaan tetapi Pencarian Buku di hapus saja"* (Katalog & pencarian buku ditiadakan agar fokus pada pencatatan sirkulasi siswa).  
> **Referensi Web**: `http://localhost:8001/portal-siswa/perpustakaan` (`App\Livewire\SiswaPerpustakaan`)  
> **Status**: **Draft Siap Dikerjakan** 🚀

---

## 📌 Ringkasan Fitur
1. **Hero Banner & Kartu Anggota Perpustakaan**:
   - Menampilkan status keanggotaan perpustakaan siswa, nomor kartu/NISN, nama sekolah, dan ringkasan buku aktif.
2. **Tab 1: Pinjaman Aktif (Sedang Dipinjam)**:
   - Daftar buku fisik yang sedang dibawa siswa.
   - Informasi detail buku: Judul buku, penulis, kategori, kode barcode/eksemplar.
   - Penghitungan Jatuh Tempo: Tanggal pinjam, tanggal batas pengembalian, status sisa hari atau badge peringatan *"Terlambat X Hari"*, estimasi denda (jika melewati tenggat).
3. **Tab 2: Riwayat Peminjaman (Selesai/Kembali)**:
   - Daftar histori buku-buku yang pernah dipinjam siswa dan sudah dikembalikan ke perpustakaan.
   - Tanggal pinjam, tanggal pengembalian riil, status denda (lunas/tidak ada).
4. **Tab 3: Riwayat Kunjungan Fisik**:
   - Catatan histori saat siswa melakukan scan presensi/kehadiran fisik di ruangan perpustakaan sekolah.
   - Tanggal kunjungan, jam masuk, jam keluar, dan status kehadiran.

---

## 🗂️ Rincian Sub-tahap Pengerjaan

### 1️⃣ Sub-tahap 1: Backend REST API Perpustakaan Siswa (`projek-absensi-barcode`)
* **Controller**: `app/Http/Controllers/Api/Mobile/Siswa/SiswaPerpustakaanController.php`
* **Rute API** (di `routes/api.php` under prefix `/api/v1/siswa/perpustakaan`):
  * `GET /summary` -> Ringkasan keanggotaan perpustakaan, jumlah pinjaman aktif, total riwayat pinjam, dan total kunjungan.
  * `GET /pinjaman-aktif` -> Daftar buku yang sedang dipinjam (perhitungan sisa hari & denda keterlambatan).
  * `GET /riwayat-pinjam` -> Daftar histori peminjaman yang sudah selesai / dikembalikan (dengan filter & paginasi).
  * `GET /riwayat-kunjungan` -> Daftar kunjungan fisik siswa ke perpustakaan.
* **Pengujian**: Validasi endpoint via `curl` (GET summary, pinjaman-aktif, riwayat-pinjam, riwayat-kunjungan).

### 2️⃣ Sub-tahap 2: Flutter Models, Endpoints & Service (`apk-android-presensi`)
* **Endpoints**: Daftarkan rute API di `lib/core/constants/api_endpoints.dart`.
* **Model**: `lib/features/siswa/models/siswa_perpustakaan_model.dart`:
  * `SiswaPerpustakaanSummaryResponse`, `SiswaPinjamanAktifItem`, `SiswaRiwayatPinjamItem`, `SiswaKunjunganItem`.
* **Service**: `lib/features/siswa/services/siswa_perpustakaan_service.dart`:
  * `getSummary()`, `getPinjamanAktif()`, `getRiwayatPinjam()`, `getRiwayatKunjungan()`.

### 3️⃣ Sub-tahap 3: Layar Utama Perpustakaan Siswa & 3 Tab Bar (`apk-android-presensi`)
* **Layar**: `lib/features/siswa/screens/perpustakaan_siswa_screen.dart`
* **Fitur UI**:
  * Hero Banner bernuansa Indigo/Emerald (*"Layanan Sirkulasi & Literasi Perpustakaan"*).
  * Kartu Anggota Perpustakaan dengan Barcode ID.
  * 3 Tab Navigasi Segmented:
    - **Tab 1: Pinjaman Aktif**: Kartu buku dengan cover, badge sisa hari / terlambat, peringatan jatuh tempo.
    - **Tab 2: Riwayat Pinjam**: Kartu histori pengembalian buku dan status denda.
    - **Tab 3: Kunjungan Fisik**: Timeline riwayat absensi fisik di ruang perpustakaan.
  * Layout responsif Tablet (grid multi-kolom) vs HP (list 1 kolom).

### 4️⃣ Sub-tahap 4: Integrasi Beranda Siswa, Audit Aksesibilitas & Validasi
* Hubungkan kartu Menu 5 di `dashboard_siswa_screen.dart` ke `PerpustakaanSiswaScreen`.
* Sinkronisasi badge buku aktif di beranda siswa (`_fetchSummary()`).
* Audit Aksesibilitas (WCAG AA): Kontras warna, semantik TalkBack, minimum hit target 48x48 dp.
* Analisis statis `flutter analyze` terverifikasi **0 Error**.
