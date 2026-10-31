# Rencana Kerja: Menu 6 - Kartu Pelajar Digital & Unduh PDF

> **Dokumen Rencana Detail**: Menu 6 Kartu Pelajar Digital Interaktif (Barcode Code 128 Native, Fullscreen Brightness Mode, dan Unduh Berkas Cetak PDF Resmi)  
> **Referensi Web**: `http://localhost:8001/portal-siswa/cetak-kartu` (`App\Http\Controllers\SiswaCetakController::cetakKartuMandiri`)  
> **Status**: **SELESAI 100%** ✅ (Sub-tahap 1 s.d. 4 Tuntas & Terverifikasi)

---

## 📌 Ringkasan Fitur

1. **Hero Header & Kontrol Cepat**:
   - Nuansa warna Indigo-Violet mewah (`#4338CA` s.d. `#7C3AED`).
   - Panduan praktis bagi siswa untuk pemindaian barcode di scanner gerbang presensi atau meja sirkulasi perpustakaan.

2. **Kartu Pelajar Digital Interaktif (Flip 2 Sisi / Elegan)**:
   - **Sisi Depan**:
     - Kop resmi sekolah (Logo instansi, Nama Sekolah: *SMP Negeri 3 Kedungreja*, badge *KARTU PELAJAR & PRESENSI*).
     - Foto resmi siswa dengan bingkai rapi (fallback avatar inisial bila foto belum diunggah).
     - Identitas lengkap: Nama Siswa, NISN, NIS, Kelas, Jenis Kelamin, Tempat & Tanggal Lahir.
     - **Barcode Code 128 Native**: Dihasilkan secara presisi melalui `BarcodeWidget(barcode: Barcode.code128(), data: barcodeData)` di atas latar putih kontras tinggi sehingga terbaca seketika oleh barcode scanner optik (laser/camera).
     - String angka NISN/barcode di bawah garis barcode dengan font monospaced tebal berjarak.
   - **Sisi Belakang (Ketentuan & Pengesahan)**:
     - Tata tertib kepemilikan kartu pelajar.
     - Catatan masa berlaku kartu (selama menjadi peserta didik aktif).
     - Tanda tangan / Nama Kepala Sekolah (*Anni Sulistyawati, S.Pd.*) beserta stempel digital.

3. **Mode Scan Layar Penuh (Fullscreen Scan Mode / Kecerahan Tinggi)**:
   - Menampilkan barcode Code-128 berukuran ekstra besar di tengah layar dengan background putih bersih dan kontras maksimal.
   - Fitur salin kode barcode ke clipboard dengan snackbar interaktif.

4. **Fitur Unduh / Cetak Dokumen PDF Resmi**:
   - **Unduh Kartu Pelajar (PDF)**: Mengunduh atau membuka berkas PDF kartu beresolusi cetak tinggi (*300 DPI layout `pdf.kartu-siswa-download`*) langsung ke galeri/unduhan perangkat siswa.
   - **Unduh Biodata Siswa (PDF)**: Mengunduh berkas PDF lembar biodata resmi siswa (`pdf.biodata-siswa`).

---

## 🗂️ Rincian Sub-tahap Pengerjaan

### 1️⃣ Sub-tahap 1: Backend REST API Kartu Siswa (`projek-absensi-barcode`) [SELESAI ✅]
* Controller: [`SiswaKartuController.php`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Http/Controllers/Api/Mobile/Siswa/SiswaKartuController.php) (extends `BaseSiswaApiController`).
* Rute API (di `routes/api.php` under prefix `/api/v1/siswa/kartu`):
  * `GET /digital-info` -> Mengambil data lengkap identitas kartu, logo sekolah, barcode data, kepala sekolah, dan URL unduh PDF.
  * `GET /download-pdf` -> Stream berkas PDF kartu siswa resmi (`Kartu_Siswa_<nisn>.pdf`) berukuran standar kartu pelajar ID-1 / portrait.
  * `GET /biodata-pdf` -> Stream berkas PDF biodata lengkap siswa.
* Pengujian live via `curl`: Status **200 OK** untuk json data dan stream berkas PDF.

### 2️⃣ Sub-tahap 2: Flutter Data Model, Endpoints & Service (`apk-android-presensi`) [SELESAI ✅]
* Registrasi rute endpoint di [`api_endpoints.dart`](file:///home/sutris-remote/Documents/projek-laravel/apk-android-presensi/lib/core/constants/api_endpoints.dart):
  * `siswaKartuDigitalInfo`, `siswaKartuDownloadPdf`, `siswaKartuBiodataPdf`.
* Data Model di [`siswa_kartu_model.dart`](file:///home/sutris-remote/Documents/projek-laravel/apk-android-presensi/lib/features/siswa/models/siswa_kartu_model.dart):
  * `SiswaKartuDigitalInfo`, `SiswaKartuResponse`.
* Service di [`siswa_kartu_service.dart`](file:///home/sutris-remote/Documents/projek-laravel/apk-android-presensi/lib/features/siswa/services/siswa_kartu_service.dart):
  * `getDigitalInfo()`
  * `getDownloadPdfUrl()`
  * `getBiodataPdfUrl()`

### 3️⃣ Sub-tahap 3: Layar Utama Kartu Pelajar & Dialog Layar Penuh (`apk-android-presensi`) [SELESAI ✅]
* Layar: [`kartu_siswa_screen.dart`](file:///home/sutris-remote/Documents/projek-laravel/apk-android-presensi/lib/features/siswa/screens/kartu_siswa_screen.dart)
  * Hero Header gradien Indigo-Violet dengan tombol refresh.
  * Kartu ID Pelajar elegan dengan efek shadow 3D, toggle animasi flip (Depan / Belakang).
  * Render Barcode Code 128 native presisi (`barcode_widget`).
  * Tombol aksi:
    - *Mode Scan Layar Penuh*: Dialog barcode high-contrast khusus pemindaian gerbang sekolah.
    - *Unduh Kartu PDF*: Membuka/mengunduh berkas PDF kartu pelajar via `url_launcher`.
    - *Unduh Biodata PDF*: Membuka/mengunduh berkas PDF biodata via `url_launcher`.
    - *Salin Barcode*: Salin kode ke clipboard dengan feedback visual.
  * Layout responsif Tablet (grid 2 kolom side-by-side kartu & aksi) vs HP (1 kolom vertikal).

### 4️⃣ Sub-tahap 4: Integrasi Beranda Siswa, Audit Aksesibilitas & Validasi [SELESAI ✅]
* Sambungkan kartu Menu 6 di [`dashboard_siswa_screen.dart`](file:///home/sutris-remote/Documents/projek-laravel/apk-android-presensi/lib/features/siswa/screens/dashboard_siswa_screen.dart) (`_navigateToKartu()`).
* Tombol cepat "Buka Kartu & PDF" disematkan pada dialog barcode beranda.
* Audit Aksesibilitas (WCAG AA): Tooltip informatif pada seluruh icon button, tap target min 48x48 dp, teks kontras tinggi, semantic labels pada barcode.
* Pengujian analisis statis: `flutter analyze` terverifikasi **0 Error** (0 issue pada seluruh modul siswa).
