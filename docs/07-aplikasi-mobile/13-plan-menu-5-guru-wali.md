# 📋 Plan Matang Tahap 5 (Menu 5): Guru Wali (SI-WALI) (`/portal-guru/guru-wali/*`)

Dokumen ini merupakan **Blueprint Teknis & Panduan Implementasi Komprehensif** untuk **Menu 5: Guru Wali (SI-WALI)** pada aplikasi mobile Flutter (`apk-android-presensi`), yang diselaraskan 100% dengan regulasi Permendikdasmen No. 11/2025, Kepmendikdasmen No. 221/P/2025, serta sistem web Livewire backend:
- **Daftar Kelompok Binaan Guru**: [`KelompokSaya.php`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Livewire/PortalGuru/GuruWali/KelompokSaya.php) & [`kelompok-saya.blade.php`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/resources/views/livewire/portal-guru/guru-wali/kelompok-saya.blade.php) (`/portal-guru/guru-wali/kelompok`).
- **Jurnal Bimbingan Individu & Klasikal**: [`JurnalList.php`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Livewire/PortalGuru/GuruWali/JurnalList.php), [`JurnalForm.php`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Livewire/PortalGuru/GuruWali/JurnalForm.php), & [`JurnalKelompokForm.php`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Livewire/PortalGuru/GuruWali/JurnalKelompokForm.php) (`/portal-guru/guru-wali/jurnal`).
- **Ruang Aman Cerita / Konsultasi Dua Arah**: [`KonsultasiList.php`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Livewire/PortalGuru/GuruWali/KonsultasiList.php) (`/portal-guru/guru-wali/konsultasi`) dan sisi siswa [`KonsultasiGuruWaliList.php`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Livewire/PortalSiswa/KonsultasiGuruWaliList.php) (`/portal-siswa/konsultasi-guru-wali`).
- **Pemantauan Kepatuhan Bulanan**: [`PemantauanBulanan.php`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Livewire/PortalGuru/GuruWali/PemantauanBulanan.php) (`/portal-guru/guru-wali/pemantauan`).
- **Cetak Laporan PDF Resmi**: [`CetakLaporanGuruWali.php`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Livewire/PortalGuru/GuruWali/CetakLaporanGuruWali.php) & [`GuruWaliCetakController.php`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Http/Controllers/GuruWaliCetakController.php) (`/portal-guru/guru-wali/cetak`).
- **Sistem Notifikasi FCM Otomatis**: Integrasi Push Notification langsung ke HP Android Guru Wali saat siswa mengirimkan permohonan bimbingan atau pesan cerita baru.

---

## 1. Landasan Regulasi & Filosofi Guru Wali (Permendikdasmen No. 11/2025)

Program **Guru Wali (SI-WALI)** bukan sekadar pencatatan administratif, melainkan pilar utama pembangunan kedekatan emosional dan pembinaan karakter murid secara berkelanjutan:

```
┌────────────────────────────────────────────────────────────────────────┐
│             PERBEDAAN FUNDAMENTAL WALI KELAS VS GURU WALI              │
├──────────────────────────┬─────────────────────────────────────────────┤
│ ASPEK                    │ WALI KELAS (Rombel Akademik)                │
├──────────────────────────┼─────────────────────────────────────────────┤
│ Ruang Lingkup            │ 1 Kelas / Rombel Fisik (contoh: Kelas 7A)   │
│ Masa Tugas               │ 1 Tahun Ajaran (berubah saat naik kelas)    │
│ Fokus Utama              │ Administrasi akademik, rapor, absensi kelas │
│ Jumlah Siswa             │ 30–36 siswa dalam 1 rombel                  │
├──────────────────────────┼─────────────────────────────────────────────┤
│ ASPEK                    │ GURU WALI (Kelompok Dampingan Karakter)     │
├──────────────────────────┼─────────────────────────────────────────────┤
│ Ruang Lingkup            │ 1 Kelompok Asuh Khusus (10–25 anak asuh)    │
│ Masa Tugas               │ Multi-Tahun (sejak masuk hingga lulus)      │
│ Fokus Utama              │ Karakter, kedisiplinan, sosial, minat bakat │
│ Beban Kerja Resmi        │ Ekuivalensi 2 JP / Minggu (Kepmen 221/P/25) │
│ Indikator Minimum        │ Setiap anak asuh wajib didampingi min. 1x/bln│
│ Tingkat Privasi          │ Strict Confidential (hanya Guru & Siswa)    │
└──────────────────────────┴─────────────────────────────────────────────┘
```

### 4 Pilar Pendampingan Karakter
1. **Akademik**: Mengatasi kendala belajar, gaya belajar efektif, motivasi asesmen.
2. **Karakter & Kedisiplinan**: Pembiasaan ibadah, akhlak mulia, kepatuhan tata tertib sekolah.
3. **Minat & Bakat / Ekskul**: Pengembangan potensi ekstrakurikuler, hobi, dan persiapan kompetisi.
4. **Sosial & Psikologis**: Hubungan pertemanan, adaptasi lingkungan, deteksi stres/kendala personal.

---

## 2. Solusi Desain UX Mobile: Single Command Center (Layar Utama Terpadu)

Di portal web terdapat 5 sub-menu terpisah (`/kelompok`, `/jurnal`, `/konsultasi`, `/pemantauan`, `/cetak`). Jika pola ini dipaksakan ke layar smartphone berukuran 6 inci, guru akan mengalami kelelahan navigasi (bolak-balik menu).

Oleh karena itu, di mobile Android seluruh fungsi dipadatkan menjadi **1 Layar Utama Terpadu (Single Command Center)** dengan model **Kotak-kotak (Grid Cards)** yang modern, responsif, dan padat informasi:

```
┌──────────────────────────────────────────────────────────────┐
│  ←  SI-WALI: Kelompok Asuh Bpk. Sutrisno               🔄    │
│     Pembimbing: Sutrisno, S.Pd. • 2 JP/Minggu                │
├──────────────────────────────────────────────────────────────┤
│  [ HERO SUMMARY CARDS ]                                      │
│  ┌──────────────┬──────────────┬──────────────┬───────────┐  │
│  │ Siswa Asuh   │ Dampingan Bln│ Kepatuhan    │ Cerita Blm│  │
│  │ 16 Siswa     │ 12 / 16 Anak │ 75% 🟢       │ 2 Menunggu│  │
│  └──────────────┴──────────────┴──────────────┴───────────┘  │
├──────────────────────────────────────────────────────────────┤
│  [ ACTION BAR CEPAT ]                                        │
│  [ + Bimbingan Klasikal ] [ 📊 Pemantauan ] [ 🖨️ Cetak PDF ] │
├──────────────────────────────────────────────────────────────┤
│  [ TABS: (1) Siswa Asuh (Grid) | (2) Jurnal | (3) Ruang Aman]│
├──────────────────────────────────────────────────────────────┤
│  [ TAB 1: MODEL KOTAK-KOTAK SISWA ASUH (GRID 2 KOLOM) ]      │
│                                                              │
│  ┌────────────────────────┐    ┌────────────────────────┐    │
│  │ 🟢 [Foto] AHMAD FAUZI  │    │ 🟡 [Foto] DEWI LESTARI │    │
│  │ NISN: 31234567 • 7A    │    │ NISN: 31234568 • 7B    │    │
│  │ Status: Sudah (2x)     │    │ Status: Belum Dampingan│    │
│  │ Terakhir: 24 Okt       │    │ Terakhir: -            │    │
│  │ Pilar: Karakter        │    │ Pilar: -               │    │
│  │ ────────────────────── │    │ ────────────────────── │    │
│  │ [+ Jurnal]  [Riwayat]  │    │ [+ Jurnal]  [Riwayat]  │    │
│  └────────────────────────┘    └────────────────────────┘    │
│  ┌────────────────────────┐    ┌────────────────────────┐    │
│  │ 🟢 [Foto] RIZKY PRATAMA│    │ 💬 [Foto] SITI NURHAL  │    │
│  │ NISN: 31234569 • 7A    │    │ NISN: 31234570 • 7C    │    │
│  │ Status: Sudah (1x)     │    │ Ada Pesan Cerita Baru! │    │
│  │ [+ Jurnal]  [Riwayat]  │    │ [Balas Pesan] [Jurnal] │    │
│  └────────────────────────┘    └────────────────────────┘    │
└──────────────────────────────────────────────────────────────┘
```

### Anatomi Komponen Layar:
1. **Hero Header Card**:
   - Menampilkan Nama Kelompok Binaan, Nama Guru Wali, dan Label Ekuivalensi 2 JP/Minggu.
   - Ringkasan Metrik 4 Kotak:
     - Total Siswa Asuh Aktif.
     - Siswa yang sudah didampingi bulan berjalan (misal: 12 / 16).
     - Persentase kepatuhan indikator minimum (indikator warna: Hijau $\ge 100\%$, Kuning $50-99\%$, Merah $< 50\%$).
     - Badge merah pengajuan konsultasi siswa yang menunggu respon.
2. **Action Bar Cepat**:
   - **`+ Bimbingan Klasikal`**: Tombol satu klik untuk membuka dialog pengisian jurnal kelompok sekaligus untuk seluruh atau sebagian siswa binaan (seperti bimbingan etika kelas bersama).
   - **`📊 Pemantauan Progres`**: Menampilkan dialog/bottomsheet matriks kedisiplinan dan checklist bimbingan bulanan (Juli–Juni).
   - **`🖨️ Cetak / Unduh PDF`**: Memilih format cetak rekap bimbingan resmi untuk diunduh langsung ke storage HP.
3. **Tab Switcher Segmented Control**:
   - **Tab 1: Siswa Asuh (Grid Kotak-Kotak)**:
     - Kartu siswa 2 kolom dengan foto profil, ring indikator status (🟢 Hijau = sudah bimbingan bulan ini, 🟡 Kuning = belum bimbingan), nama, NISN, dan kelas rombel.
     - Informasi tanggal bimbingan terakhir dan pilar yang dibahas.
     - Aksi langsung per kartu: tombol **`+ Jurnal Individu`** (langsung membuka form dengan data siswa terkunci) dan **`Riwayat`** (melihat buku catatan siswa tersebut).
   - **Tab 2: Riwayat Jurnal**:
     - Timeline daftar seluruh jurnal bimbingan (baik individu maupun klasikal).
     - Filter cepat berdasarkan 4 Pilar dan Status Sesi (Tuntas, Dalam Pemantauan, Bimbingan Lanjutan).
     - Badge sentimen bimbingan (Positif, Netral, Perlu Perhatian) serta Badge Karakter yang dihadiahkan.
     - Tombol eskalasi cepat: **"Rujuk ke Guru BK"** jika siswa mengalami permasalahan berat.
   - **Tab 3: Ruang Aman Cerita (Konsultasi Siswa)**:
     - Menampilkan daftar permohonan bimbingan dan obrolan pesan dari murid.
     - Tab filter: *Menunggu Konfirmasi*, *Dijadwalkan*, *Selesai*, *Ditolak*.
     - Antarmuka Chat Percakapan Terpadu (guru dan siswa berbalas pesan secara privat).
     - Fitur unggulan: **"Konversi ke Jurnal (+1 Klik Auto-Fill)"** yang menyalin seluruh percakapan ke jurnal resmi.

---

## 3. Sistem Push Notification FCM Realtime untuk Ruang Aman Cerita

Untuk mewujudkan mandat pengguna: *"jika siswa kirim pesan maka akan keluar notifikasi di hp android guru yang bersangkutan"*, dibangun arsitektur notifikasi realtime berbasis Firebase Cloud Messaging (FCM):

```
┌────────────────────────────────────────────────────────────────────────┐
│            ALUR PUSH NOTIFIKASI FCM RUANG AMAN CERITA SISWA            │
└────────────────────────────────────────────────────────────────────────┘

   [ Siswa Buka Portal Siswa (/portal-siswa/konsultasi-guru-wali) ]
                               │
               ┌───────────────┴───────────────┐
               ▼                               ▼
       [ Ajukan Konsultasi Baru ]      [ Kirim Pesan Obrolan Baru ]
       (kategori, topik, detail)       (teks obrolan lanjutan)
               │                               │
               └───────────────┬───────────────┘
                               │
                               ▼
     [ Livewire KonsultasiGuruWaliList: simpan() / kirimPesan() ]
                               │
                               ▼
        [ Dispatch Job: SendKonsultasiGuruWaliFcmNotificationJob ]
                               │
                               ▼
             [ Cek Tabel `fcm_device_tokens` Guru Wali ]
         (Cari device token Android aktif milik user_id guru)
                               │
                               ▼
         [ Kirim Payload ke Endpoint Google FCM HTTP v1 / Send ]
                               │
           ┌───────────────────┴───────────────────┐
           ▼                                       ▼
     [ HP Guru Dalam Keadaan Standby ]     [ Guru Sedang Buka Aplikasi ]
     Push Notif di Status Bar Android      In-App Notification Banner
     "💬 Cerita Baru: [Nama Siswa] [7A]"    "Siswa mengirim pesan baru"
     "Pak, saya mau curhat soal..."        (+ Suara & Getar)
           │                                       │
           └───────────────────┬───────────────────┘
                               │ Guru Klik Notifikasi
                               ▼
         [ Deep-Link: Buka Menu 5 Guru Wali (Tab Ruang Aman) ]
           (Langsung menampilkan obrolan dengan siswa terkait)
```

### Spesifikasi Payload Notifikasi FCM:
```json
{
  "registration_ids": ["TOKEN_FCM_GURU_1", "TOKEN_FCM_GURU_2"],
  "notification": {
    "title": "💬 Cerita Baru dari Ahmad Fauzi (Kelas 7A)",
    "body": "Pak, saya ingin berkonsultasi mengenai persiapan ujian matematika...",
    "sound": "default",
    "android_channel_id": "konsultasi_guru_wali_channel"
  },
  "data": {
    "type": "konsultasi_guru_wali",
    "konsultasi_id": "019f...uuid",
    "student_id": "019f...uuid",
    "student_name": "Ahmad Fauzi",
    "class_name": "Kelas 7A",
    "click_action": "FLUTTER_NOTIFICATION_CLICK"
  }
}
```

---

## 4. Desain Kontrak REST API Backend (`/api/v1/guru/guru-wali/*`)

Untuk menjaga kebersihan kode (*clean code* & *Single Responsibility Principle*) agar tidak menumpuk dalam 1 file besar, backend dibagi menjadi **4 Sub-Controller Modular** di dalam namespace [`App\Http\Controllers\Api\Mobile\GuruWali`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Http/Controllers/Api/Mobile/GuruWali):
1. [`GuruWaliBaseController.php`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Http/Controllers/Api/Mobile/GuruWali/GuruWaliBaseController.php): Logika resolusi autentikasi guru, kelompok aktif, dan tahun ajaran.
2. [`GuruWaliDashboardController.php`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Http/Controllers/Api/Mobile/GuruWali/GuruWaliDashboardController.php): Dashboard, statistik metrik, dan grid kotak siswa asuh aktif/arsip.
3. [`GuruWaliJurnalController.php`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Http/Controllers/Api/Mobile/GuruWali/GuruWaliJurnalController.php): CRUD jurnal individu, jurnal massal klasikal, dan eskalasi rujuk BK.
4. [`GuruWaliKonsultasiController.php`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Http/Controllers/Api/Mobile/GuruWali/GuruWaliKonsultasiController.php): Manajemen tiket ruang aman cerita, obrolan chat dua arah, jadwal tatap muka, dan konversi ke jurnal.
5. [`GuruWaliLaporanController.php`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Http/Controllers/Api/Mobile/GuruWali/GuruWaliLaporanController.php): Matriks pemantauan 12 bulan (Juli–Juni) dan streaming cetak PDF resmi.

### 1. `GET /api/v1/guru/guru-wali/dashboard`
Mengambil data utama command center:
- Objek Kelompok Binaan (ID, Nama Kelompok, Status Aktif).
- Ringkasan Statistik: Total Siswa, Didampingi Bulan Ini, Persentase Kepatuhan, Jumlah Konsultasi Menunggu.
- List Siswa Asuh Aktif:
  - `student_id`, `name`, `nisn`, `kelas_name`, `avatar_url`.
  - `has_bimbingan_bulan_ini` (boolean).
  - `bimbingan_count_bulan_ini` (int).
  - `last_bimbingan_at`, `last_kategori`, `last_status`.
  - `unread_konsultasi_count` (int).

### 2. `GET /api/v1/guru/guru-wali/jurnal`
Mengambil riwayat jurnal bimbingan:
- Parameter: `page`, `search`, `kategori`, `jenis_pendampingan`, `student_id`, `bulan`, `tahun`.
- Return: Paginated list jurnal dengan relasi `siswa`, `badgeKarakter`, dan `konselingBk`.

### 3. `POST /api/v1/guru/guru-wali/jurnal/individual`
Menyimpan jurnal bimbingan perorangan (1 siswa):
- Payload: `student_id`, `tanggal_waktu`, `jenis_pendampingan` ('Individu'), `kategori_pendampingan` (4 pilar), `uraian_pembahasan`, `status_sesi`, `rujukan_kolaborasi`, `rencana_tindak_lanjut`, `kategori_sentimen`, `is_public_note`, `give_badge` (opsional), `nama_badge`, `catatan_apresiasi`.

### 4. `POST /api/v1/guru/guru-wali/jurnal/klasikal`
Menyimpan bimbingan kelompok massal satu kelompok:
- Payload: `tanggal_waktu`, `jenis_pendampingan` ('Klasikal' / 'Kelompok Kecil'), `kategori_pendampingan`, `uraian_pembahasan`, `rujukan_kolaborasi`, `rencana_tindak_lanjut`, `is_public_note`, `student_ids` (array ID siswa yang hadir dalam sesi), `status_sesi` default.

### 5. `POST /api/v1/guru/guru-wali/jurnal/{id}/rujuk-bk`
Eskalasi masalah dari catatan jurnal ke modul Bimbingan Konseling (BK):
- Payload: `alasan_rujukan`, `urgensi` (rendah, sedang, darurat).
- Action: Membuat entitas `konseling_bk` baru dengan `rujukan_dari_jurnal_id = $id`.

### 6. `GET /api/v1/guru/guru-wali/konsultasi`
Mengambil tiket ruang aman cerita:
- Parameter: `status` ('menunggu', 'dijadwalkan', 'selesai', 'ditolak', 'semua').
- Return: List permohonan bimbingan siswa beserta jumlah pesan obrolan dan status unread.

### 7. `GET /api/v1/guru/guru-wali/konsultasi/{id}`
Mengambil detail tiket konsultasi beserta seluruh untaian chat obrolan (`pesan_konsultasi_guru_wali`). Menandai seluruh pesan siswa sebagai `is_read = true`.

### 8. `POST /api/v1/guru/guru-wali/konsultasi/{id}/pesan`
Mengirimkan pesan balasan obrolan dari guru ke siswa:
- Payload: `pesan` (string min: 2, max: 2000).
- Action: Simpan record `PesanKonsultasiGuruWali` dengan `sender_type = 'guru'`.

### 9. `POST /api/v1/guru/guru-wali/konsultasi/{id}/jadwalkan`
Menetapkan jadwal pasti pertemuan tatap muka di sekolah:
- Payload: `jadwal_pasti` (datetime), `tanggapan_guru` (string).

### 10. `POST /api/v1/guru/guru-wali/konsultasi/{id}/konversi-jurnal`
Satu klik konversi percakapan konsultasi menjadi jurnal resmi Guru Wali:
- Payload: `status_sesi`, `rujukan_kolaborasi`, `rencana_tindak_lanjut`.
- Action: Insert `jurnal_guru_wali` terisi otomatis dan update status konsultasi menjadi `Dikonversi ke Jurnal`.

### 11. `GET /api/v1/guru/guru-wali/pemantauan`
Mengambil matriks evaluasi bulanan 12 bulan (Juli–Juni) dan distribusi persentase 4 pilar bimbingan.

### 12. `GET /api/v1/guru/guru-wali/cetak/pdf-stream`
Menghasilkan binary file PDF laporan rekap resmi untuk diunduh langsung di aplikasi Flutter.

---

## 5. Rencana Arsitektur Frontend Flutter (`apk-android-presensi`)

### A. Struktur Berkas (Feature-First)
```
lib/features/guru/
├── models/
│   ├── guru_wali_dashboard_model.dart  // Info kelompok, statistik, & list siswa kotak-kotak
│   ├── jurnal_guru_wali_model.dart     // Jurnal individu & klasikal
│   ├── konsultasi_guru_wali_model.dart // Tiket konsultasi & riwayat pesan chat
│   └── pemantauan_bulanan_model.dart   // Matriks pemantauan 12 bulan
├── services/
│   └── guru_wali_service.dart          // Dio HTTP service & error handler
└── screens/
    ├── guru_wali_screen.dart           // Layar Utama Terpadu (Command Center)
    └── widgets/
        ├── guru_wali_hero_card.dart    // Header info kelompok & 4 metric cards
        ├── siswa_asuh_grid.dart        // Model kotak-kotak 2 kolom kartu siswa
        ├── siswa_asuh_card.dart        // Kartu individu siswa dengan badge status
        ├── modal_jurnal_individu.dart  // Dialog catat jurnal bimbingan perorangan
        ├── modal_jurnal_klasikal.dart  // Dialog bimbingan massal 1 kelompok
        ├── tab_riwayat_jurnal.dart     // List timeline jurnal & filter 4 pilar
        ├── tab_ruang_aman_chat.dart    // List tiket cerita & obrolan dua arah
        ├── chat_konsultasi_sheet.dart  // Bottomsheet interaktif chat percakapan
        └── modal_pemantauan_sheet.dart // Bottomsheet matriks kepatuhan bulanan
```

### B. State Management & Realtime Polling
- State dikelola menggunakan `StatefulWidget` terpadu atau Riverpod StateNotifier.
- Pada Tab Ruang Aman Cerita: Saat chat sheet dibuka, dilakukan interval short-polling setiap 5 detik agar pesan baru dari siswa langsung muncul di layar tanpa reload manual.

---

## 6. Rencana 6 Tahap Eksekusi (Implementation Milestones)

| Tahap | Status | Fokus Pekerjaan | Deliverable Kunci |
| :-: | :-: | :--- | :--- |
| **Tahap 1** | **SELESAI ✅** | **Backend API & Service Guru Wali** | - Memecah controller modular: [`GuruWaliBaseController.php`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Http/Controllers/Api/Mobile/GuruWali/GuruWaliBaseController.php), [`GuruWaliDashboardController.php`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Http/Controllers/Api/Mobile/GuruWali/GuruWaliDashboardController.php), [`GuruWaliJurnalController.php`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Http/Controllers/Api/Mobile/GuruWali/GuruWaliJurnalController.php), [`GuruWaliKonsultasiController.php`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Http/Controllers/Api/Mobile/GuruWali/GuruWaliKonsultasiController.php), [`GuruWaliLaporanController.php`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Http/Controllers/Api/Mobile/GuruWali/GuruWaliLaporanController.php)<br>- Rute terdaftar di `routes/api.php` dan terverifikasi. |
| **Tahap 2** | **SELESAI ✅** | **FCM Push Notification Ruang Aman** | - Membuat [`SendKonsultasiGuruWaliFcmNotificationJob.php`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Jobs/SendKonsultasiGuruWaliFcmNotificationJob.php)<br>- Trigger dispatch di Livewire [`KonsultasiGuruWaliList.php`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Livewire/PortalSiswa/KonsultasiGuruWaliList.php) saat siswa submit konsultasi/pesan baru. |
| **Tahap 3** | **SELESAI ✅** | **Model Data & Service HTTP di Flutter** | - Model Dart: [`guru_wali_dashboard_model.dart`](file:///home/sutris-remote/Documents/projek-laravel/apk-android-presensi/lib/features/guru/models/guru_wali_dashboard_model.dart), [`jurnal_guru_wali_model.dart`](file:///home/sutris-remote/Documents/projek-laravel/apk-android-presensi/lib/features/guru/models/jurnal_guru_wali_model.dart), [`konsultasi_guru_wali_model.dart`](file:///home/sutris-remote/Documents/projek-laravel/apk-android-presensi/lib/features/guru/models/konsultasi_guru_wali_model.dart), [`pemantauan_bulanan_model.dart`](file:///home/sutris-remote/Documents/projek-laravel/apk-android-presensi/lib/features/guru/models/pemantauan_bulanan_model.dart)<br>- Service: [`guru_wali_service.dart`](file:///home/sutris-remote/Documents/projek-laravel/apk-android-presensi/lib/features/guru/services/guru_wali_service.dart). |
| **Tahap 4** | **SELESAI ✅** | **UI Command Center & Model Kotak-Kotak** | - Layar Utama Terpadu: [`guru_wali_screen.dart`](file:///home/sutris-remote/Documents/projek-laravel/apk-android-presensi/lib/features/guru/screens/guru_wali_screen.dart)<br>- Hero Header, Metric Cards Kepatuhan Permen 11/2025, Action Bar Cepat, Segmented Tab Switcher<br>- Grid Kotak-Kotak 2 Kolom Siswa Asuh dengan ring status 🟢/🟡<br>- Integrasi Menu 5 & In-app Alert Banner di [`dashboard_guru_screen.dart`](file:///home/sutris-remote/Documents/projek-laravel/apk-android-presensi/lib/features/guru/screens/dashboard_guru_screen.dart). |
| **Tahap 5** | **SELESAI ✅** | **Form Jurnal Individu, Klasikal, Pemantauan & Cetak PDF** | - Modal Bimbingan Individu: [`modal_jurnal_individu.dart`](file:///home/sutris-remote/Documents/projek-laravel/apk-android-presensi/lib/features/guru/screens/widgets/guru_wali/modal_jurnal_individu.dart) (+ Digital Badge Karakter & Rujuk BK)<br>- Modal Bimbingan Klasikal: [`modal_jurnal_klasikal.dart`](file:///home/sutris-remote/Documents/projek-laravel/apk-android-presensi/lib/features/guru/screens/widgets/guru_wali/modal_jurnal_klasikal.dart) (Checklist multi-select siswa)<br>- Modal Pemantauan 12 Bulan: [`modal_pemantauan_sheet.dart`](file:///home/sutris-remote/Documents/projek-laravel/apk-android-presensi/lib/features/guru/screens/widgets/guru_wali/modal_pemantauan_sheet.dart)<br>- Dialog Cetak PDF Resmi: [`dialog_cetak_pdf.dart`](file:///home/sutris-remote/Documents/projek-laravel/apk-android-presensi/lib/features/guru/screens/widgets/guru_wali/dialog_cetak_pdf.dart). |
| **Tahap 6** | **SELESAI ✅** | **Ruang Aman Cerita (Chat Interaktif & FCM Deep Link)** | - Chat UI interaktif dua arah: [`chat_konsultasi_sheet.dart`](file:///home/sutris-remote/Documents/projek-laravel/apk-android-presensi/lib/features/guru/screens/widgets/guru_wali/chat_konsultasi_sheet.dart)<br>- Realtime short-polling interval 5 detik untuk tangkapan pesan siswa otomatis<br>- Aksi: Jadwalkan Tatap Muka, 1-Klik Konversi ke Jurnal Resmi, Tolak Permohonan<br>- Integrasi tap kartu & badge obrolan siswa di model kotak-kotak<br>- Dukungan parameter deep-link `initialKonsultasiId` & `initialTabIndex` saat guru menekan notifikasi Android status bar. |

---

## 7. Kriteria Keberhasilan (Acceptance Criteria)

1. ✅ **Layar Terpadu (Single Command Center)**: Guru tidak perlu berpindah-pindah 5 menu; seluruh fungsi siswa asuh, jurnal, konsultasi, pemantauan, dan cetak dapat diakses dari 1 layar.
2. ✅ **Desain Kotak-Kotak Informatif**: Tampilan grid kartu siswa asuh menampilkan foto, nama, rombel, dan status keaktifan pendampingan bulan berjalan secara jelas dan estetis.
3. ✅ **Bimbingan Klasikal & Individu**: Guru dapat mencatat bimbingan untuk 1 siswa atau langsung serentak 1 kelompok bimbingan hanya dengan beberapa sentuhan.
4. ✅ **Push Notifikasi FCM**: Saat siswa mengirimkan cerita atau pesan di portal siswa, notifikasi langsung berdering/muncul di notification bar HP Android Guru Wali.
5. ✅ **Kerahasiaan Terjamin**: Hanya Guru Wali yang bersangkutan yang dapat melihat rekam jejak dan percakapan siswa asuhnya.

