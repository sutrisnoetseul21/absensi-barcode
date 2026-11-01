# 📋 Action Plan: Notifikasi Siswa Tidak Mengikuti Pelajaran / Meninggalkan Kelas (Jurnal Mengajar)

> **Konteks Masalah**:  
> Siswa tercatat **HADIR** atau **TERLAMBAT** saat scan barcode di gerbang sekolah pada pagi hari, namun ketika Kegiatan Belajar Mengajar (KBM) berlangsung di kelas, guru mata pelajaran menandai status presensi siswa sebagai **"tidak_mengikuti"** (meninggalkan kelas / bolos / tidak ada di kelas).  
> Sistem membutuhkan mekanisme peringatan otomatis multi-kanal:
> 1. Notifikasi **WhatsApp** yang fleksibel (dapat diaktifkan/dinonaktifkan lewat toggle dan diatur targetnya ke Wali Kelas dan/atau Orang Tua).
> 2. Notifikasi **Push Android (FCM)** yang berdering di Notification Bar perangkat Android siswa dan wali kelas.

---

## 🎯 1. Arsitektur Konfigurasi di Filament Admin (`/admin/manajemen-notifikasi-wa`)

### A. Tab Baru: "Notifikasi Jurnal Mengajar"
Menambahkan Tab ke-4 pada [**\`ManajemenNotifikasiWaPage.php\`**](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Filament/Presensi/Pages/ManajemenNotifikasiWaPage.php) dengan elemen form:
1. **Master Toggle**:
   - \`jurnal_tidak_mengikuti_wa_active\` (boolean): Sakelar utama pengiriman WhatsApp.
2. **Target Pengiriman (Checkboxes / Radio)**:
   - \`jurnal_tidak_mengikuti_kirim_wali_kelas\` (boolean, default: true): Kirim notifikasi WA ke Wali Kelas siswa.
   - \`jurnal_tidak_mengikuti_kirim_ortu\` (boolean, default: true): Kirim notifikasi WA ke nomor WhatsApp Orang Tua/Wali siswa.
3. **Template Pesan WhatsApp Kustom**:
   - Textarea redaksi pesan dengan placeholder dinamis:
     - \`{nama_siswa}\` : Nama lengkap siswa
     - \`{nisn}\` : Nomor Induk Siswa Nasional
     - \`{kelas}\` : Rombongan belajar / kelas siswa
     - \`{nama_guru}\` : Guru mata pelajaran yang mengajar
     - \`{mata_pelajaran}\` : Mata pelajaran yang sedang berlangsung
     - \`{jam_ke}\` : Jam pelajaran (misal: Jam ke 3 - 4)
     - \`{tanggal}\` : Tanggal KBM
     - \`{keterangan}\` : Catatan/alasan yang ditulis guru mapel di jurnal
4. **Penyimpanan**:
   - Disimpan di tabel \`presensi_notification_settings\` secara persisten.

---

## ⚙️ 2. Backend Detection & Service Layer

### A. Service Baru: \`JurnalMengajarNotificationService.php\`
- **Tanggung Jawab**:
  1. Menerima array rekaman presensi jurnal yang baru saja disimpan/diupdate.
  2. Memfilter siswa dengan anomali:
     \`\`\`php
     $isGerbangHadir = in_array($item["status_gerbang"], ["hadir", "terlambat", "telat"], true);
     $isJurnalBolos   = ($item["status_jurnal"] === "tidak_mengikuti");
     \`\`\`
  3. Memastikan tidak terjadi pengiriman duplikat (*idempotency*) untuk jurnal & siswa yang sama pada hari yang sama.
  4. Menjalankan Job WhatsApp (\`SendJurnalTidakMengikutiWaJob\`) jika toggle WA aktif.
  5. Menjalankan Job Push FCM (\`SendJurnalTidakMengikutiFcmJob\`) ke siswa dan wali kelas.

### B. Background Jobs (Queue: \`default\` & \`notifications\`)
1. **\`SendJurnalTidakMengikutiWaJob\`**:
   - Membaca template dari \`presensi_notification_settings\`.
   - Mengirim pesan WA ke Wali Kelas via gateway fisik jika diaktifkan.
   - Mengirim pesan WA ke nomor HP orang tua jika diaktifkan.
2. **\`SendJurnalTidakMengikutiFcmJob\`**:
   - Mengirimkan Direct FCM HTTP v1 ke:
     - **Device Siswa**: Alert peringatan meninggalkan kelas KBM.
     - **Device Wali Kelas**: Notifikasi siswa asuhnya tidak berada di kelas mapel.
   - Menyimpan rekaman pesan ke tabel \`notifications\` agar tersimpan di Inbox Aplikasi Mobile.

---

## 🖥️ 3. Integrasi Trigger Pengiriman

### A. Web Portal Guru (Livewire: \`JurnalMengajarForm.php\`)
- Pada method \`saveJurnal()\`:
  - Setelah transaksi penyimpanan tabel \`jurnal_pembelajarans\` dan \`jurnal_pembelajaran_presensis\` berhasil di-commit, panggil:
    \`\`\`php
    app(JurnalMengajarNotificationService::class)->processPresensiJurnal($jurnal, $this->studentList);
    \`\`\`

### B. Mobile REST API (\`GuruJurnalMengajarController.php\`)
- Pada method \`store()\` dan \`update()\`:
  - Setelah data presensi jurnal tersimpan, panggil service yang sama untuk memproses siswa yang ditandai \`tidak_mengikuti\`.

---

## 📱 4. Integrasi Sisi Mobile Android (Flutter)

### A. Notifikasi Bar Android & In-App Alert
1. **FCM Payload**:
   - \`type: "jurnal_tidak_mengikuti"\`
   - \`id: jurnal_pembelajaran_id\`
   - \`action_url: "/portal-siswa/presensi" / "/portal-guru/jurnal-mengajar"\`
2. **Notification Router (\`notification_router.dart\`)**:
   - Menambahkan case \`"jurnal_tidak_mengikuti"\`:
     - Jika pengguna adalah Siswa: Membuka layar Detail Presensi / Riwayat KBM.
     - Jika pengguna adalah Guru/Wali Kelas: Membuka layar Jurnal Mengajar / Detail Presensi Kelas.
3. **Tampilan Inbox Notifikasi**:
   - Badge warna merah/oranye peringatan (\`Icons.warning_amber_rounded\`) dengan judul penanda jelas.

---

## 🚦 5. Tahapan Eksekusi

| Tahap | Komponen | Deskripsi |
| :--- | :--- | :--- |
| **Tahap 1** | Filament Admin | Membuat tab baru di \`ManajemenNotifikasiWaPage.php\` & migrasi/setting default. |
| **Tahap 2** | Service & Jobs | Membuat \`JurnalMengajarNotificationService\`, \`SendJurnalTidakMengikutiWaJob\`, dan \`SendJurnalTidakMengikutiFcmJob\`. |
| **Tahap 3** | Trigger Web & API | Memasang pemicu deteksi di \`JurnalMengajarForm.php\` dan \`GuruJurnalMengajarController.php\`. |
| **Tahap 4** | Flutter Mobile | Menambahkan routing \`jurnal_tidak_mengikuti\` pada \`notification_router.dart\` dan styling notifikasi. |
| **Tahap 5** | Pengujian & Verifikasi | Simulasi KBM input siswa absen saat gerbang hadir, tes WhatsApp & Push FCM, lalu commit & push. |
