# 📊 Input Nilai Mata Pelajaran & Layanan Perpustakaan Guru

Dokumen ini mendokumentasikan modul penginputan nilai asesmen mata pelajaran (`/portal-guru/nilai`) dan akses sirkulasi perpustakaan guru (`/portal-guru/perpustakaan`).

---

## 1. Input & Daftar Nilai Mata Pelajaran (`/portal-guru/nilai`)

Komponen Livewire [`App\Livewire\PortalGuru\DaftarNilai`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Livewire/PortalGuru/DaftarNilai.php):
- **Akses Fleksibel untuk Seluruh Guru**:
  - Berbeda dengan modul wali kelas, rute `/portal-guru/nilai` dapat diakses oleh seluruh guru pengajar mata pelajaran (cukup memiliki role `guru`).
- **Fitur Utama**:
  - Filter pemilihan Mata Pelajaran yang diampu dan Rombongan Belajar / Kelas.
  - Tabel input skor nilai ujian (STS, SAS, Formatif, Sumatif).
  - Indikator KKM/KKTP: Otomatis memberi warna merah jika nilai siswa berada di bawah ambang batas tuntas.
  - Tombol Simpan Cepat (*Auto Save / Batch Submit*).

---

## 2. Layanan Perpustakaan Guru (`/portal-guru/perpustakaan`)

Komponen Livewire [`App\Livewire\GuruPerpustakaan`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Livewire):
- **Pinjaman Buku Guru**:
  - Menampilkan daftar buku ajar, referensi, dan kurikulum yang sedang dipinjam oleh guru bersangkutan.
  - Menampilkan tanggal jatuh tempo pengembalian buku ke perpustakaan.
- **Katalog Pencarian Buku Sekolah**:
  - Guru dapat mencari buku referensi pengajaran yang tersedia di rak perpustakaan secara mandiri sebelum berkunjung ke ruang perpus.
