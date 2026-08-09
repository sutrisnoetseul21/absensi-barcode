# Panduan Setup Crontab Server (Laravel Scheduler)

Dokumen ini berisi panduan wajib untuk mengatur **Crontab (Cron Jobs)** pada server *production*. Tanpa konfigurasi ini, fitur otomatisasi sistem (seperti pengiriman Laporan WA Harian per Kelas dan Laporan Rekap Seluruh Sekolah) **TIDAK AKAN PERNAH BERJALAN**.

Laravel memiliki fitur Task Scheduling (di dalam `routes/console.php`), namun fitur tersebut membutuhkan satu pemicu (*trigger*) dari *operating system* Linux yang dipanggil setiap menit.

## Langkah-langkah Setup Crontab

1. **Akses Terminal Server / VPS**
   Login ke server Anda menggunakan SSH. Pastikan Anda login menggunakan *user* yang memiliki akses eksekusi ke folder proyek Laravel Anda (misalnya `www-data`, `ubuntu`, atau *user* panel hosting Anda).

2. **Buka Editor Crontab**
   Jalankan perintah berikut di terminal:
   ```bash
   crontab -e
   ```
   *(Jika ini pertama kalinya Anda menjalankan perintah ini, Anda mungkin akan diminta memilih text editor, pilih `nano` atau `vim`).*

3. **Tambahkan Baris Eksekusi Laravel Scheduler**
   Gulir ke bagian paling bawah file, dan tambahkan satu baris perintah berikut. Pastikan Anda **mengganti `/path-ke-folder-projek-anda`** dengan *path* absolut *folder* aplikasi web Anda di server (misalnya: `/var/www/html/absensi-barcode`).

   ```bash
   * * * * * cd /path-ke-folder-projek-anda && php artisan schedule:run >> /dev/null 2>&1
   ```

   **Contoh nyata jika menggunakan path server Ubuntu/Nginx standar:**
   ```bash
   * * * * * cd /var/www/absensi-barcode && php artisan schedule:run >> /dev/null 2>&1
   ```

4. **Simpan dan Keluar**
   - Jika menggunakan **nano**: Tekan `CTRL + O` (Enter), lalu tekan `CTRL + X` untuk keluar.
   - Jika menggunakan **vim**: Tekan tombol `Esc`, ketik `:wq` lalu tekan Enter.

5. **Verifikasi Instalasi Crontab**
   Pastikan baris yang baru Anda tambahkan benar-benar sudah tersimpan dengan menjalankan perintah:
   ```bash
   crontab -l
   ```
   Perintah ini akan mencetak isi *cron* aktif Anda ke layar terminal.

---

## Bagaimana Cara Memastikan Scheduler Sudah Bekerja?

Setelah *crontab* dipasang, Linux akan menembak perintah `php artisan schedule:run` secara otomatis **setiap 1 menit**.

Anda dapat memeriksa apakah sistem *cron* berjalan normal dengan:
1. Menyimak *log* pengiriman notifikasi WhatsApp di menu admin.
2. Memeriksa file *log* harian Laravel:
   ```bash
   tail -f storage/logs/laravel.log
   ```
   Jika waktunya telah tiba (misalnya pukul 08:00 untuk laporan kelas atau 08:15 untuk laporan sekolah), Anda akan melihat aktivitas *Command* tereksekusi di dalam log tersebut.

> [!WARNING]
> Jangan pernah lupa mengonfigurasi *crontab* ini jika Anda memindahkan aplikasi ke server baru, *hosting* baru, atau melakukan migrasi. Otomatisasi pengiriman laporan harian mutlak bergantung pada 1 baris konfigurasi OS ini!
