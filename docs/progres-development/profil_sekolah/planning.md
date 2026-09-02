# Rencana Implementasi (Draft): Fitur Beranda & Profil Sekolah (ERP)

## 1. Analisis Layout Web & Fitur
Sesuai instruksi desain yang elegan, berikut susunannya dari atas ke bawah:
1. **Hero Section & Widget Presensi**: Gambar banner besar. Diatasnya ada Widget Presensi realtime (Waktu saat ini & Jumlah siswa hadir hari ini — terintegrasi dengan modul attendance).
2. **Running Text**: Teks berjalan informatif di bawah Hero.
3. **Sambutan Kepala Sekolah**: 1 Kolom penuh (Slide/Section khusus).
4. **Sarpras (Fasilitas)**: Tampilan Grid elegan (4 kotak per baris, baris bertambah otomatis sesuai jumlah data).
5. **Media Sosial (YouTube & TikTok)**: Ditampilkan berjejer 2 kolom agar seimbang, modern, dan hemat tempat.
6. **Social Media Links (IG & FB)**: Ditampilkan cantik (bisa di footer atau floating icon).
7. **Galeri Sekolah**: 1 Baris menggunakan *Carousel/Slider* agar elegan meski fotonya banyak.
8. **Artikel & Pengumuman (Split 2 Kolom)**: Kiri untuk list Berita/Artikel, Kanan untuk Pengumuman.
9. **Statistik & Link Widget (Split 2 Kolom)**:
   - **Kolom Kiri (4 Kotak Grid)**: Statistik dinamis (Jumlah Siswa aktif, Pendidik, Rombel mengambil langsung dari Master Data ERP. Tenaga Kependidikan dari setting).
   - **Kolom Kanan (4 Kotak Grid)**: Widget Link cepat (Dapodik, e-Rapor, dll).
10. **Layanan Pengaduan**: Tombol/Banner mencolok menuju WA/Form pengaduan.

## 2. Struktur Database (Standar ERP Multi-Table)
Karena kebutuhannya kompleks (tidak lagi sekadar profil sederhana), kita harus memisahnya ke beberapa tabel (prefix `web_`) agar sesuai standar *Loose Coupling* dan mudah di-*maintain* via Filament.

1. `web_settings`: Tabel Singleton untuk konfigurasi tunggal (Hero, Running Text, Sambutan, Link Sosmed, Link Pengaduan).
2. `web_sarpras`: Tabel fasilitas sekolah (id, nama, foto, deskripsi).
3. `web_artikels`: Tabel untuk Publikasi (id, judul, tipe: `berita`/`pengumuman`, konten, gambar).
4. `web_galeris`: Tabel khusus foto galeri (id, judul, foto).
5. `web_widgets`: Tabel link eksternal (id, nama, url, icon).

## 3. Integrasi dengan Master Data ERP
- **Widget Presensi Hero**: Query ke tabel `attendances` berdasarkan tanggal hari ini (`whereDate('date', today())`).
- **Data Statistik**:
  - Siswa: `Student::where('status', 'aktif')->count()`
  - Rombel: `Class::count()`
  - Pendidik: `Teacher::count()`
  - (Otomatis *real-time* mengikuti perubahan data di admin master).
