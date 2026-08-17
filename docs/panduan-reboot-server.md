# Panduan Restart (Reboot) Server dan Pemulihan Aplikasi

Panduan ini berisi langkah-langkah aman untuk me-*restart* (reboot) server serta tindakan yang harus dilakukan setelah server kembali menyala untuk memastikan aplikasi `app.smp1sampang.sch.id` berjalan normal.

## 1. Cara Melakukan Reboot Server

Jika Anda perlu memulai ulang server, pastikan Anda masuk ke terminal SSH dan jalankan perintah berikut:

```bash
sudo reboot
```
*Catatan: Anda akan otomatis terputus dari terminal SSH. Tunggu sekitar 1-3 menit sampai server kembali menyala.*

---

## 2. Apa yang Terjadi Setelah Server Menyala?

Karena aplikasi ini berbasis **Laravel (PHP-FPM + Web Server Nginx/Apache)** serta menggunakan **Supervisor** dan **Cron**, pada umumnya **aplikasi akan otomatis berjalan tanpa perlu Anda hidupkan secara manual.**

Berikut komponen yang akan jalan secara otomatis:
- **Web Server & PHP**: Otomatis menyala dan langsung melayani akses ke website.
- **Database (MySQL/MariaDB)**: Otomatis menyala.
- **Cron Job**: Otomatis kembali mengeksekusi *scheduler* Laravel setiap menit.
- **Supervisor**: Otomatis menyala dan menjalankan pekerja antrean (*queue worker*).

---

## 3. Pengecekan Pasca-Reboot (Penting!)

Meskipun serba otomatis, Anda disarankan melakukan pengecekan berikut setelah server *online* kembali untuk memastikan tidak ada layanan yang *nyangkut*:

### A. Cek Status Antrean (Supervisor & Queue)
Masuk ke folder utama aplikasi:
```bash
cd /home/smp1sampang/web/app.smp1sampang.sch.id/public_html
```

Jalankan *script* pengecekan yang sudah ada:
```bash
./status-queue.sh
```
Pastikan status pekerja antrean (`smp1sampang-worker:*`) adalah **RUNNING**.

### B. Jika Antrean Error / Tidak Berjalan
Jika dari hasil cek di atas ternyata statusnya berhenti atau *error*, Anda bisa merestart ulang *queue* menggunakan *script*:
```bash
./restart-queue.sh
```

### C. Cek Fungsional Website
1. Buka browser dan akses `https://app.smp1sampang.sch.id`
2. Coba lakukan *login*.
3. Jika tiba-tiba terjadi *Error 500* atau tampilan acak-acakan (jarang terjadi tapi mungkin akibat *cache*), jalankan pembersihan *cache*:
```bash
php8.3 artisan optimize:clear
php8.3 artisan optimize
```

---

## Ringkasan 
1. `sudo reboot`
2. Tunggu nyala, lalu `cd /home/smp1sampang/web/app.smp1sampang.sch.id/public_html`
3. Cek antrean: `./status-queue.sh`
4. (Opsional jika antrean macet) `./restart-queue.sh`
5. Aplikasi siap digunakan kembali!
