# 💾 Prosedur Backup & Restore Basis Data

Dokumen ini menjelaskan tata cara pencadangan (*backup*) dan pemulihan (*restore*) database MySQL secara aman.

---

## 1. Prosedur Backup Database

Backup database dilakukan dengan perintah `mysqldump`. Pastikan menyertakan flag `--no-tablespaces` untuk menghindari kendala *privilege* tabel MySQL 8:

```bash
mysqldump --no-tablespaces -h 127.0.0.1 -u [DB_USER] -p[DB_PASSWORD] [DB_NAME] > database/backups/backup_[DB_NAME]_$(date +%Y-%m-%d_%H-%M-%S).sql
```

> [!NOTE]
> File `.sql` di dalam folder `database/backups/` telah dikecualikan oleh file `.gitignore` agar tidak terunggah ke repositori Git publik.

---

## 2. Prosedur Restore Database

Untuk mengembalikan data dari file backup `.sql`:

```bash
mysql -h 127.0.0.1 -u [DB_USER] -p[DB_PASSWORD] [DB_NAME] < database/backups/[NAMA_FILE_BACKUP].sql
```

Setelah restore selesai, bersihkan cache aplikasi:
```bash
php artisan optimize:clear
```
