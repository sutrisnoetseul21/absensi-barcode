# 🧩 Anatomi 11 Section Blade Tampilan Web Awal

Halaman beranda [`resources/views/beranda/index.blade.php`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/resources/views/beranda/index.blade.php) didesain secara modular menggunakan pendekatan `@include` ke sub-view di folder `resources/views/beranda/sections/`.

---

## 1. Daftar Komponen & Section

```text
beranda/index.blade.php
├── [00] <x-public-dashboard.navbar>          # Navigation Bar Utama
├── [01] @include('beranda.sections.hero')     # Banner Utama & Running Text
├── [02] @include('beranda.sections.sambutan') # Sambutan & Foto Kepala Sekolah
├── [03] @include('beranda.sections.profil')   # Profil Singkat, Visi, Misi, Pilar
├── [04] @include('beranda.sections.widget')   # Video YouTube & Akses Cepat
├── [05] @include('beranda.sections.statistik')# Counter Angka Statistik Sekolah
├── [06] @include('beranda.sections.fasilitas')# Galeri Fasilitas Sarpras
├── [07] @include('beranda.sections.guru')     # Swiper Slider Tenaga Pendidik
├── [08] @include('beranda.sections.prestasi') # Galeri Prestasi Siswa & Guru
├── [09] @include('beranda.sections.berita')   # Berita Terbaru & Pengumuman
├── [10] @include('beranda.sections.galeri')   # Album Foto Dokumentasi
└── [11] @include('beranda.sections.footer')   # Footer & Informasi Kontak
```

---

## 2. Bedah Detail Setiap Section

### [00] Navbar (`resources/views/components/public-dashboard/navbar.blade.php`)
- **Tujuan**: Navigasi global untuk seluruh pengunjung website.
- **Elemen Tampilan**:
  - Logo sekolah & nama sekolah (`$sekolah->school_logo_path`, `$sekolah->school_name`).
  - Menu Navigasi: *Beranda*, *Profil Sekolah*, *Direktori Guru*, *Direktori Siswa*, *Layanan Publik*, *Pengaduan*, *Perpustakaan*.
  - Tombol Call-to-Action (*CTA*): Tombol **Login Portal** yang mengarahkan ke modal / halaman pemilihan portal (`/pilih-portal`).

### [01] Hero Section (`beranda/sections/hero.blade.php`)
- **Tujuan**: Kesan visual pertama (*first impression*) dengan gambar resolusi tinggi dan pengumuman berjalan.
- **Variabel Data**:
  - `$setting->hero_image`: Gambar latar hero (disimpan di `storage/web-setting/`).
  - `$setting->running_text`: Teks berjalan (marquee) di bawah hero untuk kabar penting/kegiatan mendadak.
  - Tombol CTA cepat: Menuju profil atau layanan pendaftaran/pengaduan.

### [02] Sambutan Kepala Sekolah (`beranda/sections/sambutan.blade.php`)
- **Tujuan**: Menghadirkan sosok kepemimpinan sekolah dan arahan visi institusi.
- **Variabel Data**:
  - `$setting->foto_kepsek`: Foto resmi Kepala Sekolah.
  - `$setting->posisi_foto_kepsek`: Posisi peletakan foto (`kiri` atau `kanan`).
  - `$setting->kutipan_kepsek`: Kalimat inspiratif / moto kepala sekolah.
  - `$setting->sambutan_kepsek`: Paragraf sambutan lengkap (mendukung format teks kaya).

### [03] Profil, Visi & Misi (`beranda/sections/profil.blade.php`)
- **Tujuan**: Menyampaikan dasar pijakan dan arah pembinaan sekolah.
- **Variabel Data**:
  - `$setting->profil_singkat`: Sejarah atau gambaran singkat sekolah.
  - `$setting->visi` & `$setting->misi`: Poin visi misi terstruktur.
  - `$pillars`: Koleksi model `WebPillar` (menampilkan ikon, judul pilar seperti *Inovatif*, *Religius*, *Berakhlak*).

### [04] Widget Video & Akses Cepat (`beranda/sections/widget.blade.php`)
- **Tujuan**: Konten interaktif multimedia dan link layanan pihak ketiga.
- **Variabel Data**:
  - `$setting->link_youtube`: Otomatis diekstrak menjadi `<iframe>` YouTube embed responsif.
  - `$quickLinks`: Koleksi model `WebQuickLink` (tautan ke Siaga Pendidik, Dapodik, CBT, E-Rapor).

### [05] Statistik Sekolah (`beranda/sections/statistik.blade.php`)
- **Tujuan**: Menampilkan bukti skala sekolah dalam format angka counter dinamis.
- **Variabel Data** (didapat dari `$stats` via `HomepageStatsService`):
  - `hadir_hari_ini`: Siswa yang hadir scan hari ini.
  - `total_siswa`: Total siswa aktif terdaftar di tahun ajaran berjalan.
  - `total_rombel`: Jumlah rombongan belajar aktif.
  - `total_guru`: Jumlah tenaga pendidik terdaftar.
  - `total_tendik`: Jumlah tenaga kependidikan (`$setting->stat_tenaga_kependidikan`).
  - `$webStats`: Statistik kustom tambahan (misal: persentase kelulusan 100%, akreditasi A).

### [06] Fasilitas Sarana Prasarana (`beranda/sections/fasilitas.blade.php`)
- **Tujuan**: Menampilkan fasilitas pendukung pembelajaran.
- **Variabel Data**:
  - `$sarpras`: Koleksi `WebSarpra` terurut berdasarkan kolom `urutan`.
  - Format kartu: Foto fasilitas, nama sarana, deskripsi fasilitas, ikon dan warna badge.

### [07] Tenaga Pendidik (`beranda/sections/guru.blade.php`)
- **Tujuan**: Direktori interaktif jajaran guru dan staf.
- **Teknologi**: Menggunakan library **Swiper Slider** (`guru-slider`).
- **Variabel Data**:
  - `$gurus`: Koleksi `Teacher` dengan eager loading `jabatans`, `pengajarans.mataPelajaran`.
  - Tampilan: Foto guru, nama, NIP, serta mata pelajaran yang diampu.

### [08] Prestasi Siswa & Sekolah (`beranda/sections/prestasi.blade.php`)
- **Tujuan**: Etalase pencapaian akademik dan non-akademik siswa.
- **Variabel Data**:
  - `$prestasis`: `WebArtikel::where('tipe', 'prestasi')` (maksimal 8 item terbaru).
  - Tampilan: Badge tingkat kejuaraan (Kabupaten, Provinsi, Nasional), foto penerimaan piala, judul prestasi.

### [09] Berita & Pengumuman (`beranda/sections/berita.blade.php`)
- **Tujuan**: Warta terkini dan edaran resmi sekolah dalam layout 2 kolom berdampingan.
- **Variabel Data**:
  - Kolom Kiri: `$artikels` (`WebArtikel::where('tipe', 'berita')`).
  - Kolom Kanan: `$pengumumans` (`WebArtikel::where('tipe', 'pengumuman')`).

### [10] Galeri Kegiatan (`beranda/sections/galeri.blade.php`)
- **Tujuan**: Album dokumentasi visual kegiatan upacara, ekstrakurikuler, dan pentas seni.
- **Variabel Data**:
  - `$galeris`: Koleksi `WebGaleri` terurut berdasarkan `urutan`.

### [11] Footer (`beranda/sections/footer.blade.php`)
- **Tujuan**: Informasi legalitas, peta lokasi, kontak langsung, dan hak cipta.
- **Variabel Data**:
  - Alamat sekolah, nomor telepon, email resmi (`school_settings`).
  - Tautan akun media sosial (`link_fb`, `link_ig`, `link_tiktok`, `link_youtube`).
