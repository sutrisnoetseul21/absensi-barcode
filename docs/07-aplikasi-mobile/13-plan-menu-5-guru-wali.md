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

Untuk mendukung antarmuka mobile, dibuat controller baru:
[`GuruWaliApiController.php`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Http/Controllers/Api/Mobile/GuruWaliApiController.php) dengan middleware `auth:sanctum`:

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

| Tahap | Fokus Pekerjaan | Deliverable Kunci |
| :-: | :--- | :--- |
| **Tahap 1** | **Backend API & Service Guru Wali** | - Membuat `GuruWaliApiController.php`<br>- Mendaftarkan rute API di `routes/api.php`<br>- Unit testing endpoint dashboard, kelompok, & jurnal. |
| **Tahap 2** | **FCM Push Notification Ruang Aman** | - Membuat `SendKonsultasiGuruWaliFcmNotificationJob.php`<br>- Menambahkan trigger pengiriman di Livewire `KonsultasiGuruWaliList.php`<br>- Verifikasi payload FCM ke token HP guru. |
| **Tahap 3** | **Model Data & Service HTTP di Flutter** | - Membuat file model Dart (`guru_wali_dashboard_model.dart`, dsb)<br>- Membuat `GuruWaliService` menggunakan Dio client. |
| **Tahap 4** | **UI Command Center & Model Kotak-Kotak** | - Membuat `guru_wali_screen.dart`<br>- Implementasi Hero Header & Metric Cards<br>- Implementasi Grid Kotak-Kotak Siswa Asuh 2 Kolom dengan badge 🟢/🟡<br>- Sambungkan navigasi dari Menu 5 Dashboard Guru. |
| **Tahap 5** | **Form Jurnal Individu, Klasikal & Cetak PDF** | - Implementasi `ModalJurnalIndividu` (+ Badge Karakter)<br>- Implementasi `ModalJurnalKlasikal` (Checklist massal)<br>- Integrasi unduh & buka PDF laporan resmi. |
| **Tahap 6** | **Ruang Aman Cerita (Chat Interaktif & FCM Deep Link)** | - Implementasi `tab_ruang_aman_chat.dart`<br>- Implementasi `chat_konsultasi_sheet.dart` (Chat UI dua arah)<br>- Integrasi handler FCM klik notifikasi langsung buka chat<br>- Pengujian menyeluruh & sinkronisasi dokumentasi. |

---

## 7. Kriteria Keberhasilan (Acceptance Criteria)

1. ✅ **Layar Terpadu (Single Command Center)**: Guru tidak perlu berpindah-pindah 5 menu; seluruh fungsi siswa asuh, jurnal, konsultasi, pemantauan, dan cetak dapat diakses dari 1 layar.
2. ✅ **Desain Kotak-Kotak Informatif**: Tampilan grid kartu siswa asuh menampilkan foto, nama, rombel, dan status keaktifan pendampingan bulan berjalan secara jelas dan estetis.
3. ✅ **Bimbingan Klasikal & Individu**: Guru dapat mencatat bimbingan untuk 1 siswa atau langsung serentak 1 kelompok bimbingan hanya dengan beberapa sentuhan.
4. ✅ **Push Notifikasi FCM**: Saat siswa mengirimkan cerita atau pesan di portal siswa, notifikasi langsung berdering/muncul di notification bar HP Android Guru Wali.
5. ✅ **Kerahasiaan Terjamin**: Hanya Guru Wali yang bersangkutan yang dapat melihat rekam jejak dan percakapan siswa asuhnya.

