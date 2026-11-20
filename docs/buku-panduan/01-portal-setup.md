# 🛠️ Bab 01: Portal Setup & Instalasi Awal (`/portal-setup`)

**Portal Setup & Instalasi** adalah titik awal wajib sebelum sekolah mulai menginput data guru, data siswa, maupun menjalankan presensi harian.

- **Alamat URL**: `http://10.66.66.14:8001/portal-setup`
- **Hak Akses**: `super_admin` atau `admin_portal_setup`

---

## 1. Dashboard Ringkasan Setup (`/portal-setup`)

Halaman utama Portal Setup menampilkan **Checklist Kesiapan Awal Sistem** dengan indikator progres **0% hingga 100%** (5 Langkah Utama):

1. **Inisialisasi Sistem (*Jika Muncul Peringatan Kuning*)**:
   - Apabila terlihat banner *"Role & Pengaturan Inti Sistem Belum Terpasang"*, klik tombol **`Inisialisasi Sistem Sekarang`** satu kali untuk memasang role bawaan (`siswa`, `wali_kelas`, role admin modul) dan pengaturan dasar presensi/perpustakaan.
2. **Pantau 5 Kartu Status Checklist**:
   - **Langkah 1**: Identitas & Jenjang Sekolah (`OK` / `Belum Lengkap`)
   - **Langkah 2**: Tahun Ajaran Aktif (`OK` / `Belum Lengkap`)
   - **Langkah 3**: Master Kelas (`OK` / `Belum Lengkap`)
   - **Langkah 4**: Master Jabatan (`Terkunci` sampai Langkah 1–3 selesai)
   - **Langkah 5**: Master Mata Pelajaran (`Terkunci` sampai Langkah 1–3 selesai)
3. Klik **`Mulai Setup Sekarang`** (atau **`Kelola Pengaturan Dasar`**) untuk masuk ke `/portal-setup/dasar`.

| 🖥️ Mode Web (Desktop) | 📱 Mode HP (Mobile) |
| :---: | :---: |
| ![Dashboard Portal Setup Web](assets/bab-01-portal-setup/web-01-dashboard.png) | ![Dashboard Portal Setup HP](assets/bab-01-portal-setup/hp-01-dashboard.png) |

---

## 2. Setting Dasar 3 Tab (`/portal-setup/dasar`)

Halaman ini memiliki **3 Tab** yang harus diselesaikan secara berurutan. Tab 2 (*Tahun Ajaran*) dan Tab 3 (*Master Kelas*) otomatis terkunci sampai **Tab 1** selesai disimpan.

### A. Tab 1 — Identitas, Jenjang & Status Sekolah (`?tab=sekolah`)

1. **Pilih Jenjang Sekolah**: Klik salah satu kartu:
   - **SD / MI** *(Kelas 1 – 6)*
   - **SMP / MTs** *(Kelas 7 – 9)*
   - **SMA / MA** *(Kelas X – XII)*
   - **SMK / MAK** *(Kelas X – XIII + Jurusan/Kompetensi Keahlian)*
2. **Pilih Status Sekolah**:
   - **Sekolah Negeri** *(label kepegawaian otomatis memakai NIP)*
   - **Sekolah Swasta / Yayasan** *(label kepegawaian otomatis memakai NIY / NUPTK)*
3. **Isi Data Sekolah**:
   - **Nama Sekolah*** *(wajib)* & **Alamat Lengkap Sekolah*** *(wajib)*
   - **Kota / Kabupaten**, **Email Resmi Sekolah**, **Telepon Sekolah**
   - **Nama Kepala Sekolah** & **NIP / NIY Kepala Sekolah**
4. **Unggah Aset Visual** *(opsional, disarankan)*:
   - **Logo Sekolah** (PNG/JPG, maks. 2MB)
   - **Logo Kabupaten / Pemda / Yayasan** (tampil di Kop Surat & Kartu OSIS)
   - **Tanda Tangan Kepala Sekolah** (format PNG transparan)
5. Klik **`Simpan Identitas Sekolah`**. Begitu tersimpan, Tab 2, Tab 3, dan portal operasional lainnya langsung terbuka.

| 🖥️ Mode Web (Desktop) | 📱 Mode HP (Mobile) |
| :---: | :---: |
| ![Setup Identitas Sekolah Web](assets/bab-01-portal-setup/web-02-setup-dasar-sekolah.png) | ![Setup Identitas Sekolah HP](assets/bab-01-portal-setup/hp-02-setup-dasar-sekolah.png) |

---

### B. Tab 2 — Tahun Ajaran Aktif (`?tab=tahun-ajaran`)

1. Di panel kiri **Tambah Tahun Ajaran**:
   - Isi **Tahun Mulai** (contoh: `2026`) dan **Tahun Selesai** (contoh: `2027`).
   - Periksa **Preview Label** (`2026/2027`).
   - Pastikan centang **`Langsung jadikan Tahun Ajaran Aktif`** aktif bila tahun tersebut langsung dipakai.
   - Klik **`+ Simpan Tahun Ajaran`**.
2. Di panel kanan **Daftar Tahun Ajaran**:
   - Hanya boleh ada **1 Tahun Ajaran** berstatus **Aktif** dalam satu waktu.
   - Gunakan tombol **`Jadikan Aktif`** untuk mengganti tahun ajaran berjalan, atau **`Hapus`** pada tahun arsip yang tidak terpakai.
3. Klik **`Lanjut: Master Kelas →`** untuk berpindah ke Tab 3.

| 🖥️ Mode Web (Desktop) | 📱 Mode HP (Mobile) |
| :---: | :---: |
| ![Setup Tahun Ajaran Web](assets/bab-01-portal-setup/web-03-setup-dasar-tahun-ajaran.png) | ![Setup Tahun Ajaran HP](assets/bab-01-portal-setup/hp-03-setup-dasar-tahun-ajaran.png) |

---

### C. Tab 3 — Master Kelas (`?tab=kelas`)

Anda bisa membuat kelas sekaligus secara massal maupun satu per satu:

1. **Generate Massal Kelas** *(Cara Tercepat)*:
   - Pilih **Tingkat** (misal Kelas `7`, `8`, atau `9`).
   - Pilih **Format Tingkat** (`Angka: 7, 10` atau `Romawi: VII, X`).
   - *(Khusus SMA/SMK)* Isi kolom **Jurusan / Kompetensi Keahlian** (contoh: `TKJ`, `RPL`, `MIPA`).
   - Pilih **Pola Akhiran** (`Huruf: A, B, C...` atau `Angka: 1, 2, 3...`) dan isi **Jumlah Rombel** (contoh: `3`).
   - Periksa **Preview Nama Kelas** (misal `7A`, `7B`, `7C`), lalu klik **`Generate 3 Kelas Sekaligus`**.
2. **Tambah Kelas Satuan**: Gunakan panel kiri bawah bila ingin menambahkan nama kelas khusus secara manual.
3. **Kelola Daftar Kelas**: Di panel kanan, kelas dikelompokkan berdasarkan tingkat. Gunakan tombol **`Edit`** atau **`Hapus`** bila diperlukan.

| 🖥️ Mode Web (Desktop) | 📱 Mode HP (Mobile) |
| :---: | :---: |
| ![Setup Master Kelas Web](assets/bab-01-portal-setup/web-04-setup-dasar-kelas.png) | ![Setup Master Kelas HP](assets/bab-01-portal-setup/hp-04-setup-dasar-kelas.png) |

---

## 3. Master Jabatan Sekolah (`/portal-setup/jabatan`)

Menu ini terbuka setelah **Identitas Sekolah, Tahun Ajaran Aktif, dan Master Kelas** selesai (`OK`).

1. Di panel kiri **Sinkron Jabatan Standar**:
   - Periksa daftar jabatan standar sesuai jenjang sekolah (contoh: *Kepala Sekolah, Waka Kurikulum, Waka Kesiswaan, Waka Sarpras, Waka Humas, Guru BK, Operator, Tata Usaha, Pustakawan, Pembina Ekstrakurikuler, Kepala Lab*).
   - Klik **`Sinkronkan Jabatan Standar SMP`** untuk menambahkan seluruh jabatan baku secara otomatis tanpa duplikasi.
2. **Tambah Jabatan Tambahan** *(opsional)*:
   - Ketik nama jabatan khusus (contoh: *Bendahara BOS*, *Kaprog TKJ*), lalu klik **`+ Tambah Jabatan`**.
3. **Catatan Jabatan Inti Sistem**:
   - Jabatan berlabel **Inti Sistem** (seperti *Kepala Sekolah* dan *Guru BK*) diproteksi agar integrasi notifikasi WhatsApp dan SPIKAP tetap berfungsi.
   - *Wali Kelas* tidak dibuat di sini, melainkan diatur per kelas pada Tahun Ajaran aktif di Portal Akademik (`/portal-akademik/kelas`).

| 🖥️ Mode Web (Desktop) | 📱 Mode HP (Mobile) |
| :---: | :---: |
| ![Master Jabatan Web](assets/bab-01-portal-setup/web-05-master-jabatan.png) | ![Master Jabatan HP](assets/bab-01-portal-setup/hp-05-master-jabatan.png) |

---

## 4. Master Mata Pelajaran (`/portal-setup/mapel`)

Langkah ke-5 sebelum mengisi data Guru dan Pembelajaran Kelas:

1. Di panel kiri **Sinkron Mapel Standar**:
   - Klik tombol **`Sinkronkan Mapel Standar`** untuk mengisi seluruh mata pelajaran baku sesuai jenjang sekolah (contoh SMP: 11 mapel termasuk *PAI, PPKn, Bahasa Indonesia, Matematika, IPA, IPS, Bahasa Inggris, PJOK, Informatika, Seni Budaya/Prakarya, dan BK*).
   - *(Untuk jenjang SMK)* Sinkronisasi mengisi mapel umum/normatif-adaptif; mapel kejuruan ditambahkan manual pada form di bawahnya.
2. **Tambah Mapel Tambahan / Jurusan** *(opsional)*:
   - Isi **Nama Mata Pelajaran**, **Kode Mapel** (misal `MULOK`), dan pilih **Kategori** (`Umum`, `Pilihan`, `Kejuruan`, atau `Muatan Lokal`), lalu klik **`+ Tambah Mata Pelajaran`**.
3. **Edit / Hapus**: Di panel kanan, mata pelajaran yang belum dipakai pada tabel Pengajaran atau Ujian dapat diubah atau dihapus.

| 🖥️ Mode Web (Desktop) | 📱 Mode HP (Mobile) |
| :---: | :---: |
| ![Master Mata Pelajaran Web](assets/bab-01-portal-setup/web-06-master-mapel.png) | ![Master Mata Pelajaran HP](assets/bab-01-portal-setup/hp-06-master-mapel.png) |

---

## 5. Keamanan Gembok Edit (*Proteksi Pengaturan Dasar & Jabatan*)

Agar data pondasi sekolah tidak berubah secara tidak sengaja saat operasional berjalan, form pada `/portal-setup/dasar` dan `/portal-setup/jabatan` otomatis masuk ke **Mode Terkunci (Aman)** setelah statusnya `OK`.

### Cara Membuka Kunci Edit Sementara:
1. Klik tombol **`Buka Kunci Edit`** (atau **`Buka Gembok Edit`**) di banner kuning bagian atas halaman.
2. Pada modal **Verifikasi Super Admin**, masukkan password akun **Super Admin**.
3. Klik **`Verifikasi & Buka Kunci`**.
4. Lakukan perubahan yang diperlukan, lalu klik **`Kunci Kembali`** bila sudah selesai.

> [!IMPORTANT]
> Setelah kelima langkah di `/portal-setup` berstatus **OK (100%)**, sistem siap untuk tahap berikutnya: mengatur hak akses & WhatsApp di **Portal Sistem (`/portal-sistem`)** serta menginput Data Guru, Siswa, dan Pembelajaran di **Portal Akademik (`/portal-akademik`)**.

---

## 6. Navigasi
- ⬅️ Sebelumnya: **[Bab 00: Pendahuluan, Login & Pilih Portal](00-pendahuluan-login-dan-pilih-portal.md)**
- 🖥️ Versi Interaktif Web + Toggle Mode HP: **[Buka Buku Panduan HTML (`index.html`)](index.html)**

