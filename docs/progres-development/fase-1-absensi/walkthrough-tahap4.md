# Tahap 4: Kios Scanner Absensi (Selesai Diimplementasikan)

> [!SUCCESS]
> Semua fitur Kios Scanner Absensi (Tahap 4) telah selesai diimplementasikan sesuai *blueprint* yang ditetapkan.

Berikut adalah ringkasan teknis dari pekerjaan yang baru saja diselesaikan. Anda dapat melakukan pengujian langsung dengan mengakses rute `/scan` di browser Anda.

## 1. Konfigurasi Awal & Skema Database
- **Sesi & Timezone**: `SESSION_LIFETIME` telah dinaikkan menjadi 180 (3 jam) di `.env` untuk mengakomodasi window absensi pagi (06.00-08.00). Timezone aplikasi diasumsikan `Asia/Jakarta`.
- **Tabel Log Absensi**: Tabel `invalid_scan_logs` telah dihapus dan digantikan oleh tabel log umum **`scan_logs`**. Tabel ini akan merekam seluruh percobaan _scan_ (baik sukses maupun gagal), lengkap dengan `barcode_code`, status, dan _IP Address_ (berguna untuk pelacakan *device* di masa depan).
- **Unique Constraint Kehadiran**: Tabel `attendances` (sudah) memiliki struktur valid yang memisahkan antara `date` dan `scan_time`, serta *database-level unique constraint* pada `(student_id, date)` untuk menahan _race condition_ murni.

## 2. Kelas Aksi `ProcessScanAction` (Backend Logic)
Telah dibuat Action class di `app/Actions/ProcessScanAction.php` yang mengatur urutan validasi berikut:
- **Debounce Atomik (Redis/Cache)**: Menggunakan `Cache::add()` selama 3 detik per barcode. Eksekusi ini menahan serangan brutal klik ganda sebelum data menyentuh database.
- **Pencarian Siswa**: Siswa dicari berdasarkan barcode, termasuk verifikasi `barcode_active == true` dan kelas yang didaftarkan.
- **Pengecekan Hari Libur Spesifik Kelas**: Menghindari anomali masuk hari libur. Cek libur dieksekusi dengan query relasi `class_id IS NULL OR class_id = ?`.
- **Status Ganda Wajar (`already_scanned`)**: Dicari melalui *query* `SELECT` murni, dan di-_fallback_ melalui try-catch _Unique Constraint Violation_ (apabila dua _request_ lolos dalam hitungan mikrodetik).
- **Log Kehadiran**: Rekam status dan akumulasi waktu keterlambatan otomatis.

## 3. UI/UX Kios `AttendanceKiosk` (Frontend Alpine.js)
Antarmuka kios dibuat sangat _responsive_ dengan Livewire & Alpine.js (`resources/views/livewire/attendance-kiosk.blade.php`), yang mencakup:
- **Overlay Autoplay Browser**: Menampilkan pesan "_Sentuh Layar Untuk Mengaktifkan Kios_" agar sistem operasi membolehkan pemutaran audio otomatis.
- **Feedback State Machine**: Transisi halus untuk masing-masing status (Sukses, Telat, Gagal, Libur, Network Error) yang memiliki warna ikon dan warna latar belakang yang saling berbeda.
- **Duplicate Request Senyap**: Apabila siswa secara fisik melakukan _scan_ dua kali dengan cepat dalam waktu kurang dari 3 detik, UI *tidak akan berubah* (diabaikan senyap) sehingga mencegah efek _flashing_ dan kebingungan layar.
- **Siklus _Clear Timeout_**: Menerima _scan_ baru meskipun tampilan hasil scan sebelumnya belum usai, *timer reset* akan dibatalkan otomatis dan digantikan oleh hasil yang terbaru.
- **Input Fokus Berkelanjutan**: Fungsi `setInterval()` secara agresif mengembalikan fokus kursor (_autofocus_) ke `input` yang tersembunyi sehingga Kios tidak pernah kehilangan kontrol meskipun di-_klik_ di area luar oleh siswa iseng.

## Instruksi Verifikasi Manual
1. Pastikan menjalankan ulang _migration_ di _environment_ lokal (jika ini proyek baru):
   ```bash
   php artisan migrate
   ```
2. Akses `http://localhost:8000/scan` (atau URL *development* Anda).
3. Lakukan **klik** pada halaman awal Kios untuk mematikan _overlay_.
4. Cobalah **scan barcode**.
5. Simulasikan skenario (Uji dengan *barcode* yang benar, barcode *expired*/tidak aktif, barcode yang salah, hingga mensimulasikan jaringan internet _down_ pada *tab* Anda).

Silakan lakukan pengujian dan verifikasi apakah alur interaktif UI dan Backend di atas terasa solid dan sesuai yang kita konsepkan. Jika semua siap, kita bisa bergerak ke Tahap 5!
