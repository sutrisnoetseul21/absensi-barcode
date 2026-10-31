# Rencana Kerja: Menu 4 - SPIKAP (Anti-Perundungan & Karakter) Portal Siswa

> **Dokumen Rencana Detail**: Menu 4 Layanan Pengaduan Kekerasan, Anti-Perundungan & Tracking Penanganan Siswa  
> **Referensi Web**:  
> - `http://localhost:8001/portal-siswa/spikap` (`App\Livewire\PortalSiswa\SpikapLaporanList`)  
> - `http://localhost:8001/portal-siswa/spikap/form` (`App\Livewire\PortalSiswa\SpikapLaporanForm`)  
> **Status**: **SELESAI 100%** ✅ (Sub-tahap 1 s.d. 5 Tuntas & Terverifikasi)

---

## 📌 Ringkasan Fitur
1. **Ruang Aman & Bebas Perundungan**:
   - Menyediakan sarana bagi siswa untuk melaporkan insiden kekerasan atau perundungan (*bullying*) secara aman, terpercaya, dan terlindungi kerahasiaannya.
2. **Dua Sifat Laporan**:
   - **🚨 Darurat**: Membutuhkan penanganan instan hari ini; sistem otomatis memprioritaskan dan meneruskan notifikasi darurat ke Tim Pencegahan & Penanganan Kekerasan (TPPK), Kepala Sekolah, dan Wali Kelas.
   - **Biasa**: Untuk konseling, mediasi, atau bimbingan; siswa dapat memilih ditujukan kepada **Guru BK** atau **Wali Kelas**.
3. **Kategori Jenis Perundungan**:
   - Fisik (pemukulan, dorongan, kekerasan fisik).
   - Verbal (ejekan, hinaan, ancaman, body shaming).
   - Sosial (pengucilan, pemfitnahan, penyebaran rumor).
   - Digital / Cyberbullying (pelecehan di media sosial, teror chat).
   - Lainnya.
4. **Bukti Lampiran Foto & Video dengan Kompresi Otomatis (Ala WhatsApp)**:
   - Bukti foto (maksimal 5 foto) dikompres otomatis oleh `ImageCompressUtil` (maksimal 1280px, quality 70%) sebelum diunggah ke server.
   - Opsi lampiran video singkat (maksimal 2 video).
5. **Transparansi Tracking Status (Timeline)**:
   - Siswa dapat memantau setiap perkembangan investigasi kasus secara bertahap:
     - ⏳ **Diterima**: Laporan berhasil masuk ke sistem.
     - 🔍 **Dalam Investigasi**: Tim TPPK / Guru BK / Wali Kelas sedang memanggil saksi & menelusuri kejadian.
     - ✅ **Selesai**: Kasus telah diselesaikan secara damai, mediasi, atau tindakan pembinaan.
     - ❌ **Ditolak**: Laporan tidak valid atau tidak relevan.
   - Setiap tahapan dilengkapi riwayat catatan petugas dan waktu penanganan.

---

## 🗂️ Rincian Sub-tahap Pengerjaan

### 1️⃣ Sub-tahap 1: Backend REST API SPIKAP Siswa (`projek-absensi-barcode`) [SELESAI ✅]
* Controller: [`SiswaSpikapController.php`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Http/Controllers/Api/Mobile/Siswa/SiswaSpikapController.php)
* Rute API (di `routes/api.php` under prefix `/api/v1/siswa/spikap`):
  * `GET /dashboard` -> Daftar laporan siswa, statistik ringkas (total, diterima, investigasi, selesai, darurat), hotline TPPK & branding.
  * `GET /laporan` -> Riwayat laporan dengan filter status, sifat, jenis, dan pencarian teks.
  * `GET /laporan/{id}` -> Detail laporan, daftar bukti foto/video (URL preview & download), dan riwayat timeline investigasi (`SpikapLogStatus`).
  * `POST /laporan` -> Pengajuan laporan baru (sifat biasa/darurat, tujuan penerima, jenis perundungan, uraian, lokasi, waktu, multi-upload foto & video), auto-insert log status awal, serta trigger notifikasi WhatsApp via `SpikapNotificationService`.
  * `GET /lampiran/{id}` & `GET /lampiran/{id}/download` -> Akses streaming/download berkas foto/video bukti terotentikasi.
* Pengujian live via `curl`: **200 OK & 201 Created** (uji laporan biasa & laporan darurat dengan file gambar valid).

### 2️⃣ Sub-tahap 2: Flutter Models, Endpoints & Service (`apk-android-presensi`) [SELESAI ✅]
* Endpoints di [`api_endpoints.dart`](file:///home/sutris-remote/Documents/projek-laravel/apk-android-presensi/lib/core/constants/api_endpoints.dart):
  * `siswaSpikapDashboard`, `siswaSpikapList`, `siswaSpikapSubmit`, `siswaSpikapDetail`, `siswaSpikapLampiran`, `siswaSpikapDownloadLampiran`.
* Data Model di [`siswa_spikap_model.dart`](file:///home/sutris-remote/Documents/projek-laravel/apk-android-presensi/lib/features/siswa/models/siswa_spikap_model.dart):
  * `SiswaSpikapBranding`, `SiswaSpikapCounts`, `SiswaSpikapLampiranItem`, `SiswaSpikapPetugas`, `SiswaSpikapTrackingLog`, `SiswaSpikapLaporanItem`, `SiswaSpikapDashboardResponse`, `SiswaSpikapListResponse`, `SiswaSpikapDetailResponse`.
* Service di [`siswa_spikap_service.dart`](file:///home/sutris-remote/Documents/projek-laravel/apk-android-presensi/lib/features/siswa/services/siswa_spikap_service.dart).

### 3️⃣ Sub-tahap 3: Layar Utama SPIKAP Siswa (`apk-android-presensi`) [SELESAI ✅]
* Layar: [`spikap_siswa_screen.dart`](file:///home/sutris-remote/Documents/projek-laravel/apk-android-presensi/lib/features/siswa/screens/spikap_siswa_screen.dart)
* Hero Banner SPIKAP nuansa rose-carmine (*"Layanan Pengaduan & Ruang Aman Anti-Perundungan"*).
* Banner Perlindungan Kerahasiaan & Hotline Bantuan Darurat Sekolah.
* Filter Chips status (`Semua`, `Diterima`, `Dalam Investigasi`, `Selesai`) dan sifat (`Semua`, `Darurat`, `Biasa`).
* Live Search pencarian kata kunci, lokasi, atau isi aduan.
* Daftar Kartu Laporan: Badge sifat, jenis perundungan, status warna, ringkasan uraian, waktu, lokasi, dan indikator lampiran.
* Modal / BottomSheet Tracking Kasus (`_ModalTrackingSpikap`): Menampilkan alur timeline investigasi vertikal bertahap dengan petugas dan waktu, serta dialog pratinjau gambar.
* Floating Action Button: `+ Buat Laporan`.
* Layout responsif Tablet (grid 2 kolom) vs HP (1 kolom).

### 4️⃣ Sub-tahap 4: Layar Formulir Lapor Baru & Kompresi Foto (`apk-android-presensi`) [SELESAI ✅]
* Layar: [`form_spikap_siswa_screen.dart`](file:///home/sutris-remote/Documents/projek-laravel/apk-android-presensi/lib/features/siswa/screens/form_spikap_siswa_screen.dart)
* Pemilih Sifat Laporan: `🚨 Darurat` vs `Biasa`.
* Pemilih Tujuan Penerima: `Guru BK` atau `Wali Kelas` (dinamis aktif jika sifat = biasa).
* Pilihan Jenis Perundungan: Fisik, Verbal, Sosial, Digital, Lainnya.
* Input Lokasi Kejadian & Tanggal/Waktu Kejadian (Date & Time Picker terintegrasi).
* Textarea Uraian Kejadian (validasi minimal 20 karakter).
* Lampiran Bukti Foto (maks 5 foto): Terintegrasi dengan `ImageCompressUtil` otomatis mengompres foto ala WhatsApp (maksimal 1280px, quality 70%, hemat ~95% kuota) sebelum diunggah.
* Lampiran Bukti Video (maks 2 video opsional, batas 50 MB).
* Dialog konfirmasi kerahasiaan & keamanan identitas sebelum pengiriman data.

### 5️⃣ Sub-tahap 5: Integrasi Beranda Siswa, Audit Aksesibilitas & Validasi [SELESAI ✅]
* Sambungkan kartu Menu 4 di [`dashboard_siswa_screen.dart`](file:///home/sutris-remote/Documents/projek-laravel/apk-android-presensi/lib/features/siswa/screens/dashboard_siswa_screen.dart) ke `SpikapSiswaScreen`.
* Auto-refresh jumlah laporan aktif beranda saat kembali dari layar SPIKAP (`_fetchSummary()`).
* Audit Aksesibilitas (WCAG AA): Tooltip lengkap pada seluruh icon button, target sentuh chip & tombol min 44-48 dp, dan rasio kontras warna standar teks & badge.
* Analisis statis `flutter analyze` terverifikasi **0 Error** (0 issue pada modul SPIKAP siswa).
