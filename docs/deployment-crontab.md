# Panduan Setup Crontab Server (Laravel Scheduler)

Dokumen ini berisi panduan wajib untuk mengatur **Cron Jobs** pada server *production*. Tanpa konfigurasi ini, fitur otomatisasi sistem (seperti pengiriman Laporan WA Harian per Kelas dan Laporan Rekap Seluruh Sekolah) **TIDAK AKAN PERNAH BERJALAN**.

Laravel memiliki fitur Task Scheduling (di dalam `routes/console.php`), namun fitur tersebut membutuhkan satu pemicu (*trigger*) dari *operating system* Linux yang dipanggil setiap menit.

> [!IMPORTANT]
> Cron job **harus** dipasang menggunakan akun **user yang sama** dengan kepemilikan file Laravel (misal: `smpn3kedungreja`), bukan akun `admin` atau `root`. Jika dipasang di user yang salah, cron berjalan tetapi tidak bisa menulis ke `storage/framework/` dan akan gagal diam-diam.

---

## Command Cron yang Digunakan

```bash
* * * * * /usr/bin/php8.4 /home/smpn3kedungreja/web/app.smpn3kedungreja.sch.id/public_html/artisan schedule:run >> /dev/null 2>&1
```

Ganti path sesuai lokasi instalasi jika server berbeda.

---

## Cara Pasang via HestiaCP (Cara yang Digunakan)

1. Login ke **HestiaCP** menggunakan akun **user aplikasi** (`smpn3kedungreja`) — bukan admin

2. Klik menu **CRON** di navbar atas

3. Klik tombol **ADD CRON JOB**

4. Isi form seperti ini:

   | Field | Nilai |
   |-------|-------|
   | Minute | `*` |
   | Hour | `*` |
   | Day | `*` |
   | Month | `*` |
   | Day of Week | `*` |
   | Command | `/usr/bin/php8.4 /home/smpn3kedungreja/web/app.smpn3kedungreja.sch.id/public_html/artisan schedule:run >> /dev/null 2>&1` |

5. Klik **Save**

6. Tunggu 1-2 menit, lalu buka halaman **Manajemen Notifikasi WA** di admin panel. Banner akan berubah dari 🔴 menjadi 🟢 jika cron sudah berjalan.

---

## Cara Pasang via SSH (Alternatif)

1. Login ke server via SSH sebagai user yang tepat:
   ```bash
   ssh smpn3kedungreja@namaserver.anda
   ```

2. Buka editor crontab:
   ```bash
   crontab -e
   ```

3. Tambahkan baris ini di paling bawah:
   ```bash
   * * * * * /usr/bin/php8.4 /home/smpn3kedungreja/web/app.smpn3kedungreja.sch.id/public_html/artisan schedule:run >> /dev/null 2>&1
   ```

4. Simpan dan keluar:
   - **nano**: `CTRL+O` → Enter → `CTRL+X`
   - **vim**: `Esc` → `:wq` → Enter

5. Verifikasi:
   ```bash
   crontab -l
   ```

---

## Verifikasi Scheduler Sudah Aktif

### Cara 1 — Via UI Admin Panel

Buka halaman **Manajemen Notifikasi WA**. Lihat banner di bagian atas:
- 🟢 **Aktif** = cron berjalan normal
- 🔴 **Tidak Terdeteksi** = cron belum terpasang atau user salah

Banner bekerja dengan membaca file heartbeat di `storage/framework/schedule-heartbeat` yang diperbarui setiap menit oleh scheduler.

### Cara 2 — Via Terminal

```bash
# Cek isi file heartbeat (Unix timestamp)
cat storage/framework/schedule-heartbeat

# Konversi timestamp ke tanggal yang bisa dibaca
date -d @$(cat storage/framework/schedule-heartbeat)

# Jika timestamp kurang dari 2 menit dari sekarang → scheduler aktif
```

### Cara 3 — Jalankan Manual untuk Test

```bash
php8.4 artisan schedule:run
```

Output yang benar:
```
2026-08-11 10:42:49 Running ['artisan' scheduler:heartbeat]  399ms DONE
2026-08-11 10:42:50 Running ['artisan' presensi:send-daily-class-report]  417ms DONE
2026-08-11 10:42:50 Running ['artisan' presensi:send-school-summary]  402ms DONE
```

---

## Command yang Terdaftar di Scheduler

Semua command terdaftar di `routes/console.php`:

| Command | Frekuensi | Fungsi |
|---------|-----------|--------|
| `scheduler:heartbeat` | Setiap menit | Tulis timestamp ke `storage/framework/schedule-heartbeat` untuk deteksi status di UI |
| `presensi:send-daily-class-report` | Setiap menit | Cek cutoff time → jika dalam window 1 jam → kirim laporan harian ke wali kelas |
| `presensi:send-school-summary` | Setiap menit | Cek cutoff time → jika dalam window 1 jam → kirim rekap sekolah ke manajemen |

---

## Setelah Pindah Server / Migrasi Hosting

> [!WARNING]
> Setiap kali aplikasi **dipindahkan ke server baru** atau **hosting baru**, cron job harus dipasang ulang dari awal. Ini sering terlupakan dan menyebabkan laporan WA berhenti terkirim tanpa ada pesan error.

Checklist setelah migrasi:
- [ ] Pasang cron job di server/hosting baru (dengan user yang benar)
- [ ] Pastikan queue worker berjalan (Supervisor / PM2)
- [ ] Hubungkan ulang WhatsApp bot (scan QR di Evolution Manager)
- [ ] Cek banner scheduler di Manajemen Notifikasi WA → harus 🟢

---

*Terakhir diperbarui: 11 Agustus 2026*
