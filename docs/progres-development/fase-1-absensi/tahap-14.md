# Tahap 14: Perlindungan Data Master Interaktif & Modal Pop-Up Edit

Pada tahap ini, sistem perlindungan data akademik ditingkatkan ke standar yang lebih tinggi dengan mekanisme penguncian interaktif (*interactive blocking notification*) dan penyeragaman tampilan pengubahan data melalui **Modal Pop-Up** pada seluruh menu Master Data.

---

## 1. Penyeragaman Form Edit Menjadi Modal Pop-Up
- **Halaman Edit Diubah ke Modal**: Pengeditan data pada menu **Kelas**, **Jabatan**, **Mata Pelajaran**, dan **Tahun Ajaran** kini tidak lagi berpindah ke halaman baru (`/{id}/edit`). Form edit langsung muncul sebagai Pop-Up Modal di halaman utama.
- **Rute Edit Dihapus**: Menghapus rute `'edit'` pada `getPages()` di masing-masing Filament Resource untuk memaksa pengeditan tetap berada di modal index.

---

## 2. Kebijakan Perlindungan Save & Hapus (Interactive Blocking Notification)
Setiap menu Master Data kini dilengkapi aturan perlindungan data berbasis relasi database. Tombol Edit dan Hapus tetap dapat dibuka, namun saat pengguna mencoba menyimpan (*Save*) atau menghapus (*Delete*), sistem akan memblokir eksekusi tersebut jika data sudah memiliki relasi aktif, serta menampilkan **Notifikasi Peringatan Merah** (*Notification Danger*).

### Detail Aturan per Menu:
1. **Data Master Kelas (`/admin-akademik/kelas`)**:
   - Jika kelas sudah terisi **Siswa (Enrollments)** atau **Jadwal Pembelajaran**:
     - Dibuka via Edit: Menampilkan banner Peringatan Merah di bagian atas form modal.
     - Ditekan *Save*: Penyimpanan diblokir via `before()` hook dan menampilkan notifikasi *"Perubahan Ditolak: Kelas tidak dapat diubah karena sudah memiliki data Siswa atau Pembelajaran aktif"*.
     - Ditekan *Hapus*: Penghapusan diblokir via `before()` hook dan menampilkan notifikasi *"Akses Ditolak: Anda harus mengosongkan/menghapus data Siswa dan Pembelajaran terlebih dahulu"*.

2. **Data Master Jabatan (`/admin-akademik/jabatans`)**:
   - Jika Jabatan sudah ditugaskan pada **Guru**:
     - Ditekan *Save* / *Hapus*: Diblokir secara otomatis dengan notifikasi *"Jabatan tidak dapat diubah/dihapus karena sudah digunakan oleh data Guru"*.

3. **Data Master Mata Pelajaran (`/admin-akademik/mata-pelajarans`)**:
   - Jika Mata Pelajaran sudah terhubung ke **Jadwal Pembelajaran**:
     - Ditekan *Save* / *Hapus*: Diblokir secara otomatis dengan notifikasi *"Mata Pelajaran tidak dapat diubah/dihapus karena sudah terikat dengan jadwal pembelajaran"*.

4. **Data Master Tahun Ajaran (`/admin-akademik/tahun-ajaran`)**:
   - **Otomatisasi Tunggal Status Aktif**: Mengubah satu Tahun Ajaran menjadi status `"aktif"` akan secara otomatis mengubah Tahun Ajaran lain yang sebelumnya aktif menjadi `"arsip"` di database, serta menyinkronkan acuan aktif ke `PengaturanSekolah`.
   - **Perlindungan Save / Hapus**: Jika Tahun Ajaran berstatus **Aktif** ATAU **sudah memiliki data akademik** (Kelas, Enrollment, Presensi), maka penyimpanan atau penghapusan akan diblokir dengan notifikasi Peringatan Merah.

---

## 3. Dinamisasi Real-Time Modal Kelola Siswa Rombel (Enrollment)
- **State Management AlpineJS Terpadu**: Menyatukan state `leftStudents` dan `rightStudents` pada *root element* modal `rombel-manager-modal.blade.php`.
- **Eksekusi Instant & Real-Time**:
  - Pemindahan siswa antar panel (mengklik tombol panah atau fitur *Drag & Drop*) kini langsung memperbarui tampilan DOM secara instan tanpa perlu menutup pop up modal.
  - Memperbaiki pengurutan (*sorting*) independen per panel (`leftSortBy`, `leftSortDir`, `rightSortBy`, `rightSortDir`).
