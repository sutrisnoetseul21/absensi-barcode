# 📅 Presensi Kelas, Kalender Akademik, & Profil Siswa Binaan

Dokumen ini menjelaskan modul pemantauan kehadiran presensi rombel oleh wali kelas serta penelusuran riwayat akademik per siswa.

---

## 1. Kalender Presensi Rombel (`/portal-guru/akademik`)

Dikelola oleh komponen Livewire [`App\Livewire\WaliKelasDashboard`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Livewire):
- **Matriks Kehadiran Bulanan**: Menampilkan kalender visual harian selama satu bulan untuk seluruh siswa di kelas wali yang bersangkutan.
- **Indikator Warna Status**:
  - 🟢 **Hadir (Present)**: Siswa scan tepat waktu di gerbang.
  - 🟡 **Terlambat (Late)**: Siswa scan melebihi batas jam masuk (lengkap dengan info menit).
  - 🔵 **Izin (Excused)**: Memiliki surat izin yang telah disetujui.
  - 🟣 **Sakit (Sick)**: Memiliki surat dokter / izin sakit.
  - 🔴 **Alpa (Absent)**: Tidak hadir tanpa keterangan.
- **Ekspor Cepat**: Tombol cetak PDF lembar rekap presensi kelas untuk lampiran wali kelas saat pembagian rapor.

---

## 2. Direktori Siswa Kelas (`/portal-guru/data-siswa`)

Komponen Livewire `App\Livewire\PortalGuru\DataSiswaList`:
- Menampilkan seluruh siswa yang terdaftar aktif dalam rombel kelas binaan.
- Pencarian instan berdasarkan nama atau NISN.
- Menampilkan status kesiswaan, nomor barcode kartu, dan nomor kontak WhatsApp orang tua siswa.
- Tombol aksi menuju halaman **Detail Profil Siswa**.

---

## 3. Detail Siswa Binaan (`/portal-guru/siswa/{id}`)

Komponen Livewire [`App\Livewire\WaliKelasStudentDetail`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Livewire):
Menyajikan lembar rekam jejak (*student portfolio*) komprehensif untuk satu siswa:
1. **Tab Profil**: Foto, NISN, NIS, tempat tanggal lahir, alamat, dan data orang tua/wali.
2. **Tab Kalender Absensi Individual**: Riwayat kehadiran spesifik siswa selama satu semester, rasio persentase hadir, dan akumulasi menit keterlambatan.
3. **Tab Riwayat Izin & Sakit**: Daftar seluruh permohonan izin yang pernah diajukan beserta arsip foto surat dokternya.
4. **Tab Catatan Wali Kelas**: Catatan observasi perkembangan perilaku dan motivasi belajar siswa yang dicatat langsung oleh wali kelas.
