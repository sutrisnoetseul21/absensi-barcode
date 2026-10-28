# Tahap 2 — Atur `navigationGroup` & `navigationSort` (Rapikan Sidebar)

**Status:** ⏳ Belum dikerjakan (tunggu Tahap 1 selesai & disetujui)  
**Estimasi waktu:** ~45 menit  
**Jumlah file yang diubah:** ~20 file

---

## Tujuan

Setelah Tahap 1, semua menu sudah muncul di `/admin` tapi mungkin urutannya masih acak
dan beberapa grup nama-nya masih menggunakan nama lama (misal: `Koleksi Buku`, `Sirkulasi`,
`Laporan`, `Pengaturan` — khusus dari panel perpustakaan dan presensi).

Tahap ini merapikan semua itu agar sidebar terbagi per grup dengan urutan yang logis.

---

## Target Susunan Sidebar Final

```
📊  Dashboard
─────────────────────────────────────────────────────────
👥  Data Master         (sort grup: 1)
    ├── Pengguna                          sort: 1
    ├── Manajemen Akses Portal            sort: 2
    ├── Guru                              sort: 3
    ├── Jabatan Guru                      sort: 4
    ├── Mata Pelajaran                    sort: 5
    └── Tahun Ajaran                      sort: 6
─────────────────────────────────────────────────────────
🎓  Akademik            (sort grup: 2)
    ├── Siswa                             sort: 1
    ├── Kelas                             sort: 2
    ├── Rombongan Belajar                 sort: 3
    ├── Enrollment Siswa                  sort: 4
    ├── Pindah Kelas                      sort: 5
    ├── Siswa Lulus                       sort: 6
    ├── Siswa Mutasi                      sort: 7
    └── Pengaturan Akademik               sort: 8
─────────────────────────────────────────────────────────
📋  Presensi            (sort grup: 3)
    ├── Laporan Presensi                  sort: 1
    ├── Rekap Absensi Kelas               sort: 2
    ├── Rekap Absensi Sekolah             sort: 3
    ├── Cetak Laporan                     sort: 4
    ├── Input Presensi Manual             sort: 5
    ├── Manajemen Kartu Presensi          sort: 6
    ├── Hari Libur                        sort: 7
    ├── Notifikasi WhatsApp               sort: 8
    └── Pengaturan Presensi               sort: 9
─────────────────────────────────────────────────────────
📚  Perpustakaan        (sort grup: 4)
    ├── Buku                              sort: 1
    ├── Inventaris Buku                   sort: 2
    ├── Kategori Buku                     sort: 3
    ├── Klasifikasi DDC                   sort: 4
    ├── Peminjaman Aktif                  sort: 5
    ├── Riwayat Pengembalian              sort: 6
    ├── Kunjungan                         sort: 7
    ├── Reservasi Segera Hadir            sort: 8
    ├── Laporan Sirkulasi                 sort: 9
    ├── Anggota Perpustakaan              sort: 10
    ├── Import dari SLiMS                 sort: 11
    └── Pengaturan Perpustakaan           sort: 12
─────────────────────────────────────────────────────────
⚙️  Pengaturan Sistem   (sort grup: 5)
    ├── Pengaturan Sekolah                sort: 1
    └── Pengaturan Tema                   sort: 2
```

---

## Perubahan Detail Per File

### Grup: Data Master

| File | Perubahan Group | Perubahan Sort |
|---|---|---|
| `app/Filament/Resources/UserResource.php` | (sudah `Data Master` / cek) | sort: 1 |
| `app/Filament/Resources/ManajemenAksesPortalResource.php` | `Data Master` | sort: 2 |
| `app/Filament/Akademik/Resources/Guru/GuruResource.php` | (sudah `Data Master`) | sort: 3 |
| `app/Filament/Akademik/Resources/Jabatans/JabatanResource.php` | (sudah `Data Master`) | sort: 4 |
| `app/Filament/Akademik/Resources/MataPelajarans/MataPelajaranResource.php` | (sudah `Data Master`) | sort: 5 |
| `app/Filament/Akademik/Resources/TahunAjarans/TahunAjaranResource.php` | **Ubah** `Data Master` → `Data Master` | sort: **6** |

---

### Grup: Akademik

| File | Perubahan Group | Perubahan Sort |
|---|---|---|
| `app/Filament/Akademik/Resources/Siswa/SiswaResource.php` | **Ubah** `Data Master` → `Akademik` | sort: 1 |
| `app/Filament/Akademik/Resources/Kelas/KelasResource.php` | **Ubah** `Data Master` → `Akademik` | sort: 2 |
| `app/Filament/Akademik/Resources/RombonganBelajarResource.php` | (sudah `Akademik`) | sort: 3 |
| `app/Filament/Akademik/Resources/Enrollment/EnrollmentResource.php` | (sudah `Akademik`) | sort: 4 |
| `app/Filament/Akademik/Resources/PindahKelasResource.php` | (sudah `Akademik`) | sort: 5 |
| `app/Filament/Akademik/Resources/SiswaLulusResource.php` | (sudah `Akademik`) | sort: 6 |
| `app/Filament/Akademik/Resources/SiswaMutasiResource.php` | (sudah `Akademik`) | sort: 7 |
| `app/Filament/Akademik/Pages/AkademikSettingsPage.php` | (cek/tambah `Akademik`) | sort: 8 |

---

### Grup: Presensi

| File | Perubahan Group | Perubahan Sort |
|---|---|---|
| `app/Filament/Presensi/Pages/LaporanPresensi.php` | **Ubah** `Laporan` → `Presensi` | sort: 1 |
| `app/Filament/Presensi/Pages/RekapAbsensiKelas.php` | **Ubah** `Laporan` → `Presensi` | sort: 2 |
| `app/Filament/Presensi/Pages/RekapAbsensiSekolah.php` | **Ubah** `Laporan` → `Presensi` | sort: 3 |
| `app/Filament/Presensi/Pages/CetakLaporanPresensi.php` | **Ubah** `Laporan` → `Presensi` | sort: 4 |
| `app/Filament/Presensi/Pages/InputPresensiManual.php` | (sudah `Presensi`) | sort: 5 |
| `app/Filament/Presensi/Pages/ManajemenKartuPresensi.php` | (sudah `Presensi`) | sort: 6 |
| `app/Filament/Presensi/Resources/HariLiburs/HariLiburResource.php` | **Ubah** `Pengaturan Sistem` → `Presensi` | sort: 7 |
| `app/Filament/Presensi/Pages/ManajemenNotifikasiWaPage.php` | **Ubah** `Pengaturan` → `Presensi` | sort: 8 |
| `app/Filament/Presensi/Pages/PengaturanPresensiPage.php` | **Ubah** `Pengaturan` → `Presensi` | sort: 9 |

---

### Grup: Perpustakaan

| File | Perubahan Group | Perubahan Sort |
|---|---|---|
| `app/Filament/Perpustakaan/Resources/Bukus/BukuResource.php` | **Ubah** `Koleksi Buku` → `Perpustakaan` | sort: 1 |
| `app/Filament/Perpustakaan/Resources/InventarisBukus/InventarisBukuResource.php` | **Ubah** `Koleksi Buku` → `Perpustakaan` | sort: 2 |
| `app/Filament/Perpustakaan/Resources/KategoriBukus/KategoriBukuResource.php` | **Ubah** `Koleksi Buku` → `Perpustakaan` | sort: 3 |
| `app/Filament/Perpustakaan/Resources/KlasifikasiDdcs/KlasifikasiDdcResource.php` | **Ubah** `Koleksi Buku` → `Perpustakaan` | sort: 4 |
| `app/Filament/Perpustakaan/Resources/PeminjamanAktifResource.php` | **Ubah** `Sirkulasi` → `Perpustakaan` | sort: 5 |
| `app/Filament/Perpustakaan/Resources/RiwayatPengembalianResource.php` | **Ubah** `Sirkulasi` → `Perpustakaan` | sort: 6 |
| `app/Filament/Perpustakaan/Resources/KunjunganPerpustakaanResource.php` | **Ubah** `Sirkulasi` → `Perpustakaan` | sort: 7 |
| `app/Filament/Perpustakaan/Pages/ReservasiSegeraHadir.php` | **Ubah** `Sirkulasi` → `Perpustakaan` | sort: 8 |
| `app/Filament/Perpustakaan/Pages/LaporanSirkulasi.php` | **Ubah** `Laporan` → `Perpustakaan` | sort: 9 |
| `app/Filament/Perpustakaan/Pages/AnggotaResource.php` | **Ubah** `Keanggotaan` → `Perpustakaan` | sort: 10 |
| `app/Filament/Perpustakaan/Pages/ImportSlims.php` | **Ubah** `Pengaturan` → `Perpustakaan` | sort: 11 |
| `app/Filament/Perpustakaan/Pages/PengaturanPerpustakaan.php` | **Ubah** `Pengaturan` → `Perpustakaan` | sort: 12 |

---

### Grup: Pengaturan Sistem

| File | Perubahan Group | Perubahan Sort |
|---|---|---|
| `app/Filament/Pages/SchoolSettingsPage.php` | (cek `Pengaturan Sistem`) | sort: 1 |
| `app/Filament/Pages/ThemeSettingsPage.php` | (cek `Pengaturan Sistem`) | sort: 2 |

---

## Cara Verifikasi Setelah Tahap 2

1. Buka `/admin` sidebar → cek urutan grup sudah sesuai:
   **Data Master → Akademik → Presensi → Perpustakaan → Pengaturan Sistem**
2. Buka tiap grup → cek urutan menu di dalamnya sudah rapi dan logis
3. Tidak ada grup nama lama yang muncul (`Koleksi Buku`, `Sirkulasi`, `Keanggotaan`, `Laporan`, `Konten`, dll.)
