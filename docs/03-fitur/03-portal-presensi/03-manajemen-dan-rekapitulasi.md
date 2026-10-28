# 📋 Manajemen Data, Rekapitulasi, & Cetak Kartu Barcode

Dokumen ini menjelaskan modul administratif di Portal Presensi: input kehadiran manual, rekapitulasi data rombel/sekolah, dan pencetakan massal kartu barcode siswa.

---

## 1. Input Presensi Manual (`/portal-presensi/input-manual`)

Dikelola oleh komponen Livewire [`App\Livewire\PortalPresensi\InputPresensiManual`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Livewire/PortalPresensi).
- **Tujuan**: Mengakomodasi siswa yang tidak dapat melakukan pemindaian fisik di gerbang (misal: kartu tertinggal, dispensasi lomba di luar sekolah, atau gangguan hardware pos satpam).
- **Fitur Utama**:
  - Filter pemilihan Kelas dan Tanggal Presensi.
  - Tabel interaktif dengan tombol *radio toggle*: **Hadir**, **Terlambat**, **Izin**, **Sakit**, **Alpa**.
  - Kolom keterangan tambahan / catatan dispensasi.
  - Tombol simpan batch yang mengupdate tabel `attendances` dengan flag `is_manual_input = true` dan mencatat `manual_input_by_id = auth()->id()`.

---

## 2. Rekapitulasi Kehadiran Kelas & Sekolah

Portal Presensi memisahkan pelaporan menjadi dua tingkatan analisis:

### A. Rekap Per Kelas (`/portal-presensi/rekap-kelas`)
- Komponen: `App\Livewire\PortalPresensi\RekapAbsensiKelas`.
- Menampilkan matriks harian siswa dalam satu rombel tertentu selama satu bulan penuh.
- Memberikan ringkasan angka per siswa: Total Hadir (H), Sakit (S), Izin (I), Alpa (A), dan Terlambat (T).
- Digunakan oleh admin presensi dan wali kelas untuk bahan evaluasi kehadiran bulanan.

### B. Rekap Tingkat Sekolah (`/portal-presensi/rekap-sekolah`)
- Komponen: `App\Livewire\PortalPresensi\RekapAbsensiSekolah`.
- Mengagregasikan persentase kehadiran seluruh rombel (Tingkat 7, 8, dan 9).
- Menampilkan grafik komparasi kehadiran antarkelas untuk mendeteksi kelas dengan angka absensi tertinggi.
- Disediakan format cetak resmi berkop sekolah untuk diserahkan ke Kepala Sekolah dan Pengawas Pembina Dinas Pendidikan.

---

## 3. Cetak Kartu Siswa & Barcode Massal (`/portal-presensi/cetak-kartu`)

Komponen Livewire [`App\Livewire\PortalPresensi\CetakKartuSiswa`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Livewire/PortalPresensi):
- **Generator Barcode Standar**:
  - Mendukung barcode format **Code 128** resolusi tinggi menggunakan library Picqer Barcode.
  - Pilihan nomor barcode: berbasis NISN atau NIS siswa sesuai pengaturan `school_settings.barcode_scan_mode`.
- **Mode Cetak**:
  - **Cetak Lembar Kartu Pelajar**: Layout cetak kertas foto / PVC card lengkap dengan foto siswa, logo kabupaten/sekolah, NISN, nama, kelas, dan stempel sekolah.
  - **Cetak Stiker Label Barcode**: Layout cetak stiker hemat untuk ditempelkan pada kartu OSIS yang sudah dicetak sebelumnya.
- **Filter Pencetakan**:
  - Cetak per kelas (misal: seluruh siswa kelas VII-A sekaligus).
  - Cetak satuan (khusus siswa baru / penggantian kartu hilang).

---

## 4. Administrasi Izin & Sakit (`IjinKehadiranResource`)

Dikelola di Panel Admin melalui menu **Presensi &rarr; Ijin Kehadiran** (`/admin/ijin-kehadirans`):
- **Model Eloquent**: [`App\Models\LeaveRequest`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Models/LeaveRequest.php) (tabel `leave_requests`).
- **Otorisasi**: Memerlukan role `super_admin`, `admin_presensi_editor`, atau `admin_presensi_viewer`.
- **Formulir Pengajuan Terstruktur**:
  - **Identitas Pemohon**: Pilihan siswa atau GTK yang mengajukan izin.
  - **Tipe Ketidakhadiran**: *Izin*, *Sakit*, atau *Dispensasi Kegiatan Sekolah/Lomba*.
  - **Rentang Waktu**: Tanggal mulai hingga tanggal selesai (mendukung izin multi-hari).
  - **Lampiran Dokumen Bukti**: Unggah foto/PDF surat keterangan sakit dari dokter atau surat resmi dari orang tua/wali murid.
- **Workflow Persetujuan (*Approval Flow*)**:
  - Petugas presensi memverifikasi keabsahan dokumen bukti.
  - Saat status diubah menjadi **Approved**, sistem otomatis menginjeksi status kehadiran yang relevan ke tabel presensi harian (`attendances`) pada rentang tanggal tersebut.
  - Dengan demikian, mesin rekapitulasi bulanan dan laporan sekolah langsung mencatat rekap status izin/sakit tanpa memerlukan input manual berulang di gerbang scanner.
