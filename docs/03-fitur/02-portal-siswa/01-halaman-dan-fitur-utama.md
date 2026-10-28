# 🧩 Halaman & Fitur Utama Portal Siswa

Portal Siswa menyediakan antarmuka terpadu bagi siswa untuk memantau kehadiran, mengakses layanan sekolah, dan mengajukan permohonan secara mandiri.

---

## 1. Daftar Halaman & Rute Portal Siswa

| Rute URL | Komponen Livewire | Deskripsi Fitur |
| :--- | :--- | :--- |
| `/portal-siswa` | `SiswaMainDashboard` | Beranda ringkasan: kartu digital siswa, persentase hadir, shortcut menu, dan pengumuman aktif sekolah. |
| `/portal-siswa/akademik` | `SiswaDashboard` | Rekap kehadiran presensi gerbang dengan kalender interaktif bulanan (Hadir, Terlambat, Izin, Sakit, Alpa). |
| `/portal-siswa/sholat-dhuhur` | `SiswaSholatDhuhur` | Riwayat catatan ibadah sholat dhuhur & sholat jumat berjamaah. |
| `/portal-siswa/profil` | `SiswaProfil` | Informasi identitas siswa, NIS, NISN, wali kelas, kontak orang tua, dan badge karakter yang diraih. |
| `/portal-siswa/cetak-kartu` | `SiswaCetakController` | Cetak kartu pelajar mandiri ber-barcode (format PDF) untuk cadangan kartu fisik. |
| `/portal-siswa/perpustakaan` | `SiswaPerpustakaan` | Menampilkan buku yang sedang dipinjam, tanggal jatuh tempo, histori pinjam, dan katalog pencarian buku. |
| `/portal-siswa/ijin-kehadiran` | `IjinKehadiranList` & `Form` | Form pengajuan izin/sakit mandiri dari rumah dengan upload foto surat keterangan dokter. |
| `/portal-siswa/spikap` | `SpikapLaporanList` & `Form` | Kanal pelaporan aduan perundungan (*bullying*), keluhan, atau masalah siswa dengan opsi rahasia/anonim. |
| `/portal-siswa/konsultasi-guru-wali` | `KonsultasiGuruWaliList` | Jadwal dan ruang konsultasi/bimbingan tatap muka dengan guru wali asuh. |
| `/portal-siswa/nilai` | `SiswaNilai` | Rekapitulasi perolehan skor nilai ujian akademik (STS, SAS, US). |

---

## 2. Bedah Detail Fitur Unggulan

### A. Dashboard Utama (`SiswaMainDashboard`)
- **Kartu Identitas Digital**: Menampilkan foto, nama, NISN, kelas, dan status keaktifan.
- **Statistik Cepat**:
  - Persentase tingkat kehadiran semester berjalan.
  - Jumlah buku perpustakaan yang masih dipinjam.
  - Status permohonan izin (Pending / Disetujui / Ditolak).
  - Status tiket aduan SPIKAP aktif.
- **Profil Guru Wali**: Menampilkan foto dan nama guru wali asuh siswa bersangkutan beserta tombol cepat konsultasi.
- **Pengumuman Sekolah**: Menampilkan edaran resmi dari pihak sekolah (`Pengumuman::aktifSekarang()`).

### B. Cetak Kartu Barcode Mandiri (`SiswaCetakController::cetakKartuMandiri`)
- Siswa dapat mengunduh kartu siswa digital berformat PDF standar kartu ID (rasio kartu ATM).
- Generator barcode menggunakan standar `Code 128` berbasis NISN/NIS siswa.
- Kartu ini dapat dicetak sendiri oleh siswa atau ditunjukkan via layar smartphone ke scanner gerbang saat kartu fisik tertinggal.

### C. Pengajuan Izin / Sakit Mandiri (`IjinKehadiranForm`)
- Siswa/orang tua dapat memilih jenis: **Izin** atau **Sakit**.
- Menentukan tanggal mulai dan selesai.
- Mengunggah berkas foto surat dokter atau surat izin bertanda tangan orang tua.
- Permohonan langsung masuk ke dashboard wali kelas untuk diverifikasi (*Approve* / *Reject*).
- Jika disetujui, sistem otomatis membuat catatan di tabel `attendances` dengan status `excused` atau `sick`.

### D. Layanan Bimbingan Guru Wali & SPIKAP
- Mengakomodasi amanat bimbingan karakter siswa:
  - Siswa dapat melihat badge karakter yang disematkan guru pembina.
  - Mengajukan permohonan sesi bimbingan curhat/konsultasi jika mengalami kesulitan belajar.
  - Mengirim aduan aman jika melihat atau mengalami perundungan (*anti-bullying*).
