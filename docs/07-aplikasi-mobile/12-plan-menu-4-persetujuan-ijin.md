# 📋 Plan Matang Tahap 4 (Menu 4): Persetujuan Ijin (`/portal-guru/ijin-kehadiran`)

Dokumen ini merupakan **Blueprint Teknis & Panduan Implementasi Komprehensif** untuk **Menu 4: Persetujuan Ijin** pada aplikasi mobile Flutter (`apk-android-presensi`), yang diselaraskan 100% dengan aturan bisnis web Livewire & Filament backend:
- **Daftar Permohonan Izin Guru**: [`IjinKehadiranList.php`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Livewire/PortalGuru/IjinKehadiranList.php) & [`ijin-kehadiran-list.blade.php`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/resources/views/livewire/portal-guru/ijin-kehadiran-list.blade.php) (`/portal-guru/ijin-kehadiran`).
- **Detail & Tindakan Verifikasi Guru**: [`IjinKehadiranDetail.php`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Livewire/PortalGuru/IjinKehadiranDetail.php) & [`ijin-kehadiran-detail.blade.php`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/resources/views/livewire/portal-guru/ijin-kehadiran-detail.blade.php) (`/portal-guru/ijin-kehadiran/{id}`).
- **Form Pengajuan Izin Siswa**: [`IjinKehadiranForm.php`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Livewire/PortalSiswa/IjinKehadiranForm.php) (`/portal-siswa/ijin-kehadiran/form`).
- **Otomatisasi Presensi**: [`LeaveRequestService.php`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Services/LeaveRequestService.php) (`syncAttendances` & `removeAttendances`).
- **Gateway Notifikasi WhatsApp & Audit Log**:
  - Konfigurasi Template: `/admin/manajemen-notifikasi-wa` ([`ManajemenNotifikasiWaPage.php`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Filament/Presensi/Pages/ManajemenNotifikasiWaPage.php) & [`PresensiNotificationSetting.php`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Models/PresensiNotificationSetting.php)).
  - Audit Trail Pengiriman: `/admin/whats-app-notification-logs` ([`WhatsAppNotificationLogResource.php`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Filament/Presensi/Resources/WhatsAppNotificationLogResource.php) & [`WhatsAppNotificationLog.php`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Models/WhatsAppNotificationLog.php)).
  - Antrean Pengiriman: [`SendLeaveRequestWaNotificationJob.php`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Jobs/SendLeaveRequestWaNotificationJob.php) & [`SendWhatsAppNotificationJob.php`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Jobs/SendWhatsAppNotificationJob.php).
- **Fitur Baru**: **Sistem Notifikasi Aplikasi Android** (In-App Live Realtime Banner & Push Notification ke Device Android).

---

## 1. Bedah Logika Bisnis & Fitur Inti (Core Business Logic)

```
┌────────────────────────────────────────────────────────────────────────┐
│             ALUR KERJA PENGAJUAN & PERSETUJUAN IZIN SISWA             │
└────────────────────────────────────────────────────────────────────────┘

    [ Siswa / Orang Tua ]
              │
              ▼ Input Form Izin / Sakit + Upload Surat (Max 5 File)
    [ LeaveRequest (Status: pending) ]
              │
              ├──► 1. Trigger Job WA Gateway ──► Kirim WA ke Wali Kelas
              │                                 (+ Link Approval Cepat)
              │
              └──► 2. FITUR BARU: Push Notif ──► Kirim ke Android Wali Kelas
                                                 (Device Status Bar & In-App)
              │
              ▼
    [ Wali Kelas / Guru BK Buka Menu 4 ]
              │
              ├─── Guru BK: Mode Pantau (Read-only, tidak ada tombol putusan)
              │
              └─── Wali Kelas: Memiliki Hak Putusan (Approve / Reject / Reset)
                        │
         ┌──────────────┴──────────────┐
         ▼                             ▼
    [ SETUJUI (Approve) ]         [ TOLAK (Reject) ]
         │                             │
         ├──► Update Status 'approved' ├──► Wajib isi alasan tolak (min 3 chr)
         ├──► Catat Log Persetujuan    ├──► Update Status 'rejected'
         ├──► LeaveRequestService:     ├──► Jika dulunya approved: cabut presensi
         │    Auto-generate presensi   │    (LeaveRequestService::removeAttendances)
         │    di tabel `attendances`   ├──► Catat Log Penolakan
         └──► Kirim WA ke Siswa/Ortu   └──► Kirim WA ke Siswa/Ortu (+ Alasan)
              "Ijin Anda Disetujui"         "Ijin Anda Ditolak: ..."
```

### A. Hak Akses & Pembagian Wewenang (Wali Kelas vs Guru BK)
1. **Wali Kelas Resmi**:
   - Memiliki wewenang mutlak atas permohonan izin siswa di kelas rombel binaannya (`kelasAjarans`).
   - Dapat melihat daftar pengajuan, membuka detail lengkap, memeriksa lampiran foto/PDF surat dokter, dan **melakukan aksi putusan**:
     - **Setujui (`approve`)**
     - **Tolak (`reject`)** (wajib mencantumkan alasan)
     - **Kembalikan ke Pending (`resetToPending`)** (jika terjadi kekeliruan)
2. **Guru Bimbingan Konseling (Guru BK)**:
   - Mengacu pada `/admin/manajemen-akses-portal/{id}/edit`, Guru BK memiliki hak pantau pada kelas binaan BK (`kelasPantau`).
   - Guru BK **dapat melihat dan membaca** seluruh pengajuan izin siswa di kelas binaan BK miliknya untuk keperluan bimbingan konseling dan pemantauan tingkat kerentanan absensi siswa.
   - **Tombol aksi putusan (Setujui / Tolak) dinonaktifkan (Disabled)** dengan pemberitahuan informatif:
     > *"Anda login sebagai Guru BK. Anda memiliki akses memantau izin kelas ini, namun wewenang persetujuan resmi berada pada Wali Kelas."*
3. **Super Admin / Admin Presensi**:
   - Memiliki akses *Bypass* penuh untuk menyetujui atau menolak permohonan siswa di seluruh rombel sekolah.

### B. Otomatisasi Presensi Saat Persetujuan (`LeaveRequestService::syncAttendances`)
Ketika Wali Kelas menekan tombol **Setujui**:
1. Status `leave_requests` berubah menjadi `'approved'`, mencatat `approved_by = $userId`, `approved_by_type = App\Models\User`, dan `approved_at = now()`.
2. Sistem mencatat riwayat ke tabel `leave_request_logs` (`recordLog('approved', "Disetujui oleh {$guru->name}")`).
3. Sistem memanggil `LeaveRequestService::syncAttendances($leaveRequest)` yang menjalankan logika:
   - Mengiterasi setiap hari dari `start_date` sampai `end_date`.
   - Mengecek kalender akademik melalui `KalenderSekolahService::isHariSekolah($currentDate, $classId)`:
     - Jika hari efektif KBM: status presensi dicatat sebagai **`izin`** (jika type `'ijin'`) atau **`sakit`** (jika type `'sakit'`).
     - Jika hari libur / akhir pekan (Minggu/Sabtu): status presensi otomatis dicatat sebagai **`libur`**.
   - **Perlindungan Terhadap Data Presensi Fisik Gerbang**: Jika pada hari tersebut siswa sudah tercatat hadir fisik di mesin barcode gerbang (`hadir` atau `telat`), maka tanggal tersebut **DILONWATI / TIDAK DITIMPA**.
   - Presensi yang dibuat otomatis ditandai: `is_manual_input = true`, `leave_request_id = $record->id`, `manual_input_by_id = $guru->id`.

### C. Pembatalan Presensi Saat Penolakan / Reset (`LeaveRequestService::removeAttendances`)
1. **Penolakan (`reject`)**:
   - Wali kelas **wajib mengisi alasan penolakan** (`reason`, minimal 3 karakter).
   - Status permohonan berubah menjadi `'rejected'`.
   - Jika permohonan tersebut sebelumnya sudah berstatus `'approved'`, sistem otomatis menghapus seluruh record presensi harian yang memiliki referensi `leave_request_id = $record->id`.
2. **Pengembalian ke Pending (`resetToPending`)**:
   - Jika guru salah menekan tombol setujui/tolak, permohonan dapat dikembalikan ke status `'pending'`.
   - Record presensi otomatis dicabut kembali sampai ada keputusan final.

---

## 2. Arsitektur Notifikasi Ganda (WhatsApp & Notifikasi Android)

Integrasi notifikasi pada Menu 4 dirancang dalam **Dua Sayap Komunikasi** yang berjalan beriringan:

```
                  ┌───────────────────────────────┐
                  │ SISWA AJUKAN PERMOHONAN IZIN │
                  └───────────────┬───────────────┘
                                  │
         ┌────────────────────────┴────────────────────────┐
         ▼                                                 ▼
┌─────────────────────────────────┐       ┌─────────────────────────────────┐
│     SAYAP 1: WHATSAPP GATEWAY   │       │    SAYAP 2: NOTIFIKASI ANDROID  │
├─────────────────────────────────┤       ├─────────────────────────────────┤
│ • PresensiNotificationSetting   │       │ • In-App Live Badge (Realtime)  │
│   (template: 'leave_request')   │       │ • In-App Floating Notification  │
│ • Link One-Click Konfirmasi:    │       │ • Device Push Notification      │
│   /ijin-approval/{token}        │       │   (FCM Device Token API)        │
│ • Lampirkan Gambar Surat ke WA  │       │ • Status Bar Alert:             │
│ • Log: whatsapp_notification_log│       │   "Izin Baru: Ahmad (7A)"       │
└─────────────────────────────────┘       └─────────────────────────────────┘
```

### A. Sayap 1: WhatsApp Gateway & Audit Log
1. **Notifikasi ke Wali Kelas (Saat Pengajuan Masuk)**:
   - Dikelola oleh `SendLeaveRequestWaNotificationJob` dengan toleransi timeout 120 detik.
   - Mengambil template `leave_request` dari tabel `presensi_notification_settings`.
   - Variabel dinamis: `{nama_siswa}`, `{kelas}`, `{jenis_ijin}`, `{tanggal_mulai}`, `{tanggal_selesai}`, `{alasan}`, `{nama_wali_kelas}`, `{link_konfirmasi}`.
   - Job secara otomatis melampirkan media foto surat izin/dokter ke chat WhatsApp wali kelas.
   - Hasil pengiriman (sukses/gagal/respons gateway) dicatat ke tabel `whatsapp_notification_logs`.
2. **Notifikasi ke Siswa / Orang Tua (Saat Disetujui atau Ditolak)**:
   - Mengambil template `leave_approval` dari tabel `presensi_notification_settings`.
   - Variabel dinamis: `{nama_siswa}`, `{jenis_ijin}`, `{tanggal_mulai}`, `{tanggal_selesai}`, `{status_persetujuan}` (DISETUJUI / DITOLAK), `{nama_guru}`, `{alasan_penolakan}`.
   - Pesan dikirim otomatis ke nomor HP siswa dan nomor WhatsApp orang tua yang tersimpan di sistem.

### B. Sayap 2: Notifikasi Aplikasi Android (Fitur Baru)
Pengguna meminta adanya notifikasi langsung di aplikasi Android agar guru tidak hanya bergantung pada WhatsApp:

1. **Tingkat 1: Real-time In-App Notification & Live Counter**:
   - Di Beranda Guru (`DashboardGuruScreen`), kotak menu ke-4 memiliki badge dinamis:
     - Jika ada izin pending: **Badge Merah Menyala** (`3 Menunggu`).
     - Jika tidak ada: **Badge Netral** (`Bersih`).
   - Ketika guru membuka aplikasi, sistem memeriksa endpoint `GET /api/v1/guru/dashboard-summary` atau `GET /api/v1/guru/ijin-kehadiran/notification-check`.
   - Jika terdapat permohonan pending baru sejak sesi terakhir, aplikasi memunculkan **Floating In-App Notification Banner** di bagian atas layar:
     > *"🔔 Permohonan Baru: Ahmad Albar (Kelas 7A) mengajukan Sakit (2 hari). Ketuk untuk meninjau."*
   - Mengetuk banner langsung membuka modal detail izin siswa yang bersangkutan.

2. **Tingkat 2: System-Level Push Notification ke Perangkat Android (FCM & Device Tokens)**:
   - **Manajemen Device Token**:
     - Tabel database: `fcm_device_tokens` (`user_id`, `token`, `platform`, `device_name`, `last_used_at`).
     - Saat guru login di Android, aplikasi mendaftarkan FCM Device Token ke endpoint `POST /api/v1/auth/fcm-token`.
     - Saat guru logout, token dihapus via `DELETE /api/v1/auth/fcm-token` demi keamanan data murid.
   - **Pemicuan Notifikasi Push**:
     - Ketika siswa mengirim form izin (baik via web Portal Siswa maupun mobile), Laravel men-dispatch job `SendLeaveRequestFcmNotificationJob`.
     - Job mencari seluruh token aktif milik Wali Kelas siswa tersebut.
     - Payload Notifikasi:
       ```json
       {
         "notification": {
           "title": "📋 Permohonan Izin Baru: Ahmad Albar (7A)",
           "body": "Sakit (2 hari): Demam tinggi dan flu berat. Mohon ditinjau."
         },
         "data": {
           "type": "leave_request",
           "leave_request_id": "9d123456-7890-...",
           "click_action": "FLUTTER_NOTIFICATION_CLICK"
         }
       }
       ```
   - **Penanganan di Flutter**:
     - Mengetuk notifikasi di Status Bar Android akan meluncurkan aplikasi dan langsung mengarahkan navigator ke layar detail permohonan izin terkait.

---

## 3. Rencana Desain UI/UX Mobile Flutter (Bagus & Kekinian)

### 🎨 A. Prinsip Desain Mobile Modern:
1. **Segmented Tabs dengan Status Counters**:
   - Empat tab penyaring cepat di bagian atas:
     - `Semua` (Menampilkan seluruh riwayat permohonan)
     - `Menunggu` (Menampilkan badge angka merah menyala, fokus utama verifikasi guru)
     - `Disetujui` (Aksen hijau emerald)
     - `Ditolak` (Aksen merah rose)
2. **Kartu Siswa Vertikal Kaya Informasi (Leave Request Card)**:
   - Foto avatar inisial berwarna cerah sesuai jenis kelamin siswa.
   - Nama lengkap siswa, NISN, dan badge rombel kelas (misal: `Kelas 7A`).
   - Label jenis permohonan yang kontras:
     - 🟢 **Izin** (Background Emerald lembut + teks hijau tua)
     - 🔴 **Sakit** (Background Rose lembut + teks merah tua)
   - Badge status verifikasi:
     - ⏳ `Menunggu Verifikasi` (Amber/Kuning hangat)
     - ✅ `Disetujui oleh [Nama Guru]` (Hijau Emerald)
     - ❌ `Ditolak: [Alasan]` (Merah Rose)
   - Rentang tanggal & total hari: `📅 29 Okt 2026 - 31 Okt 2026 (3 Hari)`.
   - Ringkasan alasan/keterangan yang diajukan siswa/orang tua.
   - Thumbnail lampiran surat: Pratinjau gambar mini dengan indikator jumlah berkas (`+2 Berkas`).
   - Tombol aksi cepat: **"Tinjau & Putuskan"**.

3. **Pratinjau Berkas & Lampiran Interaktif (Document & Image Viewer)**:
   - Guru dapat mengetuk thumbnail lampiran untuk membuka **Modal Pratinjau Penuh (Full Preview Modal)**:
     - Dukungan fitur *Pinch-to-zoom* (perbesar detail tanda tangan atau cap surat dokter).
     - Tombol unduh / simpan gambar ke galeri atau buka PDF eksternal.

4. **Bilah Aksi Keputusan Ergonomis (Action Sheet)**:
   - **Tombol Hijau (Setujui)**:
     - Menampilkan dialog konfirmasi ringkas yang memberitahukan bahwa presensi siswa akan otomatis disinkronkan ke tanggal KBM efektif.
   - **Tombol Merah (Tolak)**:
     - Membuka modal form input alasan penolakan (wajib diisi, validasi minimal 3 karakter) agar orang tua siswa mendapatkan transparansi penyebab izin ditolak.
   - **Tombol Abu-abu (Kembalikan ke Pending)**:
     - Tersedia jika status sudah disetujui atau ditolak untuk mengantisipasi kesalahan ketukan.

---

### 📱 B. Struktur Layar 1: Daftar Permohonan Izin (`IjinKehadiranScreen`)
```
┌────────────────────────────────────────────────────────┐
│ ‹ Persetujuan Ijin Siswa              [🔍] [Segarkan] │
├────────────────────────────────────────────────────────┤
│ 🔮 HERO BANNER KEKINIAN                                │
│    Persetujuan Izin & Sakit Siswa                      │
│    Tahun Ajaran: 2026/2027 Ganjil • Aktif             │
│    Wewenang: Wali Kelas 7A                            │
├────────────────────────────────────────────────────────┤
│ 📊 3 KARTU STATISTIK RINGKASAN (Horizontal Card Row)   │
│  ┌──────────────┐ ┌──────────────┐ ┌────────────────┐  │
│  │ ⏳ 3 Menunggu│ │ ✅ 18 Setuju │ │ ❌ 2 Ditolak   │  │
│  │ Butuh Respon │ │ Terverifikasi│ │ Tidak Sah      │  │
│  └──────────────┘ └──────────────┘ └────────────────┘  │
├────────────────────────────────────────────────────────┤
│ 🏷️ TAB PENYARING STATUS                                │
│  [ Semua ]  [ ⏳ Menunggu (3) ]  [ Disetujui ]  [ Ditolak ] │
├────────────────────────────────────────────────────────┤
│ 🔍 BAR PENCARIAN & FILTER KELAS / BULAN                │
│  [ Cari nama siswa atau NISN...                     ]  │
│  [ Kelas 7A ▾ ]                  [ Oktober 2026 ▾ ]    │
├────────────────────────────────────────────────────────┤
│ 📋 DAFTAR PERMOHONAN SISWA (ListView)                  │
│  ┌──────────────────────────────────────────────────┐  │
│  │ (AA) Ahmad Albar               [ 🔴 SAKIT ]      │  │
│  │      NISN: 0098765432 • Kelas 7A                 │  │
│  │                                                  │  │
│  │ 📅 29 Okt 2026 - 31 Okt 2026 (3 Hari)            │  │
│  │ 💬 "Demam tinggi sejak kemarin sore, dokter...   │  │
│  │                                                  │  │
│  │ 📎 Lampiran: [🖼️ SuratDokter.jpg] [🖼️ Resep.jpg] │  │
│  │                                                  │  │
│  │ ⏳ Menunggu Persetujuan Anda                     │  │
│  │ ──────────────────────────────────────────────── │  │
│  │                     [ 👁️ Tinjau & Putuskan ]    │  │
│  └──────────────────────────────────────────────────┘  │
│  ┌──────────────────────────────────────────────────┐  │
│  │ (SA) Siti Aminah               [ 🟢 IZIN ]       │  │
│  │      NISN: 0098765433 • Kelas 7A                 │  │
│  │                                                  │  │
│  │ 📅 29 Okt 2026 (1 Hari)                          │  │
│  │ 💬 "Menghadiri resepsi pernikahan kakak kandung" │  │
│  │                                                  │  │
│  │ ✅ Disetujui oleh Bpk. Budi Santoso (Wali Kelas) │  │
│  │ ──────────────────────────────────────────────── │  │
│  │                                   [ 👁️ Detail ] │  │
│  └──────────────────────────────────────────────────┘  │
└────────────────────────────────────────────────────────┘
```

---

### 📱 C. Struktur Layar 2 / Modal: Detail Permohonan (`DetailIjinKehadiranModal`)
```
┌────────────────────────────────────────────────────────┐
│ Detail Permohonan Izin Siswa                    [ ✕ ]  │
├────────────────────────────────────────────────────────┤
│ 👤 INFORMASI PESERTA DIDIK                             │
│    Nama Lengkap  : Ahmad Albar                         │
│    NISN / Kelas  : 0098765432 / Kelas 7A               │
│    Nama Ortu/Wali: Bpk. Gunawan (0812-3456-7890)       │
├────────────────────────────────────────────────────────┤
│ 📋 RINCIAN PERMOHONAN                                  │
│    Jenis Izin    : 🔴 Sakit                            │
│    Rentang Waktu : Kamis, 29 Okt s/d Sabtu, 31 Okt 2026│
│    Durasi        : 3 Hari Kalender                     │
│    Status Saat Ini: ⏳ Menunggu Persetujuan             │
│    Alasan / Ket  : "Demam tinggi sejak kemarin sore dan│
│                    disarankan istirahat oleh klinik."  │
├────────────────────────────────────────────────────────┤
│ 📎 BERKAS LAMPIRAN SURAT & BUKTI (2 Berkas)            │
│  ┌───────────────────────┐  ┌────────────────────────┐ │
│  │ [ Pratinjau Foto ]    │  │ [ Pratinjau Foto ]     │ │
│  │ Surat_Dokter.jpg      │  │ Resep_Obat.png         │ │
│  │ (Ketuk utk Zoom)      │  │ (Ketuk utk Zoom)       │ │
│  └───────────────────────┘  └────────────────────────┘ │
├────────────────────────────────────────────────────────┤
│ 📜 RIWAYAT LOG AUDIT PERMOHONAN                        │
│  • 29 Okt 07:15 - Diajukan oleh siswa Ahmad Albar      │
│  • 29 Okt 07:16 - Notifikasi WA terkirim ke Wali Kelas │
├────────────────────────────────────────────────────────┤
│ ⚠️ CATATAN OTORISASI:                                  │
│    "Anda berhak memberikan keputusan pada izin ini."   │
├────────────────────────────────────────────────────────┤
│ 🔘 BILAH AKSI KEPUTUSAN WALI KELAS                     │
│  ┌───────────────────────┐  ┌────────────────────────┐ │
│  │ ❌ Tolak Permohonan   │  │ ✅ Setujui Permohonan  │ │
│  │    (Isi Alasan)       │  │    (Generate Presensi) │ │
│  └───────────────────────┘  └────────────────────────┘ │
└────────────────────────────────────────────────────────┘
```

---

## 4. Spesifikasi REST API Laravel Sanctum Gateway

Seluruh rute mobile ditempatkan di bawah prefix `api/v1/guru/ijin-kehadiran` dan dilindungi oleh otentikasi Sanctum / Fallback Bearer Token:

| Method | Endpoint | Deskripsi & Parameter |
| :--- | :--- | :--- |
| `GET` | `/api/v1/guru/ijin-kehadiran` | Mengambil daftar permohonan izin dengan filter: `status` (`pending`, `approved`, `rejected`), `class_id`, `month` (`YYYY-MM`), `search` (nama/nisn), dan paginasi 15 item per halaman. Mengembalikan metrik statistik ringkas (`pending_count`, `approved_count`, `rejected_count`), daftar kelas yang berhak diakses, serta role akses user (`is_wali_kelas`, `is_bk`, `can_approve`). |
| `GET` | `/api/v1/guru/ijin-kehadiran/{id}` | Mengambil detail lengkap satu permohonan: relasi siswa, kelas, biodata ortu, daftar lampiran URL lengkap (`file_path` & `file_paths`), riwayat log permohonan, dan hak verifikasi (`can_approve_or_reject`). |
| `POST` | `/api/v1/guru/ijin-kehadiran/{id}/approve` | **Menyetujui Izin**: Mengubah status menjadi `approved`, memanggil `LeaveRequestService::syncAttendances`, mencatat log persetujuan, dan memicu `SendWhatsAppNotificationJob` template `leave_approval` ke nomor HP siswa/ortu. |
| `POST` | `/api/v1/guru/ijin-kehadiran/{id}/reject` | **Menolak Izin**: Parameter `{ reason: "..." }` (wajib min 3 karakter). Mengubah status menjadi `rejected`, memanggil `LeaveRequestService::removeAttendances` jika status sebelumnya approved, mencatat log penolakan, dan mengirim WA penolakan ke siswa/ortu. |
| `POST` | `/api/v1/guru/ijin-kehadiran/{id}/reset` | **Kembalikan ke Pending**: Parameter `{ reason: "..." }`. Mengembalikan status menjadi `pending`, membatalkan presensi yang ter-generate, dan mencatat log perubahan. |
| `GET` | `/api/v1/guru/ijin-kehadiran/notification-check` | Endpoint polling ringan untuk memeriksa apakah ada permohonan pending baru yang belum dibaca sejak timestamp terakhir, digunakan untuk menampilkan In-App Notification Banner di Android. |
| `POST` | `/api/v1/auth/fcm-token` | Mendaftarkan / memperbarui token FCM perangkat Android guru: `{ token: "...", device_name: "Samsung A54", platform: "android" }`. |
| `DELETE` | `/api/v1/auth/fcm-token` | Menghapus token perangkat saat guru melakukan logout. |

---

## 5. Rencana Tahapan Eksekusi (Implementation Roadmap)

```
┌────────────────────────────────────────────────────────────────────────┐
│                   TAHAPAN IMPLEMENTASI MENU 4                          │
├────────────────────────────────────────────────────────────────────────┤
│ TAHAP 1: Backend Laravel Controller, Service & Rute API [SELESAI ✅]   │
│          - Buat `GuruIjinKehadiranController.php` (index, show,        │
│            approve, reject, reset, notificationCheck)                  │
│          - Integrasi `LeaveRequestService` (sync & remove attendances) │
│          - Daftarkan 6 rute API di `routes/api.php`                    │
│          - Sinkronkan rute konstanta ke `ApiEndpoints.dart` (Flutter)  │
├────────────────────────────────────────────────────────────────────────┤
│ TAHAP 2: Sistem Notifikasi Backend (WhatsApp & Android Push)           │
│          - Verifikasi antrean WhatsApp Notification                    │
│          - Buat tabel & model `fcm_device_tokens`                      │
│          - Implementasi endpoint pendaftaran token FCM                 │
│          - Buat job `SendLeaveRequestFcmNotificationJob`               │
├────────────────────────────────────────────────────────────────────────┤
│ TAHAP 3: Lapisan Service & State Flutter (apk-android-presensi)        │
│          - Tambahkan endpoint di `ApiEndpoints.dart`                   │
│          - Buat service `ijin_kehadiran_service.dart` via Dio         │
│          - Buat model data `leave_request_model.dart`                  │
├────────────────────────────────────────────────────────────────────────┤
│ TAHAP 4: Antarmuka Flutter (UI Screens & Interactive Modals)           │
│          - Layar utama `ijin_kehadiran_screen.dart` (Tab, Filter, List)│
│          - Modal detail `detail_ijin_kehadiran_modal.dart`             │
│          - Modal viewer lampiran foto zoomable `attachment_viewer.dart`│
│          - Form penolakan & dialog konfirmasi setuju                   │
├────────────────────────────────────────────────────────────────────────┤
│ TAHAP 5: In-App Notification Banner & Realtime Badge Dashboard         │
│          - Integrasikan badge counter pada Card Menu 4 di Beranda      │
│          - Pasang In-App Notification banner jika ada pengajuan pending│
├────────────────────────────────────────────────────────────────────────┤
│ TAHAP 6: Pengujian Komprehensif & Verifikasi Kualitas                  │
│          - Uji alur Approve -> Cek tabel `attendances` & log WA        │
│          - Uji alur Reject -> Cek presensi dicabut & log WA            │
│          - Uji wewenang Guru BK (read-only) vs Wali Kelas (action)     │
│          - Static analysis check (`analyze_files`) & Hot Reload check  │
└────────────────────────────────────────────────────────────────────────┘
```

---

## 6. Standar Kualitas & Pencegahan Regresi

1. **Konsistensi Gaya Kode**:
   - Flutter: Menggunakan `AppConfig.baseUrl` resmi (bukan `AppConfig.apiUrl`).
   - Warna & Tipografi: Mengikuti tema resmi Material 3 `AppColors` dan font *Plus Jakarta Sans*.
   - Hot Reload: Mematuhi *Proactive Flutter Hot Reload Rule* setiap kali file `.dart` diubah.
2. **Keamanan Transaksi Database (ACID)**:
   - Seluruh mutasi status persetujuan, pencatatan log, dan pembuatan/penghapusan presensi di backend Laravel wajib dibungkus dalam blok `DB::beginTransaction()` dan `DB::commit()` dengan penanganan `DB::rollBack()` pada blok `catch`.
3. **Graceful Notification**:
   - Jika layanan WhatsApp Gateway atau FCM Push Notification sedang *offline* atau mengalami gangguan jaringan, proses persetujuan / penolakan izin **TIDAK BOLEH GAGAL**. Pengiriman notifikasi wajib dijalankan melalui background queue asynchronous (`ShouldQueue`).

