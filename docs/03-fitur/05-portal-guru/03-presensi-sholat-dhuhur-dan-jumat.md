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
3. **Cetak Dokumen Laporan**:
   - **Format PDF**: Laporan rekap sholat per kelas ber-kop surat resmi sekolah.
   - **Format Excel**: Multi-sheet workbook (`RekapSholatDhuhurKelasSheet`, `RekapSholatDhuhurDetailSheet`, `RekapSholatDhuhurBelumSheet`).

---

## 4. Aksi Cepat Notifikasi WhatsApp ke Orang Tua (`WA Ortu`)

Pada daftar siswa yang berstatus **Belum Absen Sholat** atau **Tidak Hadir**, guru/wali kelas dapat mengirimkan notifikasi personal ke nomor orang tua/wali siswa melalui modal pop-up interaktif:
- **Pratinjau & Edit Pesan**: Wali kelas dapat mengedit redaksi pesan dan nomor tujuan secara fleksibel sebelum mengirim.
- **Dua Jalur Pengiriman**:
  1. **Kirim Sekarang (via Gateway)**: Mengirimkan pesan langsung melalui WhatsApp Gateway server sekolah di latar belakang tanpa membuka aplikasi WhatsApp pihak ketiga.
  2. **Buka di WA (Web / HP)**: Membuka percakapan WhatsApp baru secara langsung (`https://wa.me/...`) sebagai opsi alternatif instan.
- **Ketahanan UI Modal**: Panel modal diproteksi dengan z-index `relative z-10` dan event handler `@click.stop` untuk mencegah modal tertutup sendiri secara tidak sengaja saat pengguna mengetik atau berinteraksi di dalam pop-up.
