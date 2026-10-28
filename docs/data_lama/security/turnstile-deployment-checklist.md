# 📋 Checklist Deployment Cloudflare Turnstile (Admin Panel)

Dokumen ini adalah panduan langkah demi langkah yang **WAJIB** dilakukan sebelum kode keamanan Turnstile di-pull/deploy ke server production agar tidak terkunci (*locked-out*) dari Admin Panel.

---

### 1. Pendaftaran di Cloudflare Turnstile Dashboard
1. Buka [https://dash.cloudflare.com/](https://dash.cloudflare.com/) dan login ke akun Cloudflare.
2. Di sidebar kiri, klik menu **Turnstile**.
3. Klik tombol **Add Site** (Tambah Situs).
4. Isi data form:
   - **Site name**: `Admin Panel SMP Negeri 3 Kedungreja` (atau nama sekolah Anda).
   - **Domain**: Masukkan domain production Anda (contoh: `coba.haflaitsolution.my.id` atau domain utama).
   - **Widget Mode**: Pilih **Managed** (Recommended) atau **Non-Interactive**.
5. Klik **Create** / **Save**.
6. Anda akan mendapatkan 2 buah kunci:
   - **Site Key** (Kunci Publik / Public Key)
   - **Secret Key** (Kunci Rahasia / Private Key)

---

### 2. Konfigurasi di Server Production (`.env`)
> [!IMPORTANT]
> Lakukan langkah ini di server production **SEBELUM** menjalankan `git pull` atau me-reload aplikasi:

1. Buka file `.env` di server production Anda:
   ```bash
   nano .env
   ```
2. Tambahkan kedua baris kunci yang didapat dari Cloudflare:
   ```ini
   TURNSTILE_SITE_KEY=0x4AAAAAA... (Site Key dari Cloudflare)
   TURNSTILE_SECRET_KEY=0x4AAAAAA... (Secret Key dari Cloudflare)
   ```
3. Simpan file `.env`.
4. Jalankan pembersihan cache konfigurasi di server:
   ```bash
   php artisan config:clear
   php artisan optimize:clear
   ```

---

### 3. Deploy Kode & Verifikasi
1. Pull kode terbaru ke server (`git pull origin main`).
2. Bersihkan view cache (`php artisan view:clear`).
3. Buka halaman login admin di browser:
   `https://[domain-anda]/admin/login`
4. Pastikan:
   - Widget Cloudflare Turnstile muncul di bawah form input.
   - Status centang Turnstile berubah menjadi hijau (*success*).
   - Login berhasil masuk ke dashboard admin.
