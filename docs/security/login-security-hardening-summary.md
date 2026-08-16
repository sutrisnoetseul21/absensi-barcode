# 🛡️ Dokumentasi Penguatan Keamanan Sistem Login (Security Hardening Summary)

Dokumen ini merangkum seluruh arsitektur dan perubahan teknis yang telah diterapkan pada sistem autentikasi Sistem Informasi & ERP Sekolah untuk menangkal bot, password spraying, kebocoran state, dan pembajakan sesi.

---

## 1. Ringkasan 4 Pilar Penguatan Keamanan

### Pilar 1: Math Captcha Sisi Server (Portal Siswa & Guru)
* **File Utama**: `app/Livewire/Auth/UnifiedLogin.php`, `resources/views/livewire/auth/unified-login.blade.php`
* **Masalah Awal**: Nilai jawaban penjumlahan sebelumnya rentan terbaca pada JSON snapshot Livewire (`wire:snapshot`).
* **Solusi yang Diterapkan**:
  - Jawaban penjumlahan benar **disimpan secara eksklusif di Server Session** (`session(['unified_login_captcha' => $num1 + $num2])`).
  - Properti Livewire `$captcha_answer` hanya berfungsi menampung input user.
  - Validasi membandingkan input user langsung dengan data session.
  - Nilai diacak ulang (*regenerated*) pada **seluruh jalur kegagalan** (salah captcha, salah password, user tak terdaftar, rate limit).

---

### Pilar 2: Cloudflare Turnstile dengan Fallback Otomatis (Admin Panel)
* **File Utama**: `app/Filament/Pages/Auth/CustomLogin.php`, `resources/views/filament/pages/auth/custom-login.blade.php`, `app/Services/TurnstileService.php`
* **Fitur & Mekanisme**:
  - **Dual Mode (Zero-Config Ready)**:
    - Jika `TURNSTILE_SITE_KEY` kosong (misal instalasi baru / local dev) $\rightarrow$ sistem otomatis menggunakan **Math Captcha Session-based** tanpa perlu setup apapun.
    - Jika `TURNSTILE_SITE_KEY` diisi $\rightarrow$ sistem otomatis mengaktifkan widget **Cloudflare Turnstile**.
  - **Fail-Closed Verification**: Jika verifikasi ke Cloudflare timeout (5 detik) atau respon gagal, login ditolak (`return false`).
  - **Bebas Race Condition Script**: Memanfaatkan `?onload=onloadTurnstileCallback&render=explicit` dengan callback siap sebelum tag script dieksekusi.
  - **Fallback Timeout 8 Detik**: Memberi pesan jelas jika script Cloudflare diblokir oleh firewall jaringan lokal sekolah.
  - **Single-Use Token Reset**: Event Livewire `reset-turnstile` otomatis mereset widget pada setiap kegagalan submit.

---

### Pilar 3: Secondary Rate Limiter Berbasis Username Unik per IP (Lab-Friendly Spray Protection)
* **File Utama**: `app/Livewire/Auth/UnifiedLogin.php`
* **Konteks Kebutuhan**: Mencegah serangan *Password Spraying* tanpa mengunci lab komputer sekolah (dimana 30+ siswa login bersamaan dari 1 IP publik NAT).
* **Solusi yang Diterapkan**:
  - **Dua Lapis Rate Limiting**:
    1. *Limiter 1 (Akun Individu)*: Maksimal 5 percobaan per kombinasi `IP + Username`.
    2. *Limiter 2 (Spray Detector per IP)*: Menghitung jumlah **username gagal unik** dalam window 10 menit.
  - **Ambang Batas (Threshold)**: **30 username gagal unik per IP**. Siswa yang login sukses **tidak** dihitung ke dalam kuota kegagalan.
  - **Atomic Lock Bebas Race Condition**: Menggunakan `Cache::lock('login_spray_lock:' . $ip, 5)->block(3, ...)` untuk mencegah lost-update saat konkurensi tinggi.
  - **Fail-Open Lock Exception**: Jika lock timeout (`LockTimeoutException`), sistem mencatat log dan tetap memproses login normal (*fail-open*) agar siswa tidak terkena error 500.
  - **Log Throttling**: Menggunakan flag `login_spray_logged:{$ip}` (TTL 10 menit) agar `Log::warning()` hanya dicatat **1 kali per IP per window**.

---

### Pilar 4: Konfigurasi Keamanan Session & Cookie
* **File Utama**: `.env.example`, `config/session.php`
* **Kebijakan Environment**:
  - `SESSION_HTTP_ONLY=true` (Mencegah XSS membaca session cookie).
  - `SESSION_SAME_SITE=lax` (Mitigasi CSRF).
  - `SESSION_SECURE_COOKIE`:
    - Di `.env.example` diset default `false` agar development lokal / intranet HTTP tidak mengalami silent login failure.
    - Direkomendasikan diaktifkan menjadi `true` pada `.env` server production yang sudah Full HTTPS.

---

## 2. Daftar File yang Diubah & Dibuat

| Status | Lokasi File | Deskripsi Perubahan |
| :---: | :--- | :--- |
| **NEW** | `app/Services/TurnstileService.php` | Service HTTP client untuk validasi token Turnstile (Fail-Closed, timeout 5s). |
| **NEW** | `docs/security/turnstile-deployment-checklist.md` | Panduan langkah setup Cloudflare Turnstile sebelum deploy ke production. |
| **NEW** | `docs/security/login-security-hardening-summary.md` | Dokumen arsitektur lengkap penguatan keamanan login. |
| **MODIFY** | `app/Livewire/Auth/UnifiedLogin.php` | Implementasi Session Captcha, Pre-Check Spray Limiter, dan Atomic Lock Failure Recorder. |
| **MODIFY** | `app/Filament/Pages/Auth/CustomLogin.php` | Dual-mode login (Turnstile opsional + Fallback Math Captcha) & Single-use Token Reset. |
| **MODIFY** | `resources/views/filament/pages/auth/custom-login.blade.php` | UI dual-mode (Turnstile explicit render / Math Captcha box) + Fallback timeout 8s. |
| **MODIFY** | `resources/views/livewire/auth/unified-login.blade.php` | UI Math Captcha terpadu untuk Portal Siswa & Guru. |
| **MODIFY** | `config/services.php` | Penambahan konfigurasi key `turnstile.site_key` & `turnstile.secret_key`. |
| **MODIFY** | `.env.example` | Penambahan placeholder Turnstile dan dokumentasi `SESSION_SECURE_COOKIE`. |
