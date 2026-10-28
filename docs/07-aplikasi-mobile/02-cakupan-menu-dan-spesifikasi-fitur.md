# 📋 Cakupan Menu & Spesifikasi Fitur Terpilih

Aplikasi mobile Flutter dirancang **hanya memuat menu-menu esensial** yang paling sering digunakan sehari-hari oleh **Siswa** dan **Guru**, tanpa memasukkan modul administratif berat (seperti manajemen pengguna, pengaturan sistem, konfigurasi server, dsb).

---

## 🎒 A. Modul Portal Siswa

Portal Siswa di aplikasi mobile berfokus pada kemudahan akses identitas digital, pengecekan presensi mandiri, serta interaksi layanan kesiswaan.

### 1. Kartu Pelajar Digital & Barcode ID
- Menampilkan kartu identitas digital ber-foto resmi siswa, nama lengkap, kelas aktif, dan NISN.
- Dilengkapi **Barcode Fisik Rendered** (`Code 128`) dan QR Code yang dapat langsung diarahkan ke alat scanner gerbang sekolah atau scanner meja perpustakaan.
- Fitur *Screen Brightness Booster*: Otomatis meningkatkan kecerahan layar saat membuka kartu agar mudah dibaca barcode scanner.

### 2. Dashboard Ringkas & Status Hari Ini
- Kartu status presensi hari ini: *Hadir Tepat Waktu*, *Terlambat*, *Izin*, atau *Belum Presensi*.
- Waktu scan masuk dan waktu scan pulang tercatat secara presisi.
- Status partisipasi ibadah sholat dhuhur hari ini (*Jamaah*, *Masbuk*, atau *Berhalangan*).

### 3. Riwayat Kehadiran Bulanan
- Rekapitulasi persentase kehadiran dalam 1 bulan (Total Hadir, Sakit, Izin, Terlambat).
- Kalender kehadiran interaktif dengan indikator warna status per tanggal.

### 4. Pengajuan Izin / Sakit Online
- Formulir praktis untuk mengajukan permohonan tidak masuk sekolah:
  - Pilihan jenis: *Sakit* atau *Izin*.
  - Pemilihan rentang tanggal izin (mulai s/d selesai).
  - Alasan izin tertulis.
  - Lampiran foto surat keterangan dokter atau surat izin dari orang tua (ambil langsung dari kamera HP atau galeri).
- Pemantauan status persetujuan (*Pending*, *Disetujui*, atau *Ditolak*) oleh wali kelas secara *real-time*.

### 5. Saluran SPiKAP (Curhat & Aduan Siswa)
- Ruang pengaduan aman bagi siswa untuk melaporkan indikasi perundungan (*bullying*) atau permohonan bimbingan konseling.
- Pilihan laporan bersifat rahasia (*anonim*) atau terbuka ke Guru BK.

---

## 👨‍🏫 B. Modul Portal Guru & Wali Kelas

Portal Guru di aplikasi mobile difokuskan untuk mobilitas guru saat mendampingi siswa di ruang kelas maupun saat ibadah di mushola.

### 1. Dashboard Ringkasan Kelas Binaan
- Memilih kelas yang diampu atau kelas binaan (misal: *7C*).
- Menampilkan statistik cepat kehadiran hari ini: persentase hadir, jumlah siswa yang belum datang, dan jumlah yang terlambat.

### 2. Presensi Harian Kelas Cepat
- Daftar nama seluruh siswa dalam satu kelas.
- Tombol aksi cepat pengubahan status presensi siswa (*Hadir*, *Sakit*, *Izin*, *Alpa*).
- Tombol **WA Ortu** instan untuk membuka chat WhatsApp langsung ke orang tua siswa yang belum hadir tanpa harus menyimpan nomor kontak ke buku telepon HP guru.

### 3. Presensi Sholat Dhuhur Berjamaah
- Khusus digunakan guru piket atau wali kelas saat memantau sholat berjamaah di mushola:
  - Tombol **"Semua Jamaah"** untuk pengisian instan dalam 1 detik.
  - Penyesuaian cepat bagi siswi yang *Berhalangan* (haid) atau siswa yang *Masbuk*.
  - Otomatis memfilter siswa non-muslim agar tidak salah rekap.

### 4. Verifikasi & Approval Izin Siswa
- Daftar pengajuan izin masuk dari siswa kelas binaan yang berstatus *Pending*.
- Guru dapat melihat foto surat lampiran, membaca alasan, lalu menekan tombol **Setujui** atau **Tolak** langsung dari HP.

### 5. Jurnal Mengajar Ringkas
- Pencatatan kegiatan belajar mengajar harian:
  - Jam pelajaran & mata pelajaran.
  - Judul materi pokok yang disampaikan.
  - Catatan kejadian khusus di kelas.
