# 🕌 Presensi Ibadah Sholat Dhuhur & Sholat Jumat Berjamaah

Dokumen ini menjelaskan alur pencatatan dan pengelolaan presensi ibadah sholat berjamaah oleh wali kelas melalui Portal Guru ([`App\Livewire\PortalGuru\SholatDhuhur\WaliKelasSholatDhuhur`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Livewire/PortalGuru/SholatDhuhur/WaliKelasSholatDhuhur.php)).

---

## 1. Antarmuka Input Massal Sholat Kelas (`/portal-guru/sholat-dhuhur`)

Wali kelas dapat mengisi presensi sholat seluruh siswa di kelasnya dengan cepat:
- **Tabel Daftar Siswa**: Menampilkan nama siswa, jenis kelamin, dan tombol status interaktif.
- **Pilihan Status Sholat**:
  - 🟢 **Jamaah (Hadir)**: Siswa mengikuti sholat berjamaah tepat waktu di mushola sekolah.
  - 🟡 **Masbuk**: Siswa terlambat bergabung dalam barisan sholat berjamaah.
  - 🟣 **Berhalangan**: Khusus siswi putri yang sedang dalam kondisi haid / tidak sholat sesuai syariat.
  - 🔴 **Tidak Sholat**: Siswa muslim yang berada di sekolah namun tidak mengikuti ibadah berjamaah.
- **Tombol Hadir Semua Instan**: Mempercepat penginputan dengan sekali klik untuk menandai seluruh siswa hadir jamaah, lalu wali kelas cukup mengubah beberapa siswa yang berhalangan/masbuk.

---

## 2. Pengaturan Presensi Hari Jumat (`enable_sholat_jumat`)

Sistem mendukung penyesuaian khusus untuk hari Jumat:
- Diatur via database `school_settings.enable_sholat_jumat`.
- Jika diaktifkan:
  - Siswa laki-laki dicatat keikutsertaannya dalam **Sholat Jumat**.
  - Siswi perempuan dapat dicatat dalam sholat dhuhur atau kepulangan awal sesuai kebijakan sekolah.

---

## 3. Matriks Statistik Bulanan & Ekspor Laporan

1. **Rekap Bulanan Real-Time**:
   - Menghitung persentase keaktifan sholat setiap siswa selama 1 bulan kalender.
   - Deteksi dini bagi siswa yang sering tidak sholat untuk diberikan pembinaan oleh guru PAI atau wali kelas.
2. **Cetak Dokumen Laporan**:
   - **Format PDF**: Laporan rekap sholat per kelas ber-kop surat resmi sekolah.
   - **Format Excel**: Multi-sheet workbook (`RekapSholatDhuhurKelasSheet`, `RekapSholatDhuhurDetailSheet`, `RekapSholatDhuhurBelumSheet`).
