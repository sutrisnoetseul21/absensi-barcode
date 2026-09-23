# SPESIFIKASI TEKNIS FITUR STRATEGIS & KESIAPAN LOMBA APRESIASI GTK DEDIKATIF 2026

> **Dokumen Panduan Optimalisasi Aplikasi Guru Wali (SI-WALI)**  
> **Kategori Lomba**: Apresiasi GTK Dedikatif 2026  
> **Unit Kerja**: SMPN 3 Kedungreja (Sekolah Pinggiran Perbatasan Kabupaten Cilacap)  
> **Tema Karya**: Tema 9 — Mentoring Skill dan Pendampingan  
> **Landasan Regulasi**: Permendikdasmen No. 11 Tahun 2025, Kepmendikdasmen No. 221/P/2025, & Pedoman Penghargaan GTK 2026 (Keputusan Ditjen GTK No. 45 Tahun 2026)

---

## 1. Pemetaan Fitur Aplikasi terhadap Aspek Penilaian GTK Dedikatif

Berdasarkan **Pedoman Penyelenggaraan Penghargaan GTK 2026**, jalur **Apresiasi GTK Dedikatif** menitikberatkan pada **resiliensi guru, tingkat kesulitan tempat bertugas, serta dampak perubahan positif pada siswa**. Berikut adalah penyesuaian pemetaan aplikasi SI-WALI di SMPN 3 Kedungreja:

| No | Indikator Penilaian GTK Dedikatif | Kondisi Faktual & Karakteristik SMPN 3 Kedungreja | Implementasi Fitur Aplikasi SI-WALI |
| :---: | :--- | :--- | :--- |
| **1** | **Tingkat Kesulitan & Tantangan Lingkungan (Bobot Utama)** | Sekolah di wilayah pinggiran perbatasan. Siswa cenderung pemalu, canggung, dan tertutup mengenai masalah akademik/sosial. | **Safe-Space Consultation Signal**: Opsi inisiasi konsultasi cepat privat tanpa harus mengetik narasi panjang. |
| **2** | **Resiliensi & Inisiatif Solutif Guru** | Keterbatasan saluran konsultasi privat di sekolah pinggiran memicu guru membangun solusi digital terjangkau dan mudah diakses. | **Inovasi Portal Dua Arah & Auto-Convert**: Mengubah sinyal konsultasi siswa menjadi Jurnal Pendampingan resmi. |
| **3** | **Kesesuaian & Kedalaman Tema Mentoring** | Perlunya pemantauan holistik 100% murid secara berkala tanpa ada siswa pinggiran yang terabaikan. | **Form 4 Pilar & Indikator Pemantauan Bulanan**: Visual 🟢/🔴 untuk memastikan min. 1x interaksi/bulan per murid. |
| **4** | **Dampak Perubahan Perilaku Siswa** | Peningkatan keberanian dan keterbukaan siswa perbatasan dalam menyampaikan kendala belajar dan sosial. | **Dashboard Impact Analytics**: Grafik pertumbuhan inisiatif konsultasi mandiri siswa dari bulan ke bulan. |
| **5** | **Kolaborasi & Penanganan Lintas Peran** | Perlunya rujukan yang aman bila masalah siswa membutuhkan penanganan Guru BK, Wali Kelas, atau Orang Tua. | **Form Rujukan Digital & Bridge Kerahasiaan (Strict Isolation)**: Jurnal privat tetapi rujukan terintegrasi. |
| **6** | **Keberlanjutan & Pengimbasan** | Kemudahan aplikasi untuk di-clone atau direplikasi oleh guru di sekolah perbatasan/pinggiran lainnya. | **Modul Replikasi & Exporter Template**: Ekspor konfigurasi aplikasi untuk pengimbasan antar-sekolah. |

---

## 2. Rincian Fitur Utama Aplikasi SI-WALI (Perspektif GTK Dedikatif)

### A. Fitur 1: Safe-Space Consultation Signal (Solusi Siswa Perbatasan)
* **Tujuan**: Meruntuhkan tembok rasa malu dan canggung pada murid di sekolah pinggiran/perbatasan.
* **Mekanisme**:
  * Siswa dapat menekan tombol **"Sinyal Pendampingan Privat"** di Portal Siswa dengan 3 pilihan respon cepat:
    1. 🟢 *Ingin Diskusi Ringan*
    2. 🟡 *Butuh Masukan Akademik/Ekskul*
    3. 🔴 *Butuh Teman Bicara (Privat/Segera)*
  * Siswa tidak diwajibkan mengetik penjelasan panjang jika belum siap, cukup memilih mode: *Tatap Muka Privat* atau *Pesan Tertulis Portal*.

### B. Fitur 2: Dashboard Analitik Impact (Bukti Keterbukaan Murid)
* **Tujuan**: Menampilkan bukti data kuantitatif perubahan keterbukaan siswa perbatasan sebagai bahan Naskah Best Practice & Video.
* **Komponen Visual**:
  * **Tingkat Partisipasi Mandiri**: Grafik kenaikan persentase siswa yang mengajukan konsultasi atas inisiatif sendiri.
  * **Peta Sebaran 4 Pilar**: Komposisi isu yang dihadapi murid (Akademik, Karakter, Minat/Bakat, Sosial-Psikologis).
  * **Ketercapaian Target Bulanan**: Kepastian 100% murid dampingan tersentuh pendampingan setiap bulan.

### C. Fitur 3: Student Encouragement Badge (Penguatan Karakter Positif)
* **Tujuan**: Membangun rasa percaya diri dan motivasi anak-anak di daerah perbatasan.
* **Mekanisme**:
  * Guru Wali dapat memberikan **Badge Karakter** (seperti *Pembelajar Gigih*, *Resilien*, *Empatis*, *Kreatif*) setelah sesi pendampingan, yang akan tampil di dashboard portal siswa.

### D. Fitur 4: Modul Replikasi & Exporter Template (Pengimbasan)
* **Tujuan**: Menunjukkan daya inspirasi dan pengimbasan karya ke sekolah-sekolah sejenis di wilayah perbatasan/pinggiran.
* **Mekanisme**: Fitur ekspor konfigurasi terstandar agar sistem dapat diimplementasikan di sekolah lain dalam hitungan menit.

---

## 3. Skema Basis Data Tambahan (Database Schema Extension)

```sql
-- 1. Tabel Sinyal Safe Space Konsultasi Siswa
CREATE TABLE sinyal_konsultasi_siswa (
    id VARCHAR(36) PRIMARY KEY,
    siswa_id VARCHAR(36) NOT NULL,
    guru_id VARCHAR(36) NOT NULL,
    tingkat_urgensi ENUM('Ringan', 'Sedang', 'Privat_Segera') DEFAULT 'Ringan',
    kategori_pendampingan ENUM('Akademik', 'Karakter & Kedisiplinan', 'Minat & Bakat / Ekskul', 'Sosial & Psikologis') NOT NULL,
    mode_konsultasi ENUM('Tatap_Muka', 'Pesan_Portal') DEFAULT 'Tatap_Muka',
    pesan_inisiasi TEXT NULL, -- Boleh kosong bila siswa belum siap mengetik
    status ENUM('Menunggu_Respon', 'Dijadwalkan', 'Selesai', 'Dibatalkan') DEFAULT 'Menunggu_Respon',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (siswa_id) REFERENCES siswa(id),
    FOREIGN KEY (guru_id) REFERENCES guru(id)
);

-- 2. Tabel Badge Karakter & Apresiasi Siswa
CREATE TABLE badge_karakter_siswa (
    id VARCHAR(36) PRIMARY KEY,
    jurnal_id VARCHAR(36) NOT NULL,
    siswa_id VARCHAR(36) NOT NULL,
    nama_badge VARCHAR(50) NOT NULL, -- Contoh: "Pembelajar Gigih", "Anak Resilien"
    catatan_penguatan TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (jurnal_id) REFERENCES jurnal_guru_wali(id),
    FOREIGN KEY (siswa_id) REFERENCES siswa(id)
);
```

---

## 4. Checklist Berkas & Ketentuan Pendaftaran GTK Dedikatif 2026

- [x] **Dokumen Perencanaan 1 Tahun Ajaran**: `rencana-pendampingan-guru-wali.docx`
- [x] **Jurnal Harian & Rekap Bulanan**: `jurnal-harian-guru-wali.docx` & `rekap-pemantauan-bulanan-guru-wali.docx`
- [x] **Form Rujukan / Escalation**: `form-rujukan-kolaborasi-guru-wali.docx`
- [x] **Laporan Individual & Rapor Portofolio**: `laporan-pendampingan-individual-siswa.docx` & `rapor-portofolio-perkembangan-diri-siswa.docx`
- [x] **Dokumen Laporan Periodik Tahunan**: `laporan-pendampingan-periodik-tahunan.docx`
- [x] **Dokumen Spesifikasi Teknis AI Agent & Developer**: 8 file `.md` spesifikasi di Studio.
- [ ] **Surat Pengusulan Dinas Pendidikan**: Pengusulan resmi dari Dinas Pendidikan Kabupaten Cilacap.
- [ ] **Naskah Karya Tulis Best Practice (STAR)**: 1.500–2.000 kata berlatar SMPN 3 Kedungreja.
- [ ] **Video Praktik Baik GTK Dedikatif**: Durasi **5 – 10 menit** (Format Landscape 16:9 HD, menampilkan suasana SMPN 3 Kedungreja dan interaksi pendampingan).
