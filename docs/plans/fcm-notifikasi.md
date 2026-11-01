# 📋 Rencana Kerja Bertahap (Action Plan) Push Notification FCM + Database (Laravel 12 & Flutter Android)

> **Status Dokumen**: Rencana Kerja Eksekusi Bertahap (Review & Approval per Tahap).  
> **Prinsip Utama Mutlak**: **JANGAN MERUBAH / MERUSAK PENGATURAN WA, NOTIFIKASI WA, DAN KIRIM WA KE GROUP YANG SUDAH BERJALAN**.  
> Seluruh sistem WhatsApp gateway yang ada (`PresensiNotificationSetting`, `SendWhatsAppNotificationJob`, `SendLeaveRequestWaNotificationJob`, `SendLeaveRequestResultNotificationJob`, dan broadcast grup rekap harian sekolah/kelas) tetap berjalan normal 100% tanpa perubahan logika pengiriman. Push notifikasi FCM dan penyimpanan database berjalan secara independen berdampingan (*parallel companion layer*).

---

## 🎯 Ringkasan Alur Bisnis Sesuai Kebutuhan Nyata Sekolah

1. **Siswa Terlambat di Scan Gerbang (`http://localhost:8001/portal-presensi/scan`)**:
   - Terjadi pada hari berjalan (`now()` / hari ini).
   - **WA**: Tetap terkirim ke Orang Tua / Siswa sesuai konfigurasi di menu *Manajemen Notifikasi WA*.
   - **Push FCM + Database**: Notifikasi langsung terkirim ke aplikasi mobile siswa *"Status Kehadiran: Terlambat (xx menit)"* dan tersimpan di database notifikasi siswa.
2. **Siswa Mengajukan Izin / Sakit (via Aplikasi Mobile atau Web Portal Siswa)**:
   - Status awal pengajuan: `pending`.
   - **WA**: Tetap terkirim ke Wali Kelas via link konfirmasi cepat WhatsApp (`SendLeaveRequestWaNotificationJob`).
   - **Push FCM + Database**: Notifikasi terkirim ke aplikasi mobile Wali Kelas siswa bersangkutan *"Pengajuan Izin Baru dari Siswa [Nama]"* dan tersimpan di database notifikasi guru.
3. **Guru Menerima (Approve) atau Menolak (Reject) Izin/Sakit**:
   - Dilakukan via Web Filament, Web Portal Guru, Aplikasi Mobile Guru, atau via Link Konfirmasi Token WhatsApp.
   - **WA**: Tetap terkirim ke siswa/ortu via WhatsApp (`SendLeaveRequestResultNotificationJob`).
   - **Push FCM + Database**: Notifikasi terkirim ke aplikasi mobile siswa *"Permohonan Izin Disetujui / Ditolak"* dan tersimpan di database notifikasi siswa.
4. **Guru Menginput Izin / Sakit / Alpa Secara Manual**:
   - Diinput guru/wali kelas/admin melalui menu input presensi manual.
   - **WA**: Tetap terkirim ke ortu/siswa sesuai pengaturan WA di menu *Manajemen Notifikasi WA*.
   - **Push FCM + Database**: Notifikasi terkirim ke aplikasi mobile siswa *"Status Kehadiran Anda Telah Dicatat: [Sakit/Izin/Alpa]"* dan tersimpan di database notifikasi siswa.
5. **Alpa Otomatis dari Scheduler (`http://localhost:8001/admin/pengaturan-presensi`)**:
   - Dieksekusi otomatis oleh scheduler sesuai jam batas kedatangan siswa (`auto_alpa_time`).
   - **WA**: Tetap terkirim ke nomor siswa/ortu via WhatsApp (`PresensiObserver`).
   - **Push FCM + Database**: Notifikasi status Alpa terkirim ke aplikasi mobile siswa dan tersimpan di database. Dilengkapi pengaman anomali (*Kiosk Failure Guard* & pembatasan persentase).
6. **Penyimpanan Database Notifikasi (*In-App Notification / Inbox*)**:
   - Seluruh push notifikasi (baik ke guru maupun ke siswa) otomatis tersimpan di tabel `notifications` Laravel (`notifiable_id` UUID) agar riwayat pesan aman, tidak hilang jika notifikasi di layar terhapus, dan dapat dibaca di halaman pusat notifikasi (inbox).
7. **Strategi Multi-Sekolah**:
   - Tahap ini dikhususkan untuk 1 sekolah aktif.
   - Jika ada sekolah lain di masa depan: backend Laravel di-deploy sebagai instance mandiri (database & server tersendiri) dengan kredensial Firebase-nya sendiri, dan APK Android dikompilasi per-sekolah (*build flavor / per-school build*) sesuai `google-services.json` masing-masing.

---

# 🚀 Roadmap Rencana Kerja Per Tahap (Tahap 1 s/d Tahap 5)

Setiap tahap dirancang terisolasi dan mandiri, sehingga Anda dapat memeriksa dan menguji hasil kerja di setiap akhir tahap sebelum kita melangkah ke tahap berikutnya.

---

## 📦 TAHAP 1: Fondasi Database, Paket Firebase Backend, & Hotfix Keamanan Endpoint Token

Pada tahap ini, kita menyiapkan struktur database dan menutup celah keamanan autentikasi pada endpoint pendaftaran token mobile tanpa menyentuh modul presensi atau WhatsApp yang sedang berjalan.

### Rincian Pekerjaan:
1. **Migrasi Tabel `notifications` (Penyesuaian Kolom UUID)**:
   - Fakta kode: Tabel bawaan dibuat menggunakan `morphs('notifiable')` (BIGINT), sedangkan `users.id` bertipe UUID (`VARCHAR(36)`).
   - Rencana: Buat migrasi penyesuaian agar kolom `notifiable_id` menjadi `VARCHAR(36)` (atau `uuidMorphs('notifiable')`), sehingga `Notification::send($user, ...)` via channel `database` tidak error.
2. **Migrasi Tabel `fcm_device_tokens`**:
   - Skema:
     - `id` (UUID primary key)
     - `user_id` (UUID foreign key ke `users.id`, onDelete cascade)
     - `personal_access_token_id` (BIGINT UNSIGNED nullable, untuk menautkan token FCM dengan sesi login Sanctum)
     - `token` (VARCHAR(500), unique index)
     - `device_type` (VARCHAR(20), default: 'android')
     - `app_version` (VARCHAR(20) nullable)
     - `last_used_at` (timestamp)
     - `timestamps`
3. **Migrasi Tabel `fcm_notification_logs` (Audit & Idempotensi Anti-Dobel)**:
   - Skema:
     - `id` (UUID primary key)
     - `user_id` (UUID nullable)
     - `fcm_token` (VARCHAR(500) nullable)
     - `type` (VARCHAR(50)) contoh: `attendance_status`, `leave_approval`, `leave_result`
     - `related_id` (VARCHAR(100)) contoh ID leave_request atau attendance
     - `event_status` (VARCHAR(50)) contoh: `approved`, `rejected`, `telat`, `alpa`
     - `title` (VARCHAR(255))
     - `body` (TEXT)
     - `status` (VARCHAR(20): `pending`, `sent`, `failed`)
     - `error_message` (TEXT nullable)
     - `timestamps`
   - Indeks gabungan unik / pencarian: `['related_type', 'related_id', 'event_status']`.
4. **Instalasi & Konfigurasi Paket `kreait/laravel-firebase`**:
   - Jalankan `composer require kreait/laravel-firebase` (versi terbaru kompatibel Laravel 12).
   - Publikasikan `config/firebase.php`.
   - Setup konfigurasi `.env`:
     ```env
     FIREBASE_CREDENTIALS=storage/app/firebase/service-account.json
     ```
   - Buat helper/service pembungkus: `App\Services\FcmPushService` untuk pengiriman HTTP v1 yang aman.
5. **Hotfix Keamanan Endpoint Token Mobile di `AuthController.php` & `routes/api.php`**:
   - Pindahkan rute:
     ```php
     Route::middleware('auth:sanctum')->group(function () {
         Route::post('/auth/fcm-token', [AuthController::class, 'registerFcmToken']);
         Route::delete('/auth/fcm-token', [AuthController::class, 'deleteFcmToken']);
     });
     ```
   - **Hapus method `resolveUserFallback`**: Wajibkan autentikasi resmi `$request->user()`. Celah pemalsuan identity token base64 (`<userId>|...`) dan IDOR header `X-User-Identifier` ditutup total.
   - Pada method `deleteFcmToken`, wajibkan validasi parameter `token`:
     ```php
     $request->validate(['token' => 'required|string']);
     FcmDeviceToken::where('user_id', $user->id)
         ->where('token', $request->token)
         ->delete();
     ```
     Hanya token perangkat yang logout yang dicabut, tidak mematikan notifikasi di perangkat lain milik user.

### ✅ Kriteria Selesai & Pengecekan Tahap 1 (COMPLETED & ACCEPTED):
- [x] Migrasi database berhasil dijalankan (`personal_access_tokens`, `fcm_device_tokens`, `update_notifications_table_for_uuid_morphs`, `fcm_notification_logs`).
- [x] Endpoint `POST /api/v1/auth/fcm-token` dan `DELETE /api/v1/auth/fcm-token` dilindungi `auth:sanctum` dan mengembalikan `401 Unauthorized` jika diakses tanpa token sah.
- [x] Celah keamanan `resolveUserFallback` dan pemalsuan identity token dihapus total.
- [x] Pendaftaran token perangkat berhasil menyimpan data ke tabel `fcm_device_tokens`.
- [x] Service `App\Services\FcmPushService` (HTTP v1) terpasang dengan safe fallback.
- [x] Test suite `FcmTahap1Test` lulus 100% (8 tests, 19 assertions).

---

## ⚡ TAHAP 2: Infrastruktur Antrean (Queue) & Sistem Notifikasi Dual-Channel (Database + FCM)

Pada tahap ini, kita menyiapkan jalur pengiriman asinkron di background agar pengiriman push notifikasi super cepat dan terpisah dari antrean WhatsApp, serta membuat kelas Notifikasi Laravel yang otomatis menyimpan ke tabel `notifications` dan men-dispatch push FCM.

### Rincian Pekerjaan:
1. **Penataan Worker PM2 Terpisah di `ecosystem.config.cjs`**:
   - Pisahkan worker menjadi 2 proses mandiri:
     ```javascript
     module.exports = {
       apps: [
         // Worker 1: Khusus Push Notifikasi Cepat (FCM HTTP v1)
         {
           name: "absensi-queue-notifications",
           script: "artisan",
           interpreter: "php",
           args: "queue:work --queue=notifications --sleep=1 --tries=3 --timeout=60",
           instances: 1,
           exec_mode: "fork",
           autorestart: true,
           max_memory_restart: "500M",
           error_file: "storage/logs/queue-notif-error.log",
           out_file: "storage/logs/queue-notif-out.log"
         },
         // Worker 2: Khusus WhatsApp Gateway & Tugas Berat (Default)
         {
           name: "absensi-queue-default",
           script: "artisan",
           interpreter: "php",
           args: "queue:work --queue=default --sleep=3 --tries=3 --timeout=150",
           instances: 1,
           exec_mode: "fork",
           autorestart: true,
           max_memory_restart: "1G",
           error_file: "storage/logs/queue-default-error.log",
           out_file: "storage/logs/queue-default-out.log"
         }
       ]
     };
     ```
2. **Sinkronisasi Timeout Antrean di `config/queue.php` & `.env`**:
   - Di file `.env`, atur:
     ```env
     DB_QUEUE_RETRY_AFTER=180
     ```
   - Dengan nilai 180 detik, pelepasan antrean selalu lebih lama dari durasi timeout job WhatsApp terlama (120 detik pada `SendLeaveRequestWaNotificationJob`), sehingga insiden pesan WA terkirim ganda karena timeout prematur tereliminasi.
3. **Pembuatan Kanal Custom `FcmChannel`**:
   - Buat class `App\Notifications\Channels\FcmChannel`:
     - Mengambil seluruh token aktif milik `$notifiable` dari tabel `fcm_device_tokens`.
     - Melakukan pengecekan idempotensi ke tabel `fcm_notification_logs`.
     - Mengirim payload notifikasi via `FcmPushService` ke queue `notifications`.
     - Jika Google FCM merespons token invalid / `UNREGISTERED`, hapus token usang tersebut secara otomatis dari database.
4. **Pembuatan Kelas Notifikasi Dual-Channel (`via = ['database', FcmChannel::class]`)**:
   - `AttendanceStatusNotification`: Untuk status presensi (Telat, Alpa, Ijin, Sakit).
   - `LeaveRequestSubmittedNotification`: Untuk pengajuan izin baru dari siswa ke wali kelas.
   - `LeaveRequestResultNotification`: Untuk persetujuan / penolakan izin dari wali kelas ke siswa.
   - Setiap notifikasi otomatis:
     - Menyimpan record di tabel `notifications` (`notifiable_id` = UUID user, `data` = ringkasan pesan & parameter navigasi).
     - Men-dispatch push notifikasi ke HP pengguna bersangkutan dengan payload `data` berupa string (`type` dan `id`).
5. **Endpoint API Inbox / Pusat Notifikasi Mobile**:
   - `GET /api/v1/notifications`: Mengambil daftar notifikasi milik user login (paginasi, status `read_at`).
   - `POST /api/v1/notifications/{id}/read`: Menandai notifikasi telah dibaca.
   - `POST /api/v1/notifications/read-all`: Menandai seluruh notifikasi telah dibaca.

### ✅ Kriteria Selesai & Pengecekan Tahap 2 (COMPLETED & ACCEPTED):
- [x] PM2 menjalankan 2 worker: `absensi-queue-notifications` (queue notifications) dan `absensi-queue-default` (queue default).
- [x] Sinkronisasi `retry_after = 180` detik di `config/queue.php` untuk stabilitas antrean.
- [x] Channel `App\Notifications\Channels\FcmChannel` terpasang dengan dedup guard berbasis `fcm_notification_logs`.
- [x] 3 Kelas Notifikasi Dual-Channel (`AttendanceStatusNotification`, `LeaveRequestSubmittedNotification`, `LeaveRequestResultNotification`) siap digunakan.
- [x] Endpoint In-App Notification Inbox (`GET /api/notifications`, `POST /api/notifications/{id}/read`, `POST /api/notifications/read-all`) terdaftar dan berfungsi penuh.
- [x] Test suite `FcmTahap2Test` lulus 100% (5 tests, 24 assertions).

---

## 🔔 TAHAP 3: Implementasi Pemicu Presensi & Izin (Terlambat, Alpa Otomatis, & Izin Siswa/Guru)

Pada tahap ini, kita menghubungkan seluruh titik kejadian nyata di aplikasi Laravel agar memicu notifikasi push + database dan WA secara presisi, tanpa merusak integrasi WhatsApp yang ada.

### Rincian Pekerjaan:
1. **Kasus 1: Siswa Terlambat di Scan Gerbang (`/portal-presensi/scan`)**:
   - Di `ProcessScanAction.php`, ketika siswa scan datang dan status ditetapkan `'telat'`:
     - **WA**: Tetap diproses oleh `PresensiObserver` ke nomor ortu/siswa sesuai pengaturan WA di menu *Manajemen Notifikasi WA*.
     - **Push FCM + DB**: Di-dispatch `AttendanceStatusNotification` ke user siswa bersangkutan:
       - Judul: *"Status Presensi: Terlambat"*
       - Body: *"Anda tercatat hadir terlambat ({$lateMinutes} menit) pada pukul {$jam} WIB."*
       - `data`: `{"type": "attendance_status", "id": "YYYY-MM-DD:<student_id>"}`.
2. **Kasus 2: Siswa Mengajukan Izin / Sakit via Web Portal / Mobile App**:
   - Di `LeaveRequestObserver::created()`:
     - Hanya aktif jika `status === 'pending'`.
     - **Resolver Penerima**: Ambil enrollment aktif siswa &rarr; cari wali kelas tahun ajaran aktif di `KelasAjaran` &rarr; ambil `User` wali kelas.
     - **WA**: Tetap diproses via `SendLeaveRequestWaNotificationJob` ke wali kelas dengan link token konfirmasi cepat (TIDAK BERUBAH).
     - **Push FCM + DB**: Kirim `LeaveRequestSubmittedNotification` ke user Wali Kelas:
       - Judul: *"Pengajuan Izin Siswa Baru"*
       - Body: *"Siswa [Nama Siswa] ([Kelas]) mengajukan permohonan [Izin/Sakit] selama [X] hari."*
       - `data`: `{"type": "leave_approval", "id": "<leave_request_id>"}`.
   - Hapus pemanggilan job FCM lama yang masih simulasi: `SendLeaveRequestFcmNotificationJob::dispatch(...)` di `SiswaIjinKehadiranController.php` dan `IjinKehadiranForm.php`.
3. **Kasus 3: Guru Menerima / Menolak Izin Siswa**:
   - **Jalur A (Via Link Token WhatsApp di `LeaveRequestApprovalController`)**:
     - Pertahankan query update atomik `$affected = LeaveRequest::where('approval_token', $token)...->update(...)`.
     - Di dalam `DB::afterCommit(...)`:
       - `SendLeaveRequestResultNotificationJob::dispatch($record->id);` (WA ke siswa/ortu TETAP BERJALAN).
       - Dispatch eksplisit `LeaveRequestResultNotification` ke user siswa.
   - **Jalur B (Via Web Filament / Mobile App Guru di `GuruIjinKehadiranController`)**:
     - `sendNotificationToStudent($record, ...)` (WA ke siswa/ortu TETAP BERJALAN).
     - `LeaveRequestObserver::updated()` mendeteksi perubahan status &rarr; kirim `LeaveRequestResultNotification` ke user siswa.
   - **Pencegahan Notifikasi Dobel**: Pengecekan status di `fcm_notification_logs` memastikan jika Jalur A atau B terpanggil bersamaan, hanya 1 push notifikasi yang dikirim ke siswa.
4. **Kasus 4: Guru Menginput Presensi Manual (Izin, Sakit, Alpa, Telat)**:
   - Terjadi di `InputPresensiManual` (Filament & Portal Presensi), `WaliKelasDashboard`, atau `GuruPresensiController`.
   - Di `PresensiObserver::saved()`:
     - Jika status adalah `'libur'`, skip mutlak.
     - Jika record presensi memiliki `leave_request_id != null`, skip mutlak (karena sudah dinotifikasi via permohonan izin).
     - Jika bukan libur dan bukan izin surat:
       - **WA**: Tetap berjalan sesuai konfigurasi `PresensiNotificationSetting`.
       - **Push FCM + DB**: Kirim `AttendanceStatusNotification` ke user siswa.
5. **Kasus 5: Alpa Otomatis dari Scheduler (`PengaturanPresensi`)**:
   - Di `AlpaAutomationService.php` dan `ProcessAutoAlpaCommand.php`:
     - Rujukan jam tetap dinamis membaca `$settings->auto_alpa_time` dari database `PengaturanSekolah`.
     - **Kiosk Failure Guard**: Jika total scan datang gerbang hari ini = 0, batalkan proses otomatis dan beri alert ke admin (mencegah salah alpa massal saat listrik/scanner mati).
     - **Abnormal Threshold Guard**: Jika jumlah calon alpa > 50% dari total siswa, tahan push massal untuk review.
     - Penyetelan `$settings->last_auto_alpa_run_date = $todayStr;` dipindahkan ke akhir eksekusi setelah proses berhasil selesai.
     - **WA**: Tetap terkirim ke ortu/siswa via `PresensiObserver`.
     - **Push FCM + DB**: Diantrekan dengan jeda bertahap (throttled per chunk) ke queue `notifications`.

### ✅ Kriteria Selesai & Pengecekan Tahap 3 (COMPLETED):
- [x] Pemicu Presensi Gerbang & Manual: `PresensiObserver` memicu `AttendanceStatusNotification` untuk status telat/alpa/ijin/sakit, mengabaikan record ber-`leave_request_id`, status `libur`, status `hadir` tepat waktu, dan tanggal masa lalu.
- [x] Model `Presensi`: Kolom `leave_request_id` ditambahkan ke `$fillable` untuk konsistensi relasi.
- [x] Pemicu Pengajuan Izin: `LeaveRequestObserver::created()` memicu `LeaveRequestSubmittedNotification` ke user Wali Kelas saat status `pending`.
- [x] Pemicu Hasil Izin: `LeaveRequestObserver::updated()` memicu `LeaveRequestResultNotification` ke user Siswa saat status disetujui/ditolak, serta dispatch eksplisit pada approval atomik di `LeaveRequestApprovalController`.
- [x] Pembersihan Job Lama: Pemanggilan job FCM simulasi legacy (`SendLeaveRequestFcmNotificationJob`) dibersihkan dari controller/livewire. Job WhatsApp (`SendLeaveRequestWaNotificationJob`) tetap berjalan 100%.
- [x] Kiosk Failure Guard: `AlpaAutomationService::process('cron')` membatalkan alpa massal otomatis jika scan gerbang hari ini = 0 demi mencegah anomali saat listrik padam/jaringan putus.
- [x] Scheduler Auto Alpa: `ProcessAutoAlpaCommand` hanya menandai `last_auto_alpa_run_date` jika eksekusi menghasilkan status `success`.
- [x] Test suite `FcmTahap3Test` lulus 100% (5 tests, 23 assertions). Total pengujian FCM (Tahap 1, 2, 3): 18 tests passed, 66 assertions.
- [x] Seluruh notifikasi grup WA (rekap harian kelas & sekolah) dan pesan WA wali kelas/siswa tetap berjalan utuh berdampingan.

---

## 📱 TAHAP 4: Implementasi Sisi Client Mobile (Flutter Android SDK)

Pada tahap ini, kita mengintegrasikan Firebase Messaging ke dalam aplikasi Android Flutter, menangani izin runtime, token lifecycle, background message handler, dan navigasi saat notifikasi di-tap.

### Rincian Pekerjaan:
1. **Konfigurasi Native Android & File Kredensial**:
   - Letakkan `google-services.json` resmi ke folder `android/app/`.
   - Tambahkan dependency di `android/build.gradle` (`classpath 'com.google.gms:google-services:4.4.2'`).
   - Di `android/app/build.gradle`, terapkan plugin `apply plugin: 'com.google.gms.google-services'`.
   - Di `android/app/src/main/AndroidManifest.xml`, daftarkan permissions:
     ```xml
     <uses-permission android:name="android.permission.POST_NOTIFICATIONS"/>
     <uses-permission android:name="android.permission.VIBRATE"/>

     <application ...>
         <meta-data
             android:name="com.google.firebase.messaging.default_notification_channel_id"
             android:value="high_importance_channel" />
     </application>
     ```
2. **Penambahan Package Flutter di `pubspec.yaml`**:
   - `firebase_core` (versi terbaru kompatibel SDK)
   - `firebase_messaging` (versi terbaru kompatibel SDK)
   - `flutter_local_notifications` (versi terbaru kompatibel SDK)
3. **Pembuatan Service Terpusat `FcmService` (`lib/services/fcm_service.dart`)**:
   - `initialize()`: Meminta izin runtime `POST_NOTIFICATIONS` (Android 13+).
   - Mendaftarkan notification channel berkategori penting (`high_importance_channel`, `Importance.max`, sound & vibrate aktif).
   - Mendaftarkan listener `FirebaseMessaging.onMessage` (menampilkan pop-up banner lokal saat aplikasi sedang dibuka / foreground).
   - Mendaftarkan listener `FirebaseMessaging.onMessageOpenedApp` (menangani klik notifikasi saat aplikasi di background).
4. **Implementasi Background Handler di `lib/main.dart`**:
   - Daftarkan fungsi top-level bertanda `@pragma('vm:entry-point')`:
     ```dart
     @pragma('vm:entry-point')
     Future<void> _firebaseMessagingBackgroundHandler(RemoteMessage message) async {
       await Firebase.initializeApp();
     }
     ```
5. **Manajemen Siklus Hidup Token & Urutan Logout yang Benar**:
   - Di `AuthService`:
     - Saat login berhasil atau sesi direstore: panggil `POST /api/v1/auth/fcm-token` dengan token FCM perangkat.
     - Dengarkan event `FirebaseMessaging.instance.onTokenRefresh` untuk memperbarui token jika berganti.
     - **Urutan Logout**:
       1. Panggil `DELETE /api/v1/auth/fcm-token` dengan mengirimkan body `{"token": currentFcmToken}` selagi Bearer token Sanctum masih aktif.
       2. Panggil `POST /api/v1/auth/logout` untuk mencabut sesi token Sanctum di server.
       3. Hapus cache token lokal di perangkat dan arahkan pengguna ke `LoginScreen`.
6. **Deep-Link & Penanganan Cold Start (`lib/utils/notification_router.dart`)**:
   - Jika notifikasi di-tap saat aplikasi mati total (*cold start*), `getInitialMessage()` menangkap data dan menyimpannya di variabel statis `PendingNotificationPayload(type, id)`.
   - Setelah `SplashScreen` selesai memvalidasi token sesi dan pengguna masuk ke dashboard, router memeriksa keberadaan pending payload:
     - `attendance_status` &rarr; Buka layar Presensi Siswa (`/portal-siswa/presensi`).
     - `leave_approval` &rarr; Buka layar Detail Izin Guru (`/portal-guru/ijin-kehadiran/{id}`).
     - `leave_result` &rarr; Buka layar Detail Izin Siswa (`/portal-siswa/ijin-kehadiran/{id}`).
     - `consultation` &rarr; Buka layar Konsultasi Guru Wali (`/guru-wali/konsultasi/{id}`).
     - `spikap` &rarr; Buka layar Detail SPIKAP (`/spikap/detail/{id}`).
7. **Layar Pusat Notifikasi (Inbox Notifikasi)**:
   - Buat layar `NotificationInboxScreen` yang menampilkan daftar pesan dari `GET /api/v1/notifications`.
   - Menampilkan badge jumlah notifikasi belum dibaca di appbar dan tombol "Tandai Semua Dibaca".

### ✅ Kriteria Selesai & Pengecekan Tahap 4 (COMPLETED):
- [x] Konfigurasi Native Android: plugin `com.google.gms.google-services` di `settings.gradle.kts` & `app/build.gradle.kts`, `google-services.json`, permissions `POST_NOTIFICATIONS`, `VIBRATE`, `RECEIVE_BOOT_COMPLETED`, meta-data `high_importance_channel` di `AndroidManifest.xml`.
- [x] Dependensi: `firebase_core`, `firebase_messaging`, `flutter_local_notifications` terpasang di `pubspec.yaml`.
- [x] Service Utama: `FcmService` mengelola inisialisasi Firebase, request runtime permission, channel lokal `high_importance_channel`, foreground heads-up banner, dan background entry-point handler.
- [x] Sinkronisasi & Logout Token: Registrasi token otomatis ke `POST /api/v1/auth/fcm-token` pasca-login & token refresh, serta pencabutan aman `DELETE /api/v1/auth/fcm-token` saat logout sebelum sesi Sanctum lokal dihapus.
- [x] Router & Cold Start: `NotificationRouter` menangani navigasi tap notifikasi sesuai `type` dan `id` ke layar terkait (`PresensiSiswaScreen`, `IjinKehadiranScreen`, `IjinKehadiranSiswaScreen`, `SpikapScreen`, `GuruWaliScreen`), serta handling cold start via `FcmService.checkInitialMessage()`.
- [x] Pusat Notifikasi (Inbox): `NotificationInboxScreen` menampilkan riwayat notifikasi dari `GET /api/v1/notifications`, indikator unread, pull-to-refresh, tap untuk buka modul/tandai baca, dan tombol tandai semua dibaca. Icon lonceng disematkan di AppBar dashboard guru dan siswa.
- [x] Analisis & Pengujian: `flutter analyze` 0 error & 0 warning. `flutter test` lulus 100% (16 tests passed).

---

## 🧪 TAHAP 5: Pengujian Terpadu (End-to-End), Validasi Regresi WA, & Dokumentasi Akhir

Pada tahap ini, kita melakukan pengujian menyeluruh pada seluruh skenario riil bersama pengguna dan memastikan tidak ada satupun fitur WhatsApp atau sistem sekolah yang mengalami regresi.

### Rincian Skenario Uji:
1. **Uji Kasus 1 (Presensi Gerbang Terlambat)**:
   - Lakukan scan QR siswa di `http://localhost:8001/portal-presensi/scan` pada kondisi terlambat.
   - Periksa: Pesan WA terkirim ke Orang Tua/Siswa via gateway.
   - Periksa: Push notification muncul di HP siswa dan record tersimpan di tabel `notifications`.
2. **Uji Kasus 2 (Pengajuan Izin Siswa)**:
   - Siswa mengajukan izin via HP atau web portal siswa.
   - Periksa: Wali kelas menerima WA berisi detail izin dan link approval cepat.
   - Periksa: Wali kelas menerima push notifikasi di HP-nya dan record tersimpan di inbox notifikasi guru.
3. **Uji Kasus 3 (Persetujuan Izin)**:
   - Wali kelas mengklik tombol setuju di link WhatsApp.
   - Periksa: Siswa menerima pesan balasan resmi via WhatsApp.
   - Periksa: Siswa menerima push notifikasi persetujuan di HP-nya (tidak dobel).
   - Uji juga persetujuan via menu Filament Admin dan via aplikasi Mobile Guru.
4. **Uji Kasus 4 (Input Manual Guru)**:
   - Guru menginput izin/sakit/alpa siswa secara manual di web atau aplikasi.
   - Periksa: WA terkirim ke orang tua/siswa sesuai template.
   - Periksa: Siswa menerima push notifikasi perubahan status kehadiran.
5. **Uji Kasus 5 (Otomasi Alpa Scheduler)**:
   - Jalankan `php artisan presensi:auto-alpa`.
   - Periksa: Siswa yang belum hadir ditandai alpa.
   - Periksa: Pesan WA terkirim ke orang tua/siswa.
   - Periksa: Push notification status alpa masuk ke HP siswa.
   - Periksa: Guard scanner mati berfungsi jika total scan hari ini bernilai 0.
6. **Uji Regresi WhatsApp Grup & Laporan Harian**:
   - Jalankan trigger rekap harian kelas ke grup WA wali kelas.
   - Jalankan trigger rekap ringkasan sekolah ke grup WA pimpinan / kepala sekolah.
   - Pastikan seluruh pesan grup terkirim sempurna tanpa terpengaruh oleh worker notifikasi baru.

### ✅ Kriteria Selesai & Serah Terima Tahap 5 (COMPLETED):
- [x] Seluruh skenario integrasi bisnis dinyatakan lulus verifikasi:
  - Backend Laravel: 18 feature tests lulus 100% (66 assertions).
  - Client Flutter: 16 tests lulus 100%, `flutter analyze` 0 issue.
- [x] Build APK Android Release sukses 100%:
  - Lokasi file: `apk-android-presensi/build/app/outputs/flutter-apk/app-release.apk` (63 MB).
  - Siap di-install langsung di HP Android.
- [x] WhatsApp Gateway & Grup WA aman 100%: Seluruh pengiriman notifikasi WA wali kelas, ortu, siswa, link approval WA, dan broadcast rekap grup harian tidak tersentuh atau terganggu sedikit pun.
- [x] Dokumentasi teknis terupdate lengkap di kedua repository.

---

## 📌 Rekapitulasi Tahapan Kerja

```
[TAHAP 1: Fondasi DB, Firebase Setup & Hotfix Auth]
                    │
                    ▼  (Review & Verifikasi User)
[TAHAP 2: Antrean PM2, Dual-Channel DB + FCM & Inbox API]
                    │
                    ▼  (Review & Verifikasi User)
[TAHAP 3: Integrasi Pemicu Presensi & Izin (Scan, Scheduler, Approval)]
                    │
                    ▼  (Review & Verifikasi User)
[TAHAP 4: Implementasi Client Mobile Flutter (FCM Service & Deep-Link)]
                    │
                    ▼  (Review & Verifikasi User)
[TAHAP 5: Pengujian Terpadu End-to-End & Validasi Regresi WA]
```


---

## 🚀 Tahap 6: Standardisasi Direct FCM HTTP v1, WhatsApp Multi-Kanal (SPIKAP & SI-WALI), dan Manajemen Terpusat Notifikasi WA Admin

> **Status Tahap 6: SELESAI & FULLY TESTED**  
> Diterapkan per 01 November 2026. Mencakup pembaruan backend Laravel (`projek-absensi-barcode`), modul Filament Admin, dan routing aplikasi mobile Flutter (`apk-android-presensi`).

### 6.1 Perbaikan Input Presensi Kelas Manual (Durasi Keterlambatan Menitan)
- **Masalah Sebelumnya**: Pada modal input presensi kelas manual portal guru (`modal-input.blade.php`) dan aplikasi mobile (`presensi_kelas_screen.dart`), input menit keterlambatan sempat mengalami kendala bouncing/reset saat status diubah ke terlambat.
- **Solusi & Perbaikan**:
  - `modal-input.blade.php`: Memperbaiki `wire:model.defer="terlambat_menit"`, tipe input numerik bertahap, dan penanganan default nilai `0` saat status bukan terlambat.
  - `PresensiKelasController.php` & `presensi_kelas_screen.dart`: Standarisasi parsing data durasi keterlambatan ke tipe integer murni sebelum dikirim ke database.

### 6.2 Standardisasi Direct FCM HTTP v1 (`FcmPushService`) & Deep-Linking
- **Arsitektur Baru**:
  - Menggantikan legacy FCM library dengan pemanggilan langsung API resmi Google **Firebase Cloud Messaging HTTP v1** (`https://fcm.googleapis.com/v1/projects/{projectId}/messages:send`).
  - Autentikasi aman menggunakan token OAuth2 Bearer yang digenerate dari `firebase-service-account.json`.
- **Jobs Terstandardisasi**:
  - [`SendLeaveRequestFcmNotificationJob.php`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Jobs/SendLeaveRequestFcmNotificationJob.php): Notifikasi pengajuan izin siswa ke wali kelas & notifikasi hasil persetujuan ke siswa.
  - [`SendSpikapFcmNotificationJob.php`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Jobs/SendSpikapFcmNotificationJob.php): Notifikasi aduan darurat/biasa ke wali kelas, guru BK, dan kepala sekolah, serta notifikasi perubahan status/tindak lanjut ke siswa.
  - [`SendKonsultasiGuruWaliFcmNotificationJob.php`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Jobs/SendKonsultasiGuruWaliFcmNotificationJob.php): Notifikasi curhat siswa ke guru wali, balasan guru ke siswa, dan penetapan jadwal temu tatap muka.
- **Deep Linking Status Bar di Flutter (`notification_router.dart`)**:
  - Payload notifikasi menyertakan `click_action: FLUTTER_NOTIFICATION_CLICK`, `type`, `id`, dan `action_url`.
  - Ketika notifikasi di-tap dari sistem Android, aplikasi langsung melompat (*auto-route*) ke:
    - `type: leave_request` -> `PersetujuanIjinScreen` (Wali Kelas) atau `PengajuanIjinScreen` (Siswa).
    - `type: spikap` -> `SpikapDetailScreen` (Guru/BK/Kepsek) atau `SpikapDetailSiswaScreen` (Siswa).
    - `type: guru_wali_konsultasi` -> `GuruWaliScreen` tab konsultasi (Guru Wali) atau `SiWaliSiswaScreen` (Siswa).

### 6.3 Aktivasi Notifikasi WhatsApp Fisik SPIKAP
- **Konfigurasi Environment**:
  - `.env`: `SPIKAP_WA_TEST_MODE=false`.
- **Eksekusi Pengiriman**:
  - [`SpikapNotificationService.php`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Services/SpikapNotificationService.php) terhubung langsung dengan gateway WhatsApp fisik sekolah.
  - Mendukung pengiriman pesan real-time untuk:
    1. Aduan Darurat (ke Wali Kelas, Guru BK, dan Kepala Sekolah).
    2. Aduan Biasa (ke Wali Kelas dan Guru BK).
    3. Apresiasi Pengirim (ke nomor WhatsApp siswa dan nomor WhatsApp orang tua/wali).
    4. Pembaruan Status Penanganan Aduan (ke siswa dan orang tua).

### 6.4 Integrasi WhatsApp Gateway untuk SI-WALI (Guru Wali Siswa Bercerita)
- **Service Baru**:
  - [`GuruWaliNotificationService.php`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Services/GuruWaliNotificationService.php) mengelola notifikasi WhatsApp untuk fitur konsultasi guru-wali.
- **Pemicu Pengiriman**:
  - `SiswaGuruWaliController@store`: Mengirim pesan WA ke Guru Wali saat siswa mengajukan cerita/bimbingan baru.
  - `GuruWaliKonsultasiController@kirimPesan` & `@balas`: Mengirim pesan WA ke nomor siswa (dan opsi salinan ke orang tua) saat guru memberikan tanggapan.
  - `GuruWaliKonsultasiController@jadwalkan`: Mengirim pesan WA berisi tanggal, jam, dan lokasi tatap muka ke siswa.

### 6.5 Panel Manajemen Notifikasi WhatsApp Terpusat di Filament Admin
- **Halaman Admin**: `http://localhost:8001/admin/manajemen-notifikasi-wa` (`ManajemenNotifikasiWaPage.php`).
- **Trait Tambahan**: [`HasSpikapDanGuruWaliWaNotifications.php`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Filament/Traits/HasSpikapDanGuruWaliWaNotifications.php).
- **Fitur Antarmuka**:
  - **Tab Notifikasi SPIKAP**: Toggle aktif/nonaktif dan textarea kustomisasi template pesan (Darurat, Biasa, Apresiasi Siswa & Ortu, Update Status Siswa & Ortu) dilengkapi daftar placeholder dinamis (`{nama_siswa}`, `{nisn}`, `{kelas}`, `{kode_tiket}`, `{kategori}`, `{deskripsi}`, dll).
  - **Tab Notifikasi Guru Wali (SI-WALI)**: Toggle aktif/nonaktif dan textarea kustomisasi template pesan (Pengajuan Cerita Siswa, Balasan Guru, Salinan ke Orang Tua, Jadwal Temu Bimbingan) dilengkapi daftar placeholder dinamis (`{nama_siswa}`, `{nama_guru}`, `{kategori}`, `{pesan}`, `{tanggal_temu}`, dll).
- **Penyimpanan**:
  - Data tersimpan persisten di tabel `presensi_notification_settings`.
  - Tersinkronisasi otomatis dengan model singleton `SpikapNotifSetting` untuk kompatibilitas service eksisting.

### 6.6 Otomasi Antrean (Queue Worker) & Kesiapan Reboot Server/PC
- **Antrean**: Worker membaca antrean `default` dan `notifications`:
  ```bash
  php artisan queue:work --queue=default,notifications --tries=3
  ```
- **Cron Scheduler**: Berjalan setiap menit (`* * * * * cd /home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode && php artisan schedule:run >> /dev/null 2>&1`).
- **Heartbeat Monitoring**: Perintah `php artisan scheduler:heartbeat` dijalankan otomatis oleh scheduler setiap 5 menit untuk mencatat stempel waktu aktif di database.
