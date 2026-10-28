# 🛠️ Pemeliharaan Server & Troubleshooting

Dokumen ini berisi panduan penanganan kendala umum, pembersihan cache, dan prosedur pasca reboot server.

---

## 1. Pembersihan & Optimasi Cache Laravel

Setiap kali melakukan pembaruan kode, file template, atau migrasi database, jalankan perintah:

```bash
php artisan optimize:clear
```

Perintah ini akan membersihkan cache:
- Konfigurasi (`config`)
- Rute (`routes`)
- View Blade (`views`)
- Icon Blade & Filament metadata

---

## 2. Prosedur Pasca Reboot Server

Saat server fisik atau VPS mengalami restart:
1. Pastikan service MySQL aktif:
   ```bash
   sudo systemctl status mysql
   ```
2. Pastikan Web Server (Nginx / Apache / PHP-FPM) aktif:
   ```bash
   sudo systemctl status php8.2-fpm
   sudo systemctl status nginx
   ```
3. Periksa status crontab scheduler:
   ```bash
   crontab -l
   ```
4. Pastikan storage link tetap terhubung:
   ```bash
   php artisan storage:link
   ```

---

## 3. Pengecekan Log Error
- **Laravel Log**: `storage/logs/laravel.log`
- Memantau log secara real-time:
  ```bash
  tail -f -n 50 storage/logs/laravel.log
  ```
