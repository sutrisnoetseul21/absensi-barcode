# 🖨️ Panduan Cetak Rekapitulasi Jurnal Mengajar (PDF)

Dokumen ini menjelaskan format standar, tata letak (*layout*), konfigurasi kop dinamis sekolah, serta parameter filter untuk mencetak laporan rekapitulasi Jurnal Mengajar dalam bentuk PDF siap cetak.

---

## 1. Akses & Parameter Cetak PDF

Fitur cetak rekapitulasi jurnal tersedia melalui rute:
`GET /portal-guru/jurnal-mengajar/cetak-pdf`
(dikelola oleh Controller [`App\Http\Controllers\JurnalMengajarCetakController`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Http/Controllers/JurnalMengajarCetakController.php))

### Parameter Query yang Didukung:
| Parameter | Tipe Data | Keterangan |
| :--- | :--- | :--- |
| `academic_year_id` | UUID | ID Tahun Ajaran yang ingin dicetak (otomatis aktif jika kosong). |
| `teacher_id` | UUID | ID Guru Pengampu (khusus Supervisi / Super Admin). Jika kosong / guru reguler, otomatis guru login. |
| `mata_pelajaran_id` | Bigint | Filter cetak per mata pelajaran tertentu (opsional). |
| `class_id` | UUID | Filter cetak per rombongan belajar tertentu (opsional). |
| `date_from` | Date (`Y-m-d`) | Batas awal tanggal pelaksanaan KBM. |
| `date_to` | Date (`Y-m-d`) | Batas akhir tanggal pelaksanaan KBM. |

---

## 2. Format & Standar Tata Letak Dokumen

Tampilan cetak dirancang menggunakan view [`resources/views/pdf/jurnal-mengajar-rekap.blade.php`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/resources/views/pdf/jurnal-mengajar-rekap.blade.php) dengan spesifikasi resmi dokumen kedinasan sekolah:

1. **Orientasi Kertas**:
   * Format **Landscape** (A4) untuk keterbacaan optimal tabel materi dan uraian KBM.
2. **Kop Surat Sekolah Dinamis**:
   * Logo sekolah resmi dari tabel `school_settings` (`school_logo_path`).
   * Nama instansi, nama sekolah, alamat lengkap, NPSN, dan email sekolah.
   * Garis pembatas ganda formal.
3. **Identitas Pembelajaran & Guru**:
   * Nama Guru Pengampu & NIP.
   * Mata Pelajaran & Kelas Rombel.
   * Tahun Ajaran & Semester aktif.
   * Rentang Periode Tanggal KBM.
4. **Tabel Rekapitulasi Pembelajaran**:
   * **No**: Nomor urut pertemuan KBM.
   * **Hari & Tanggal**: Hari dan tanggal dalam format Bahasa Indonesia (misal: *Rabu, 28 Oktober 2026*).
   * **Jam Ke (JP)**: Jam pelajaran terlaksana (misal: *Jam ke 1 - 2 (2 JP)*).
   * **Materi Pokok**: Bab / Kompetensi Dasar / Pokok Bahasan.
   * **Uraian Aktivitas KBM**: Ringkasan metode pembelajaran dan penugasan.
   * **Catatan / Refleksi**: Evaluasi ketercapaian dan tindak lanjut.
   * **Kehadiran Siswa**: Rekap siswa hadir vs tidak mengikuti (misal: *30 / 32 Siswa*).
5. **Kolom Pengesahan Tanda Tangan**:
   * Titimangsa otomatis kota sekolah dan tanggal cetak.
   * Tanda tangan sebelah kiri: **Kepala Sekolah** lengkap dengan NIP.
   * Tanda tangan sebelah kanan: **Guru Mata Pelajaran** lengkap dengan NIP.

---

## 3. Cara Mengunduh & Mencetak

### Dari Portal Guru:
1. Buka menu **Jurnal Mengajar** (`/portal-guru/jurnal-mengajar`).
2. Gunakan filter di bagian atas jika ingin mencetak rentang tanggal, mapel, atau kelas tertentu.
3. Klik tombol **"Cetak Rekap PDF"** di pojok kanan atas.
4. Dokumen akan terbuka di tab baru peramban dengan dialog cetak / opsi unduh PDF.

### Dari Panel Admin Filament:
1. Buka menu **Akademik &rarr; Jurnal Mengajar** (`/admin/jurnal-mengajar`).
2. Klik tombol aksi **"Cetak Rekap PDF"** di *header* halaman.
3. Rekapitulasi KBM akan terunduh langsung sesuai filter aktif.
