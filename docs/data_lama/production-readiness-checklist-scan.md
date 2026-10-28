# Production Readiness Checklist — Scan Barcode (Presensi/Kiosk)

Dokumen ini digunakan untuk verifikasi kesiapan production sebelum deploy fitur scan barcode
(`ProcessScanAction.php`) dan setiap kali menambah jumlah perangkat kiosk baru.

Terkait: `app/Actions/ProcessScanAction.php`

---

## 1. Redis (Cache Lock Layer)

Redis dipakai untuk `Cache::add('scan_lock:{barcode}', true, 3)` — debounce level pertama sebelum
request menyentuh database.

- [ ] Redis service **aktif** dan **auto-restart** (systemd `Restart=always` atau Docker
      `restart: unless-stopped`)
- [ ] Cek `CACHE_DRIVER` di `.env` production benar-benar `redis`, bukan `file`/`database`
      (kalau salah, lock jadi tidak efektif lintas proses PHP-FPM)
- [ ] Uji **fallback behavior** kalau Redis down:
  ```bash
  # Simulasikan Redis mati, lalu coba scan — apakah error fatal atau tetap jalan (tanpa lock)?
  sudo systemctl stop redis
  ```
  - Jika `Cache::add()` melempar exception saat Redis mati → **wajib** dibungkus try-catch di
    `ProcessScanAction.php` supaya tidak jadi 500, minimal fallback "lanjut tanpa lock" dengan log
    warning.
- [ ] Set `maxmemory-policy` di `redis.conf` (mis. `allkeys-lru`) supaya Redis tidak OOM kalau lupa
      di-flush
- [ ] Pastikan Redis **tidak dipakai bareng** untuk session + queue + cache tanpa prefix terpisah
      (`CACHE_PREFIX` di `.env`) — hindari key collision antar fitur
- [ ] Monitoring dasar: `redis-cli INFO memory` dan `redis-cli INFO clients` dicek berkala
      (jumlah `connected_clients` tidak boleh mendekati `maxclients`)

## 2. Database Locking (`lockForUpdate`)

- [ ] Pastikan kolom `student_id` + `date` di tabel `presensi` punya **index** (idealnya composite
      unique index) — tanpa index, `lockForUpdate()` bisa lock lebih dari 1 baris (row lock jadi
      gap/table lock) saat volume tinggi
- [ ] Cek `innodb_lock_wait_timeout` (MySQL/MariaDB) tidak default terlalu lama (default 50 detik
      bisa bikin request kiosk menggantung lama kalau ada deadlock)
- [ ] Cek isolation level koneksi Laravel: default `REPEATABLE READ` (MySQL) sudah cukup aman untuk
      pola ini, tidak perlu diubah ke `SERIALIZABLE`
- [ ] Load test dengan skenario **barcode sama** dari beberapa device bersamaan (bukan cuma
      barcode beda-beda) untuk memverifikasi `lockForUpdate` benar-benar antre, bukan race

## 3. PHP-FPM Capacity

Ini titik rawan sebenarnya untuk beban banyak kiosk, bukan logic lock-nya.

- [ ] Hitung `pm.max_children` berdasarkan RAM VPS:
  ```
  pm.max_children = (RAM tersedia untuk PHP-FPM) / (rata-rata RAM per proses PHP-FPM)
  ```
  Cek rata-rata RAM per proses dengan:
  ```bash
  ps aux | grep php-fpm | awk '{sum+=$6; count++} END {print sum/count/1024 " MB avg"}'
  ```
- [ ] `pm.max_children` **minimal** = jumlah kiosk device + buffer untuk traffic portal lain
      (siswa/guru/admin) yang jalan di server yang sama
- [ ] Set `pm = dynamic` dengan `pm.start_servers`, `pm.min_spare_servers`,
      `pm.max_spare_servers` proporsional, bukan `pm = static` kecuali server didedikasikan khusus
      endpoint scan
- [ ] Cek log `slow.log` PHP-FPM aktif (`slowlog` + `request_slowlog_timeout`) untuk mendeteksi
      request scan yang macet menunggu lock

## 4. Unique Constraint (Lapis Terakhir)

- [ ] Pastikan **unique constraint** aktual ada di skema DB (`student_id`, `date`) — bukan cuma
      diasumsikan dari kode aplikasi. Cek dengan:
  ```sql
  SHOW INDEX FROM presensi WHERE Key_name != 'PRIMARY';
  ```
- [ ] Uji pesan error yang ditangkap di catch block (`duplicate entry` / `unique constraint`)
      cocok dengan **driver DB production** (MySQL vs PostgreSQL beda format pesan error — kalau
      production pakai PostgreSQL tapi testing di MySQL, string match bisa gagal)

## 5. Monitoring & Alerting (Opsional tapi Disarankan)

- [ ] Log setiap `duplicate_request` dan `already_scanned` dengan timestamp + device ID (kalau ada)
      untuk audit kalau ada komplain "scan saya tidak masuk"
- [ ] Alert sederhana (mis. via WhatsApp gateway yang sedang dibangun) kalau ada **spike error 500**
      di endpoint scan dalam rentang waktu pendek — indikasi Redis/DB bermasalah saat jam sibuk
      absensi (pagi hari)

## 6. Load Test Sebelum Go-Live

- [ ] Simulasikan N device sesuai jumlah kiosk real + margin 2x, barcode **campuran** (ada yang
      sama, ada yang beda), pakai tool ringan seperti `k6` atau `hey`:
  ```bash
  hey -n 200 -c 10 -m POST -d '{"barcode":"..."}' https://domain/portal-presensi/scan
  ```
- [ ] Verifikasi tidak ada data ganda masuk setelah load test (query manual ke tabel `presensi`)
- [ ] Verifikasi response time endpoint scan tetap di bawah ambang wajar (idealnya < 500ms) saat
      beban puncak

---

**Catatan:** Checklist ini fokus ke concurrency & infra readiness. Untuk hardening keamanan
endpoint (rate limiting per IP, auth token kiosk, dsb) buat dokumen terpisah.
