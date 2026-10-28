# Tahap 13: Manajemen Penghapusan (Delete) vs Pengarsipan (Archive) Data Master

Pada tahap ini, sistem perlindungan data yang sangat ketat telah diimplementasikan untuk mencegah hilangnya data absensi akibat penghapusan data master secara tidak sengaja oleh Admin/Superadmin. 

Sistem kini memisahkan secara tegas antara **menghapus data (karena salah input)** dan **mengarsipkan data (karena siswa lulus/mutasi)**.

## 1. Fitur Arsip Siswa (Lulus & Mutasi)
Siswa yang sudah memiliki rekam jejak absensi tidak boleh dihapus jika mereka keluar dari sekolah. Sebagai gantinya, status mereka diubah.
- **Kolom Baru:** Menambahkan kolom `status` (`aktif`, `lulus`, `mutasi`) pada tabel `students`.
- **Menu Siswa (Aktif):** Tabel utama di menu Siswa kini difilter secara otomatis untuk HANYA menampilkan siswa dengan status `aktif`.
- **Menu Baru (Data Master -> Siswa Lulus):** Halaman read-only khusus untuk melihat daftar siswa yang sudah diluluskan.
- **Menu Baru (Data Master -> Siswa Mutasi):** Halaman read-only khusus untuk melihat daftar siswa yang pindah/keluar sekolah.
- **Aksi Tandai Mutasi:** Ditambahkan tombol "Tandai Mutasi" pada baris tabel siswa aktif. Jika diklik, sistem akan otomatis mengubah status global siswa menjadi `mutasi` dan sekaligus mengubah status pendaftaran (enrollment) di kelas aktifnya menjadi `pindah`. Jadi, siswa tidak perlu dikeluarkan dari kelas secara manual.
- **Aksi Aktifkan Kembali:** Ditambahkan tombol untuk mengembalikan siswa Lulus/Mutasi menjadi Aktif.
- **Luluskan Kelas 9:** Fitur kelulusan massal di menu Pendaftaran Kelas kini secara otomatis mengubah `status` siswa menjadi `lulus` di tabel `students`.

## 2. Kebijakan Hapus Berjenjang (Hierarchical Delete Guard)
Tombol *Delete* (Hapus Permanen) HANYA diizinkan untuk data yang baru saja dibuat atau salah ketik (belum memiliki relasi/history). Jika sudah ada relasi, aksi penghapusan akan diblokir oleh sistem.

Aturan pemblokiran yang diterapkan (via `beforeDelete` hook di Filament):
1. **Tahun Ajaran:** Tidak bisa dihapus jika sudah digunakan oleh Kelas, Pendaftaran Siswa, atau Presensi. (Solusi: Edit status menjadi "Arsip").
2. **Kelas:** Tidak bisa dihapus jika masih ada siswa yang terdaftar di dalamnya (Enrollments).
3. **Guru:** Tidak bisa dihapus jika guru tersebut masih ditugaskan sebagai Wali Kelas.
4. **Siswa:** Tidak bisa dihapus jika siswa tersebut masih terdaftar di kelas (Enrollments).
5. **Pendaftaran Kelas (Enrollment):** Tidak bisa mengeluarkan siswa dari kelas jika siswa tersebut **sudah memiliki data presensi** di kelas tersebut.

## 3. Fitur Hapus Presensi (Koreksi Salah Input)
Karena aturan di atas sangat ketat (Siswa tidak bisa dikeluarkan dari kelas jika sudah ada presensi), maka dibuatkan "jalan keluar" apabila Admin salah melakukan absensi.
- **Laporan Detail Presensi:** Menambahkan tombol **Hapus** pada tiap baris dan opsi **Hapus Presensi Terpilih (Bulk Delete)** di halaman Laporan Detail.
- **Alur Koreksi:** Jika Admin salah mendaftarkan anak ke kelas dan terlanjur diabsen -> Admin hapus absennya di Laporan Detail -> Admin keluarkan anak dari kelas (Enrollments) -> Admin Hapus siswanya (jika fiktif).

Dengan implementasi tahap ini, database dipastikan 100% aman dari kerusakan relasi atau yatimnya data (orphaned records).
