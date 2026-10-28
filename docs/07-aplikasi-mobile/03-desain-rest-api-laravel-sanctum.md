# 🔌 Desain REST API & Autentikasi Laravel Sanctum

Dokumen ini merinci rancangan endpoint REST API di backend Laravel (`routes/api.php`) yang akan dikonsumsi oleh aplikasi mobile Flutter.

---

## 1. Mekanisme Autentikasi (Laravel Sanctum)

1. Siswa/Guru melakukan login via endpoint:
   `POST /api/v1/auth/login` dengan payload:
   ```json
   {
     "identifier": "3142700891", // Bisa berupa NISN, NIP, atau Email
     "password": "password123",
     "device_name": "Samsung SM-A536B (Flutter App)"
   }
   ```
2. Backend memvalidasi kredensial pengguna via tabel `users`.
3. Jika berhasil, server mengembalikan **Personal Access Token (Sanctum)**:
   ```json
   {
     "success": true,
     "message": "Login berhasil",
     "data": {
       "token": "1|qXy...sanctum_token...",
       "user": {
         "id": "uuid-xxx",
         "name": "Adzkia Ulfatunnisa",
         "role": "siswa", // atau "guru" / "wali_kelas"
         "photo_url": "https://domain.sch.id/storage/siswa-photos/adzkia.jpg"
       }
     }
   }
   ```
4. Setiap request berikutnya wajib menyertakan HTTP Header:
   `Authorization: Bearer <token>`

---

## 2. Struktur Format Response Baku (Standard JSON Envelope)

Semua endpoint API mengembalikan format response yang seragam:

```json
{
  "success": true,
  "message": "Deskripsi pesan status",
  "data": { ... },       // Objek atau array data utama
  "meta": { ... }        // Opsional: informasi paginasi, versi API, dsb.
}
```

Format jika terjadi error validasi (HTTP 422):
```json
{
  "success": false,
  "message": "Validasi formulir gagal",
  "errors": {
    "lampiran": ["File lampiran wajib berupa gambar (JPG/PNG)."]
  }
}
```

---

## 3. Daftar Endpoint API yang Direncanakan

### 🔐 A. Otentikasi & Profil Pengguna (`/api/v1/auth`)
| Method | Endpoint | Fungsi |
| :--- | :--- | :--- |
| `POST` | `/api/v1/auth/login` | Login siswa atau guru, menerbitkan token Sanctum |
| `GET` | `/api/v1/auth/me` | Mengambil data profil user yang sedang aktif |
| `POST` | `/api/v1/auth/logout` | Menghapus token aktif di server |
| `POST` | `/api/v1/auth/fcm-token` | Menyimpan FCM Device Token untuk pengiriman push notification |

---

### 🎒 B. Layanan Siswa (`/api/v1/siswa`)
| Method | Endpoint | Fungsi |
| :--- | :--- | :--- |
| `GET` | `/api/v1/siswa/dashboard` | Status kehadiran hari ini, sholat hari ini, jadwal |
| `GET` | `/api/v1/siswa/kartu` | Detail kartu pelajar & data barcode string (`nisn`) |
| `GET` | `/api/v1/siswa/presensi/riwayat` | Riwayat presensi bulanan (parameter: `month`, `year`) |
| `GET` | `/api/v1/siswa/izin` | Daftar pengajuan izin siswa yang pernah dibuat |
| `POST` | `/api/v1/siswa/izin` | Mengirim pengajuan izin/sakit baru (multipart/form-data) |
| `POST` | `/api/v1/siswa/spikap` | Mengirim aduan/curhat perlindungan siswa |

---

### 👨‍🏫 C. Layanan Guru / Wali Kelas (`/api/v1/guru`)
| Method | Endpoint | Fungsi |
| :--- | :--- | :--- |
| `GET` | `/api/v1/guru/classes` | Daftar rombel/kelas yang diampu guru |
| `GET` | `/api/v1/guru/presensi/today` | Daftar kehadiran siswa di kelas binaan hari ini |
| `POST` | `/api/v1/guru/presensi/update`| Ubah status presensi siswa (hadir, sakit, izin, alpa) |
| `GET` | `/api/v1/guru/sholat-dhuhur/today` | Daftar siswa Muslim kelas binaan untuk sholat |
| `POST` | `/api/v1/guru/sholat-dhuhur/bulk`| Simpan presensi sholat berjamaah massal |
| `GET` | `/api/v1/guru/izin/pending` | Daftar pengajuan izin siswa yang butuh persetujuan |
| `POST` | `/api/v1/guru/izin/{id}/approve` | Menyetujui atau menolak izin siswa |
| `POST` | `/api/v1/guru/jurnal/create` | Simpan jurnal mengajar pertemuan harian |
