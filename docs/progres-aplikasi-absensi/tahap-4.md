# TAHAP 4 — Kios Scanner Absensi (High-Speed)

> **Goal**: Halaman kios scan bekerja cepat, async, dengan feedback suara dan visual.
> Ini adalah fitur INTI proyek. Referensi: `06-business-rules.md`.
> **UI**: Background putih, logo sekolah di tengah atas, card input scanner di tengah layar.

- `[x]` **Konfigurasi Awal**:
  - Set `SESSION_LIFETIME=180` (3 jam) di `.env` sebagai buffer aman untuk window operasional 06.00–08.00. Tidak perlu pengecualian CSRF atau keep-alive token.
  - Pastikan timezone aplikasi di `config/app.php` adalah `Asia/Jakarta`.
- `[x]` Buat route **di `routes/web.php`** (Bukan `api.php`):
  - `GET /scan` untuk halaman kios Livewire (akses publik).
  - `POST /scan` untuk endpoint fetch. Pasang middleware `throttle:60,1` untuk proteksi dasar.
- `[x]` Buat Livewire component `AttendanceKiosk`
- `[x]` **Layout UI Kios**:
  - Tampilkan overlay "Sentuh layar untuk mengaktifkan kios" di awal untuk bypass kebijakan *audio autoplay* browser.
  - Background modern (misalnya, perpaduan slate dan blue gradient) dengan elemen glassmorphism. Gunakan atribut `wire:ignore` pada area yang dimanipulasi murni oleh Alpine.js.
  - Logo sekolah di bagian atas tengah.
  - Card besar di tengah layar untuk menampung input dan feedback.
  - Area feedback: foto siswa (dengan placeholder avatar jika null) + nama + status (hijau=hadir, kuning=telat, merah=error, abu-abu/biru=libur).
  - State dan UI khusus jika terjadi **Network/Connection Error** (saat request `fetch()` gagal di-reach).
  - Loading indicator kecil (spinner/dot) yang aktif saat request in-flight.
- `[x]` **Logic Scan (Alpine.js)**:
  - Livewire component `AttendanceKiosk` hanya merender shell/layout awal. Semua interaksi ditangani Alpine.js + `fetch()`. Ambil CSRF dari `<meta name="csrf-token">`.
  - Fokus input robust: `autofocus` di hidden input, global click listener untuk refocus, dan `setInterval` tiap 2 detik untuk auto-refocus.
  - Trigger Submit: Terjadi saat `input.length === 10` **ATAU** event `keydown.enter` (mana yang lebih dulu).
  - Clear input: event `keydown.escape` untuk menghapus manual input.
  - Submit scan HARUS via `fetch()` murni (dengan header `X-CSRF-TOKEN`), BUKAN via `$wire`/Livewire method call.
  - UX Interrupt: Jika ada scan baru masuk sebelum timer reset 3 detik selesai, langsung update tampilan ke hasil baru saat itu juga (`clearTimeout` timer lama lalu render ulang).
- `[x]` **Backend endpoint `POST /scan`** (Action Class `ProcessScanAction`):
  - Server-side debounce atomik: `Cache::add('scan_lock:'.$barcode, true, 3)`. Jika gagal, return `duplicate_request`.
  - Cari `student` berdasarkan `barcode_code` (Eloquent otomatis exclude soft-deleted). Ambil `class_id` siswa dari relasi enrollment aktif.
  - Jika tidak ditemukan → return `{status: 'not_found'}`.
  - Jika `barcode_active == false` → return `{status: 'barcode_inactive'}`.
  - Cek hari ini libur atau tidak (`holidays` table). Query wajib mengecek: `(class_id IS NULL OR class_id = student_class_id)`. Jika libur → return `{status: 'holiday'}`.
  - Cek normal (SELECT) sudah absen hari ini (`attendances`). Jika ada → return `{status: 'already_scanned'}`.
  - Simpan ke `attendances`. Pastikan struktur tabel memisahkan kolom `date` dan `scan_time`. Pasang try-catch DB unique constraint `(student_id, date)` sebagai *safety net* race condition murni.
  - Hitung `late_minutes` berdasarkan `scan_time` vs jam batas masuk.
  - Logging Audit: Simpan jejak semua percobaan scan ke satu tabel log umum (`scan_logs`) dengan kolom minimal: `barcode_code`, `student_id` (nullable), `status`, `scan_time`, dan `ip_address`.
  - Return JSON.
- `[x]` **Audio Feedback & UI Handling**:
  - File audio pendek (`.mp3`/`.wav`) di-preload via tag `<audio>`.
  - Suara *beep* sukses: untuk status `success_on_time`, `success_late`.
  - Suara *buzz* error: untuk status `not_found`, `already_scanned`, `barcode_inactive`.
  - Suara *netral/chime*: untuk status `holiday`.
  - Suara *siren/terputus*: untuk status network error.
  - Status `duplicate_request` wajib **diabaikan sepenuhnya secara senyap** di frontend (tidak merubah UI, tidak bersuara) karena ini hanya masalah fisik scanner yang membaca ganda.

**Catatan Operasional & Technical Debt:** 
- Device kios **wajib dibuka/refresh browser setiap pagi** sebelum jam 06.00 (bukan mengandalkan tab yang menyala dari hari sebelumnya). Ini prasyarat supaya session/CSRF token selalu valid tanpa perlu mekanisme refresh otomatis.
- Route `/scan` untuk saat ini **belum diproteksi PIN/re-auth kios** (sesuai business rules "Login & Akses"). Ini harus diselesaikan sebelum go-live production.

**Verifikasi Tahap 4 Selesai:**
- [x] Overlay 'sentuh untuk aktifkan' muncul sekali di awal, dan suara berfungsi normal setelah disentuh (bypass autoplay policy).
- [x] Scan kartu → muncul nama + foto + status dalam < 1 detik.
- [x] Scan 5 kartu berurutan cepat → semua masuk ke database tanpa lag.
- [x] Barcode sama discan 2x dalam 3 detik → hanya 1 record tersimpan, dan respon ganda ditangani secara senyap tanpa bunyi/error UI.
- [x] Barcode tidak terdaftar → muncul peringatan & log tersimpan.
- [x] Tes libur (nasional vs kelas) → siswa ditolak logikanya secara spesifik jika kena libur kelasnya.
- [x] Tes 2 device berbeda scan barcode yang sama dalam window 3 detik → hanya 1 record tersimpan (validasi server-side debounce + unique constraint).
- [x] Tes scan baru masuk sebelum timer reset 3 detik selesai → tampilan langsung update, tidak nunggu.
- [x] Tes fokus input tetap kembali otomatis setelah klik di luar area kios.

**Status: Selesai ✅**
