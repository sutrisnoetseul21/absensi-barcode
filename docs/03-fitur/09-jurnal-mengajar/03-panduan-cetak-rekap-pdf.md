# 🖨️ Panduan Cetak Rekapitulasi & Ekspor Excel Jurnal Mengajar

Dokumen ini menjelaskan format standar, tata letak (*layout*), konfigurasi kop dinamis sekolah, modal dialog interaktif (model cetak Akademik), serta parameter filter untuk mencetak laporan rekapitulasi Jurnal Mengajar dalam bentuk **PDF** siap cetak maupun spreadsheet **Excel (.xlsx)**.

---

## 1. Dialog Modal Cetak Interaktif (Model Akademik)

Mengadopsi pola antarmuka cetak absensi di `/portal-guru/akademik`, tombol **"Cetak Laporan KBM"** di halaman Jurnal Mengajar membuka **Modal Dialog Interaktif** ([`resources/views/livewire/portal-guru/jurnal-mengajar/modal-cetak.blade.php`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/resources/views/livewire/portal-guru/jurnal-mengajar/modal-cetak.blade.php)).

### Pilihan Periode Laporan:
1. **Bulanan**:
   - Memilih bulan spesifik dari daftar bulan aktif dalam Tahun Ajaran berjalan (format `MM-YYYY`, misal: *Oktober 2026*).
   - Menghasilkan rekap KBM dari tanggal 1 hingga hari terakhir bulan yang dipilih.
2. **Per Semester**:
   - **Semester Ganjil**: 1 Juli s.d. 31 Desember tahun ajaran awal.
   - **Semester Genap**: 1 Januari s.d. 30 Juni tahun ajaran akhir.
3. **Satu Tahun Ajaran Penuh**:
   - 1 Juli tahun ajaran awal s.d. 30 Juni tahun ajaran akhir.

### Filter Tambahan:
- **Kelas / Rombel**: Pilihan rombongan belajar (Semua Kelas atau rombel spesifik yang diampu).
- **Mata Pelajaran**: Pilihan mata pelajaran (Semua Mapel atau mapel spesifik).
- **Guru Pengampu** *(Khusus Mode Supervisi / Kepala Sekolah)*: Pilihan guru yang disupervisi atau semua guru.

---

## 2. Akses & Parameter Endpoint Cetak & Ekspor

Dikelola oleh Controller [`App\Http\Controllers\JurnalMengajarCetakController`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Http/Controllers/JurnalMengajarCetakController.php):

| Rute URL | Method | Output | Handler Method |
| :--- | :---: | :---: | :--- |
| `/portal-guru/jurnal-mengajar/cetak-pdf` | `GET` | Stream PDF (In-browser / Print) | `cetak(Request $request)` |
| `/portal-guru/jurnal-mengajar/download-excel` | `GET` | Download File `.xlsx` | `excel(Request $request)` |

### Parameter Query yang Didukung:
| Parameter | Tipe Data | Keterangan |
| :--- | :--- | :--- |
| `periode_type` | String | `bulanan`, `semester`, atau `tahunan` (otomatis menghitung `date_from` dan `date_to`). |
| `bulan_year` | String (`m-Y`) | Digunakan jika `periode_type=bulanan` (misal: `10-2026`). |
| `semester` | String | Digunakan jika `periode_type=semester` (`ganjil` atau `genap`). |
| `date_from` | Date (`Y-m-d`) | Batas awal tanggal pelaksanaan KBM (jika manual). |
| `date_to` | Date (`Y-m-d`) | Batas akhir tanggal pelaksanaan KBM (jika manual). |
| `class_id` | UUID | Filter rombongan belajar tertentu (opsional). |
| `mata_pelajaran_id` | Bigint | Filter mata pelajaran tertentu (opsional). |
| `teacher_id` | UUID | ID Guru Pengampu (khusus Supervisi / Super Admin). |
| `academic_year_id` | UUID | ID Tahun Ajaran (otomatis aktif jika kosong). |

---

## 3. Format & Tata Letak Dokumen

### A. Format PDF (`resources/views/pdf/jurnal-mengajar-rekap.blade.php`)
1. **Orientasi Kertas**: Format **Landscape** (A4) untuk keterbacaan optimal tabel materi dan uraian KBM.
2. **Kop Surat Sekolah Dinamis**:
   - Logo sekolah resmi dari tabel `school_settings` (`school_logo_path`).
   - Nama instansi, nama sekolah, alamat lengkap, NPSN, dan email sekolah.
   - Garis pembatas ganda formal.
3. **Identitas Pembelajaran & Guru**:
   - Nama Guru Pengampu & NIP.
   - Mata Pelajaran & Kelas Rombel.
   - Tahun Ajaran, Semester, dan Label Periode Dinamis (misal: *Bulan Oktober 2026* atau *Semester Ganjil TA 2026/2027*).
4. **Tabel Rekapitulasi Pembelajaran**:
   - No, Hari & Tanggal KBM, Jam Pelajaran (JP), Materi Pokok, Uraian Aktivitas KBM, Catatan / Refleksi, dan Rekap Kehadiran Siswa.
5. **Kolom Pengesahan Tanda Tangan**:
   - Titimangsa otomatis kota sekolah dan tanggal cetak.
   - Tanda tangan sebelah kiri: **Kepala Sekolah** lengkap dengan NIP.
   - Tanda tangan sebelah kanan: **Guru Mata Pelajaran** lengkap dengan NIP.

### B. Format Excel Spreadsheet (`App\Exports\JurnalMengajarExport` & `resources/views/excel/jurnal-mengajar-rekap.blade.php`)
1. **Struktur Lembar Kerja (Worksheet)**:
   - Header Dokumen & Identitas Guru/Mapel/Kelas/Periode.
   - Kolom Data KBM: Nomor, Hari/Tanggal, Jam Ke, JP, Kelas, Mata Pelajaran, Materi Pokok, Uraian Aktivitas, Catatan/Refleksi, Hadir, Sakit, Izin, Alpa, Total Siswa, dan Persentase Kehadiran (%).
   - Baris Total Akumulatif: Rangkuman total pertemuan, total JP terlaksana, dan rata-rata persentase kehadiran kelas.
   - Kolom Tanda Tangan Pengesahan (Kepala Sekolah & Guru Pengampu).
2. **Teknologi**: Menggunakan `maatwebsite/excel` dengan implementasi `FromView`, `ShouldAutoSize`, `WithStyles`, dan `WithTitle`.
3. **Nama File Dinamis**: Format `Rekap_Jurnal_Mengajar_[NamaGuru]_[Periode]_[Timestamp].xlsx`.

---

## 4. Cara Penggunaan di Antarmuka Guru

1. Masuk ke **Portal Guru &rarr; Jurnal Mengajar** (`/portal-guru/jurnal-mengajar`).
2. Klik tombol **"Cetak Laporan KBM"** di pojok kanan atas.
3. Pada pop-up modal:
   - Pilih **Jenis Periode** (Bulanan, Per Semester, atau Satu Tahun Ajaran).
   - Tentukan **Bulan** atau **Semester** yang diinginkan.
   - Saring **Kelas** dan/atau **Mata Pelajaran** jika hanya ingin mencetak rombel tertentu.
4. Klik salah satu tombol aksi:
   - 🟢 **Unduh Excel**: Mengunduh berkas `.xlsx` untuk pengolahan data spreadsheet atau arsip administrasi kurikulum.
   - 🔴 **Cetak PDF**: Membuka berkas PDF resmi siap cetak / simpan dokumen digital.
