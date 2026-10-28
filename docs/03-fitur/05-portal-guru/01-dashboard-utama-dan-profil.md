# 📊 Dashboard Utama Guru & Pengaturan Profil

Dokumen ini mendokumentasikan beranda kendali utama guru ([`App\Livewire\GuruMainDashboard`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Livewire)) dan modul kelola profil identitas guru ([`App\Livewire\GuruProfil`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Livewire)).

---

## 1. Dashboard Utama (`/portal-guru`)

`GuruMainDashboard` dirancang sebagai *cockpit* harian guru saat tiba di sekolah:

### A. Rangkuman Kelas Binaan & Jadwal
- **Identitas Rombel**: Menampilkan nama rombongan belajar yang dibina (contoh: *Kelas VII-A*), tahun ajaran aktif, dan jumlah total siswa aktif di kelas.
- **Tingkat Kehadiran Hari Ini**:
  - Siswa yang sudah hadir scan di gerbang.
  - Siswa yang datang terlambat beserta durasi keterlambatan.
  - Siswa yang mengajukan izin / sakit.
  - Siswa yang belum hadir / alpa.

### B. Widget Aksi Cepat & Notifikasi Peringatan
1. **Badge Izin Pending**: Peringatan jika terdapat permohonan izin/sakit baru dari siswa yang membutuhkan persetujuan wali kelas.
2. **Badge Aduan SPIKAP Baru**: Notifikasi jika ada laporan aduan baru yang melibatkan siswa binaan (hanya muncul bagi wali kelas/guru BK).
3. **Agenda Konsultasi Guru Wali**: Menampilkan jadwal janji temu tatap muka bimbingan karakter dengan siswa asuh yang dijadwalkan hari ini.
4. **Pintasan Sholat Dhuhur**: Tombol cepat untuk langsung membuka lembar input presensi sholat kelas saat jam istirahat sholat tiba.

---

## 2. Pengaturan Profil Guru (`/portal-guru/profil`)

Komponen Livewire `App\Livewire\GuruProfil`:
- **Data yang Dapat Diperbarui Mandiri**:
  - Nomor Handphone / WhatsApp aktif guru (kritis untuk pengiriman rekap harian otomatis dari sistem).
  - Foto profil resmi.
  - Kata sandi (password) akun login.
- **Data Read-Only (Terkunci oleh Admin)**:
  - NIP / NUPTK, Nama Lengkap beserta gelar, dan status penugasan wali kelas.
