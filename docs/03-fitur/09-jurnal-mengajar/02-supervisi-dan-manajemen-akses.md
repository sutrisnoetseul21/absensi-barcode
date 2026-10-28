# 🛡️ Manajemen Akses & Mode Supervisi Kepala Sekolah

Dokumen ini memuat panduan pengaturan wewenang hak akses supervisi jurnal mengajar serta tata cara Kepala Sekolah / Waka Kurikulum memantau pembelajaran seluruh guru.

---

## 1. Konfigurasi Wewenang di Panel Admin

Administrator sekolah dapat memberikan wewenang supervisi kepada Kepala Sekolah, Wakil Kepala Sekolah Bidang Kurikulum, atau tim supervisi akademik melalui menu:
**Panel Admin &rarr; Manajemen Akses &rarr; Manajemen Akses Portal (`/admin/manajemen-akses-portal`)**.

### Langkah Konfigurasi:
1. Temukan akun Kepala Sekolah / Waka Kurikulum pada tabel daftar pengguna.
2. Klik tombol **Edit**.
3. Pada seksi **Akses Portal Guru (`/portal-guru`)**:
   * Pastikan **Izinkan Akses Portal Guru** dalam keadaan aktif.
   * Aktifkan toggle **Akses Semua Kelas (Bypass Mode)** jika pengguna berhak memantau absensi seluruh rombel sekolah (`portal_guru:akses_semua_kelas`).
   * Aktifkan toggle **Supervisi Jurnal Mengajar (Akses Seluruh Guru)** jika pengguna berhak memantau, memeriksa, dan mencetak rekap jurnal seluruh guru (`portal_guru:supervisi_jurnal`).
4. Klik **Simpan Perubahan**.

> [!NOTE]
> Sistem akan otomatis menyematkan permission `portal_guru:supervisi_jurnal` dan/atau `portal_guru:akses_semua_kelas` kepada akun tersebut via Spatie Permission. Pengguna dengan status `is_super_admin` atau role `super_admin` otomatis memiliki hak penuh tanpa konfigurasi manual.

---

## 2. Perbedaan Antarmuka Guru Reguler vs Mode Supervisi

| Aspek | Guru Reguler (Pengajar) | Mode Supervisi (Kepala Sekolah / Admin) |
| :--- | :--- | :--- |
| **Dropdown Guru** | **Disembunyikan**. Otomatis terkunci ke profil guru yang sedang login. | **Ditampilkan**. Memuat daftar seluruh guru pengampu di sekolah. |
| **Filter Mapel** | Hanya mapel yang diajar oleh guru tersebut. | Otomatis memuat mapel yang diampu oleh guru terpilih (*cascade*). |
| **Filter Kelas** | Hanya rombel yang diajar oleh guru tersebut. | Otomatis memuat kelas yang diajar oleh guru terpilih untuk mapel tersebut. |
| **Riwayat Jurnal** | Hanya menampilkan jurnal yang ditulis oleh guru yang login. | Menampilkan seluruh riwayat jurnal guru yang sedang dipilih. |
| **Hak Edit / Hapus** | Bisa mengedit dan menghapus jurnal miliknya sendiri. | Mode inspeksi supervisi (read-only), kecuali akun Super Admin. |
| **Cetak Rekap PDF** | Mencetak rekap jurnal mengajar miliknya sendiri. | Mencetak rekap supervisi KBM per guru terpilih atau per mata pelajaran. |

---

## 3. Alur Kerja Pemantauan Supervisi

1. Kepala Sekolah login ke akun Portal Guru dan membuka menu **Jurnal Mengajar** (`/portal-guru/jurnal-mengajar`).
2. Muncul banner khusus bertanda **Mode Supervisi**.
3. Kepala Sekolah memilih guru yang akan disupervisi pada dropdown **Pilih Guru Pengampu**.
4. Sistem secara otomatis memperbarui dropdown **Mata Pelajaran** dan **Kelas Rombel** sesuai penugasan guru tersebut.
5. Tabel riwayat KBM menampilkan daftar materi yang telah diajarkan, tanggal pelaksanaan, jam ke (durasi JP), persentase kehadiran siswa, dan catatan refleksi guru.
6. Kepala Sekolah dapat menekan tombol **"Cetak Rekap PDF"** untuk mengunduh dokumen resmi rekapitulasi agenda KBM guru yang bersangkutan sebagai berkas supervisi akademik.
