# Rencana Kerja: Menu 3 - Guru Wali (SI-WALI) Portal Siswa

> **Dokumen Rencana Detail**: Menu 3 Layanan Bimbingan & Konsultasi Guru Wali Siswa  
> **Referensi Web**: `http://localhost:8001/portal-siswa/konsultasi-guru-wali` (`App\Livewire\PortalSiswa\KonsultasiGuruWaliList`)  
> **Status**: **SELESAI 100%** ✅ (Sub-tahap 1 s.d. 5 Tuntas & Terverifikasi)

---

## 📌 Ringkasan Fitur
1. **Profil Guru Wali Asuh**: Menampilkan identitas guru wali yang mengampu siswa (foto, nama lengkap, NIP/identitas, kontak cepat, dan nama kelompok binaan).
2. **2 Tab Utama Layanan**:
   - **Tab Konsultasi Mandiri (Curhat)**: Daftar permohonan konsultasi siswa ke guru wali, riwayat percakapan, status pengajuan (*Menunggu Konfirmasi*, *Dijadwalkan*, *Selesai*, *Ditolak*), dan aksi berikan feedback/ulasan.
   - **Tab Pendampingan Klasikal & Kelompok**: Catatan bimbingan bersama guru wali di kelas/kelompok asuh, materi arahan motivasi, catatan terbuka, dan RTL (Rencana Tindak Lanjut).
3. **Pop-up / Modal Mulai Bercerita (Curhat)**:
   - 4 Pilar Kategori: Akademik, Karakter, Minat Bakat, Sosial & Psikologis.
   - 2 Mode Konsultasi: *Tatap Muka Langsung* vs *Pesan Melalui Portal*.
   - Usulan jadwal pertemuan & detail cerita kendala yang dialami.
4. **Thread Percakapan Dua Arah (Chat)**:
   - Siswa dan guru wali dapat saling berkirim pesan secara berkala di sesi konsultasi yang aktif.
5. **Rating & Feedback**:
   - Evaluasi sesi setelah selesai (skala bintang 1-5 dan 3 pilihan emoji: *Lega*, *Biasa*, *Masih Bingung*).
6. **Push Notification (FCM)**:
   - Terintegrasi otomatis ke guru wali setiap ada curhat baru atau pesan baru dari siswa.

---

## 🗂️ Rincian Sub-tahap Pengerjaan

### 1️⃣ Sub-tahap 1: Backend REST API SI-WALI Siswa (`projek-absensi-barcode`) [SELESAI ✅]
* Controller: [`SiswaGuruWaliController.php`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Http/Controllers/Api/Mobile/Siswa/SiswaGuruWaliController.php)
* Rute API:
  * `GET /api/v1/siswa/guru-wali/dashboard` -> Info guru wali asuh & statistik sesi.
  * `GET /api/v1/siswa/guru-wali/konsultasi` -> Daftar sesi curhat mandiri (filter status, search, paginasi).
  * `POST /api/v1/siswa/guru-wali/konsultasi` -> Pengajuan curhat baru + trigger Push Notification FCM ke guru wali.
  * `GET /api/v1/siswa/guru-wali/konsultasi/{id}` -> Detail konsultasi + thread pesan balasan.
  * `POST /api/v1/siswa/guru-wali/konsultasi/{id}/pesan` -> Kirim pesan obrolan inline + notifikasi.
  * `POST /api/v1/siswa/guru-wali/konsultasi/{id}/feedback` -> Kirim penilaian bintang & emoji evaluasi.
  * `DELETE /api/v1/siswa/guru-wali/konsultasi/{id}` -> Batalkan pengajuan (jika masih *Menunggu Konfirmasi*).
  * `GET /api/v1/siswa/guru-wali/pendampingan-klasikal` -> Daftar bimbingan klasikal kelas.
* Uji coba endpoint live via `curl`: **200 OK & 201 Created**.

### 2️⃣ Sub-tahap 2: Model & Service Flutter (`apk-android-presensi`) [SELESAI ✅]
* Endpoints di [`api_endpoints.dart`](file:///home/sutris-remote/Documents/projek-laravel/apk-android-presensi/lib/core/constants/api_endpoints.dart):
  * `siswaGuruWaliDashboard`, `siswaGuruWaliKonsultasi`, `siswaGuruWaliDetailKonsultasi`, `siswaGuruWaliKirimPesan`, `siswaGuruWaliFeedback`, `siswaGuruWaliBatalKonsultasi`, `siswaGuruWaliKlasikal`.
* Data Model di [`siswa_guru_wali_model.dart`](file:///home/sutris-remote/Documents/projek-laravel/apk-android-presensi/lib/features/siswa/models/siswa_guru_wali_model.dart):
  * `SiswaGuruWaliProfile`, `SiswaGuruWaliCounts`, `SiswaKonsultasiItem`, `SiswaPesanItem`, `SiswaKonsultasiDetail`, `SiswaBimbinganKlasikalItem`.
* Service di [`siswa_guru_wali_service.dart`](file:///home/sutris-remote/Documents/projek-laravel/apk-android-presensi/lib/features/siswa/services/siswa_guru_wali_service.dart).

### 3️⃣ Sub-tahap 3: Layar Utama Guru Wali & Tab Bar Segmented (`apk-android-presensi`) [SELESAI ✅]
* Layar: [`guru_wali_siswa_screen.dart`](file:///home/sutris-remote/Documents/projek-laravel/apk-android-presensi/lib/features/siswa/screens/guru_wali_siswa_screen.dart)
* Hero Banner SI-WALI nuansa biru indigo (*"Ruang Aman Bercerita & Bimbingan"*).
* Kartu Profil Guru Wali Asuh: Foto, nama, NIP, nama kelompok, status aktif, tombol WhatsApp.
* Tab Bar Segmented:
  * **Konsultasi Mandiri / Curhat**: Daftar kartu konsultasi dengan badge status (*Menunggu*, *Dijadwalkan*, *Selesai*, *Ditolak*), mode (*Tatap Muka* / *Pesan*), dan kategori 4 pilar.
  * **Pendampingan Klasikal / Kelompok**: Daftar kartu sesi klasikal kelas, materi/arahan motivasi guru, catatan publik, dan rencana tindak lanjut.
* Filter chips status & live search.
* Floating Action Button: `+ Mulai Bercerita`.
* Layout responsif Tablet (grid 3 kolom & multi-kolom) vs HP (1 kolom).

### 4️⃣ Sub-tahap 4: Pop-up Mulai Bercerita & Detail Chat/Feedback (`apk-android-presensi`) [SELESAI ✅]
* **Modal Form "Mulai Bercerita / Curhat"** (`_ModalCurhatForm`):
  * Pilihan 4 Pilar Kategori (Akademik, Karakter, Minat Bakat, Sosial & Psikologis).
  * Pilihan Mode (*Tatap Muka* vs *Pesan Portal*).
  * Input topik & detail curhat/kendala.
  * Pemilih usulan waktu tatap muka (date/time picker).
* **Modal Detail Konsultasi & Obrolan Pesan** (`_ModalDetailKonsultasi`):
  * Ringkasan informasi konsultasi, info jadwal resmi dari guru, ruangan, dan tanggapan awal.
  * Thread obrolan interaktif (bubble chat pesan siswa & guru wali).
  * Input kirim pesan baru inline.
* **Modal Ulasan / Feedback Sesi**:
  * Pemilih bintang (1 s.d. 5).
  * 3 Pilihan Emoji: *Lega*, *Biasa*, *Masih Bingung*.
  * Textarea kesan & catatan siswa.
* Tombol Batalkan Konsultasi jika masih berstatus *Menunggu Konfirmasi*.

### 5️⃣ Sub-tahap 5: Integrasi Beranda Siswa, Audit Aksesibilitas & Validasi [SELESAI ✅]
* Hubungkan kartu Menu 3 di [`dashboard_siswa_screen.dart`](file:///home/sutris-remote/Documents/projek-laravel/apk-android-presensi/lib/features/siswa/screens/dashboard_siswa_screen.dart) ke `GuruWaliSiswaScreen`.
* Refresh dashboard otomatis (`_fetchSummary()`) saat kembali dari layar SI-WALI untuk sinkronisasi badge sesi aktif.
* Audit Aksesibilitas (WCAG AA): Tooltip lengkap pada seluruh icon button, minimum hit target 48x48 dp pada tombol emoji/bintang/clear, dan rasio kontras warna standar teks & badge.
* Analisis statis `flutter analyze` terverifikasi **0 Error** (0 issue pada modul SI-WALI siswa).
