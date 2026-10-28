# 📟 Panduan Operasional Hardware Kiosk & WhatsApp Gateway

Dokumen ini memuat panduan teknis operasional **Perangkat Keras Stasiun Pemindaian Barcode (*Kiosk Gerbang & Perpustakaan*)** serta **Manajemen Server WhatsApp Evolution API Gateway** untuk sistem **Presensi Barcode & Smart School ERP**.

---

## 📌 Daftar Isi
1. [Spesifikasi & Konfigurasi Barcode Scanner USB](#1-spesifikasi--konfigurasi-barcode-scanner-usb)
2. [Mode Chromium Kiosk Fullscreen & Autostart on Boot](#2-mode-chromium-kiosk-fullscreen--autostart-on-boot)
3. [Manajemen Audio Feedback & Kebijakan Autoplay](#3-manajemen-audio-feedback--kebijakan-autoplay)
4. [Mitigasi Gangguan Jaringan (Network Blip) di Pos Gerbang](#4-mitigasi-gangguan-jaringan-network-blip-di-pos-gerbang)
5. [Manajemen Sesi WhatsApp Evolution API Gateway](#5-manajemen-sesi-whatsapp-evolution-api-gateway)
6. [Kebijakan Anti-Banned & Perlindungan Nomor WhatsApp Dinas](#6-kebijakan-anti-banned--perlindungan-nomor-whatsapp-dinas)

---

## 1. Spesifikasi & Konfigurasi Barcode Scanner USB

Stasiun kiosk presensi gerbang dan perpustakaan menggunakan pemindai barcode otomatis tanpa tombol (*Hands-free Omnidirectional Auto-Sensing Barcode Scanner*).

### Standarisasi Konfigurasi Scanner
1. **Mode Komunikasi: USB HID Keyboard Emulation**
   Pemindai harus beroperasi dalam mode emulasi keyboard, sehingga setiap pembacaan barcode akan dikirimkan ke komputer seolah-olah diketikkan dengan kecepatan tinggi oleh keyboard fisik.
2. **Suffix Terminator: Wajib Mengirimkan Karakter `Enter` (`CR/LF`)**
   Setelah baris kode barcode dibaca, scanner wajib memicu sinyal tombol `Enter` agar event formulir JavaScript/Livewire langsung mengirimkan data presensi tanpa perlu intervensi klik mouse.
3. **Sensitivitas Auto-Sensing**
   Atur scanner ke mode *Continuous Scan* atau *Auto-Induction*. Sinar pemindai akan otomatis menyala ketika kartu pelajar didekatkan pada jarak 5–15 cm dari kaca sensor.

### Cara Menyetel Scanner Barcode Baru:
1. Buka buku manual barcode bawaan pemindai.
2. Pindai lembar barcode konfigurasi dengan urutan berikut:
   - `[Restore Factory Default]`
   - `[USB HID Mode]`
   - `[Add Enter / CR Suffix]`
   - `[Enable Auto-Sensing / Continuous]`
3. **Pengujian**: Buka aplikasi teks editor biasa (gedit / notepad / terminal). Dekatkan kartu barcode siswa. Jika kartu terbaca dan kursor otomatis berpindah ke baris baru di bawahnya, konfigurasi dinyatakan **100% Berhasil**.

---

## 2. Mode Chromium Kiosk Fullscreen & Autostart on Boot

Komputer kiosk gerbang (Mini PC / Thin Client / Raspberry Pi OS / Ubuntu Desktop) harus dirancang agar **langsung membuka antarmuka presensi layar penuh tanpa bilah peramban (URL bar), tanpa notifikasi sistem, dan tanpa screensaver yang mati saat dibiarkan**.

### 1. Perintah Peluncuran Chromium Kiosk Mode

Gunakan parameter bendera (*flags*) Chromium berikut:

```bash
chromium-browser \
  --kiosk \
  --noerrdialogs \
  --disable-infobars \
  --check-for-update-interval=31536000 \
  --disable-pinch \
  --overscroll-history-navigation=0 \
  --autoplay-policy=no-user-gesture-required \
  --disable-features=Translate \
  http://app.sekolah.sch.id/kiosk
```

| Parameter | Fungsi Operasional |
| :--- | :--- |
| `--kiosk` | Mengunci peramban dalam mode layar penuh murni (tanpa tombol close, navbar, atau menu). |
| `--noerrdialogs` | Mencegah munculnya pop-up dialog error jika sempat terjadi gangguan koneksi. |
| `--disable-infobars` | Menyembunyikan bilah info peringatan ("Chrome sedang dikontrol otomasi"). |
| `--autoplay-policy=no-user-gesture-required` | Mengizinkan peramban membunyikan audio chime presensi tanpa perlu klik awal pengguna. |
| `--overscroll-history-navigation=0` | Mencegah gestur swipe layar sentuh kembali ke halaman sebelumnya (*back navigation*). |

### 2. Konfigurasi Autostart on Boot (Linux X11)

Buat berkas autostart desktop di gawai kiosk:
```bash
mkdir -p ~/.config/autostart
nano ~/.config/autostart/kiosk.desktop
```

Isikan konfigurasi berikut:
```ini
[Desktop Entry]
Type=Application
Name=Presensi Kiosk
Exec=/usr/bin/chromium-browser --kiosk --noerrdialogs --disable-infobars --autoplay-policy=no-user-gesture-required http://app.sekolah.sch.id/kiosk
Hidden=false
NoDisplay=false
X-GNOME-Autostart-enabled=true
```

### 3. Menonaktifkan Screensaver & Hemat Daya Layar

Agar layar monitor kiosk tidak mati saat jam istirahat atau jam sepi:
Tambahkan perintah berikut ke berkas `~/.xsessionrc` atau jalankan via terminal:

```bash
xset s off       # Matikan screensaver
xset -dpms       # Matikan DPMS (Energy Star)
xset s noblank   # Cegah layar menjadi hitam
```

---

## 3. Manajemen Audio Feedback & Kebijakan Autoplay

Respons audio instan sangat krusial bagi siswa dan satpam gerbang untuk mengetahui apakah kartu mereka berhasil diproses tanpa harus membaca layar monitor.

Sistem presensi menghasilkan 3 jenis isyarat audio:
1. **Chime Sukses Masuk**: Nada bersemangat dua ketukan menandakan presensi masuk berhasil dicatat.
2. **Chime Sukses Pulang**: Nada melodi kepulangan sekolah.
3. **Buzzer Peringatan (Error)**: Nada peringatan satu ketukan panjang jika:
   - Siswa sudah melakukan presensi sebelumnya (*sudah absen hari ini*).
   - Kartu tidak terdaftar atau status siswa `mutasi`/`lulus`.
   - Jam presensi belum dibuka atau sudah melewati batas toleransi.

> [!TIP]
> **Mengatasi Masalah Audio Macet di Browser:**
> Peramban web modern secara bawaan memblokir pemutaran suara otomatis (*Audio Autoplay Restriction*). Menambahkan parameter `--autoplay-policy=no-user-gesture-required` pada perintah peluncuran Chromium adalah solusi permanen untuk mengatasi kendala ini.

---

## 4. Mitigasi Gangguan Jaringan (Network Blip) di Pos Gerbang

Pos satpam atau gerbang sekolah sering kali mengalami fluktuasi sinyal Wi-Fi atau kabel LAN yang tersenggol.

### Desain Ketahanan Jaringan di Antarmuka Kiosk
1. **Indikator Status Online/Offline**:
   Antarmuka Kiosk memiliki indikator deteksi jaringan berbasis JavaScript (`navigator.onLine`). Jika internet terputus, layar menampilkan status peringatan 🔴 **Jaringan Terputus**.
2. **Penanganan Pemindaian Saat Offline**:
   Jika siswa memindai kartu saat koneksi terputus, sistem akan membunyikan nada peringatan dan meminta siswa mencoba kembali setelah lampu indikator berubah menjadi 🟢 **Online**.
3. **Kombinasi Akses Intranet Lokal**:
   Jika sekolah menggunakan server fisik lokal di ruang server (bukan cloud VPS), arahkan alamat kiosk ke IP LAN server (contoh: `http://192.168.1.100/kiosk`), sehingga proses presensi gerbang tetap berjalan normal meskipun internet provider (Indihome/Telkom) padam.

---

## 5. Manajemen Sesi WhatsApp Evolution API Gateway

Pengiriman pesan WhatsApp massal dikelola melalui server **Evolution API** yang berkomunikasi via REST API HTTP.

```mermaid
flowchart LR
    A[Laravel App\nWA_BASE_URL:8088] -->|HTTP Post Payload| B[Evolution API Gateway]
    B <-->|WebSocket Persistent| C[Gawai WhatsApp Dinas Sekolah\n(Android / iPhone)]
    C -->|Jaringan Seluler Meta| D[WhatsApp Server Cloud]
    D --> E[Gawai WhatsApp Orang Tua]
```

### 1. Pemeriksaan Status Koneksi Sesi Bot
Buka browser dan akses **Evolution Manager** (atau periksa via cURL):

```bash
curl -X GET "http://10.77.77.21:8088/instance/connectionState/sekolah-bot" \
  -H "apikey: KunciApiRahasiaEvolutionAnda"
```

Respons Status:
- `open`: 🟢 **Bot Aktif & Terhubung.** Pesan siap dikirimkan.
- `connecting`: 🟡 **Sedang Menyambung Ulang.** Terjadi proses handshake socket.
- `close`: 🔴 **Sesi Terputus.** Memerlukan scan QR ulang.

### 2. Prosedur Pairing Ulang (Reconnect QR Code)
Jika sesi terputus (misalnya gawai dinas restart atau session expired):
1. Buka **Evolution Manager** di browser.
2. Pilih instance `sekolah-bot`, klik tombol **Connect / QR Code**.
3. Ambil gawai Android/iPhone nomor dinas sekolah.
4. Buka aplikasi WhatsApp &rarr; menu **Perangkat Tertaut (*Linked Devices*)** &rarr; **Tautkan Perangkat**.
5. Arahkan kamera gawai ke kode QR di layar.
6. Status di Evolution Manager akan berubah menjadi `open`.

---

## 6. Kebijakan Anti-Banned & Perlindungan Nomor WhatsApp Dinas

WhatsApp (Meta) menerapkan algoritma pendeteksi spam yang sangat ketat terhadap nomor yang mengirimkan pesan massal dalam waktu singkat.

### 5 Aturan Emas Mencegah Pemblokiran (*Anti-Banned Policy*)

1. **Gunakan Nomor Dinas yang Berumur (*Warmed-Up Number*)**:
   Jangan pernah menggunakan kartu SIM perdana baru beli untuk gateway massal! Gunakan nomor dinas resmi sekolah yang sudah berumur minimal **3 bulan** dan memiliki riwayat obrolan masuk dan keluar secara alami.
2. **Penerapan Jeda Pengiriman (*Throttling Delay*)**:
   Queue Worker diatur dengan opsi `--sleep=3` pada [`ecosystem.config.cjs`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/ecosystem.config.cjs), sehingga pesan tidak ditembakkan serentak dalam 1 detik, melainkan mengalir dengan ritme manusiawi (jeda 1–3 detik antar pesan).
3. **Format Pesan Informatif & Ramah Ortu**:
   Sertakan identitas resmi sekolah dan kontak humas pada pesan presensi agar orang tua tidak menandai pesan sebagai *Spam/Laporkan*:
   ```text
   Assalamu'alaikum Wr. Wb.
   Pemberitahuan Presensi SMP Negeri 1 Majenang:
   Ananda [Nama Siswa] telah hadir di sekolah pada pukul [06.45 WIB].
   
   -- Layanan Informasi Otomatis Sekolah Terpadu --
   ```
4. **Sediakan Kartu SIM Cadangan (*Failover Backup SIM*)**:
   SysAdmin wajib menyiapkan 1 nomor cadangan yang sudah di-pairing di instance sekunder Evolution API. Jika sewaktu-waktu nomor primer mengalami peninjauan (*review*), admin cukup mengubah variabel `WA_INSTANCE_NAME` di `.env` dalam hitungan detik.
5. **Kepatuhan Pesan Dua Arah**:
   Jika orang tua membalas pesan bot, pastikan sesekali admin sekolah membalas atau membaca chat tersebut agar rasio percakapan dua arah tetap terjaga di server Meta.
