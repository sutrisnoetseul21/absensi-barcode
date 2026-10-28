# SPESIFIKASI TEKNIS FITUR: GENERATOR RAPOR / PORTOFOLIO PERKEMBANGAN DIRI MURID

> **Dokumen Panduan Pengembang & AI Agent**  
> **Versi**: 1.0  
> **Tanggal**: September 2026  
> **Landasan Regulasi**: Permendikdasmen No. 11 Tahun 2025 & Kepmendikdasmen No. 221/P/2025  

---

## 1. Ringkasan Eksekutif & Konsep Fitur

Dokumen spesifikasi ini mengatur pembangunan **Generator Rapor / Portofolio Perkembangan Diri Murid** pada portal guru dan portal siswa. 

Berbeda dengan Rapor Akademik (Nilai Kognitif) bulanan/semesteran dari Wali Kelas, dokumen ini fokus pada **rekam jejak perkembangan holistik murid (multi-tahun)** yang dibimbing langsung oleh **Guru Wali**.

### Fitur Utama:
* **Generasi Otomatis (Auto-Agregasi)**: Mengompilasi seluruh catatan harian dari `jurnal_guru_wali` dan riwayat `konsultasi_guru_wali` dua arah dalam 1 tahun ajaran.
* **Segmentasi 4 Pilar Utama**: Mengelompokkan narasi perkembangan murid ke dalam **Akademik**, **Karakter & Kedisiplinan**, **Minat & Bakat / Ekskul**, serta **Sosial & Psikologis** per Semester (Ganjil & Genap).
* **Komponen Refleksi Diri Siswa**: Menampilkan sudut pandang pribadi murid mengenai capaian yang dibanggakan dan tantangan yang berhasil diatasi.
* **Keamanan & Privasi**: Hanya dapat diakses dan di-generate oleh Guru Wali Pengampu, Siswa yang bersangkutan (serta Orang Tua/Wali), dan Kepala Sekolah.

---

## 2. Arsitektur Data & Kueri Agregasi SQL

### A. Tabel Tambahan: `refleksi_siswa_portofolio`
Tabel untuk menyimpan data masukan refleksi mandiri siswa pada akhir semester/tahun ajaran.

```sql
CREATE TABLE refleksi_siswa_portofolio (
    id VARCHAR(36) PRIMARY KEY,
    siswa_id VARCHAR(36) NOT NULL,
    tahun_ajaran VARCHAR(10) NOT NULL, -- Contoh: "2025/2026"
    capaian_dibanggakan TEXT NOT NULL,
    tantangan_solusi TEXT NOT NULL,
    cita_cita_target TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (siswa_id) REFERENCES siswa(id) ON DELETE CASCADE,
    CONSTRAINT unique_refleksi_siswa_tahun UNIQUE (siswa_id, tahun_ajaran)
);
```

### B. Kueri Agregasi Sesi & Distribusi Pilar
Kueri SQL untuk mengagregasi total interaksi pendampingan seorang murid dalam 1 tahun ajaran:

```sql
SELECT 
    s.id AS siswa_id,
    s.nama_lengkap,
    COUNT(j.id) AS total_sesi_terlaksana,
    SUM(CASE WHEN j.jenis_pendampingan = 'Individu' THEN 1 ELSE 0 END) AS total_sesi_individu,
    SUM(CASE WHEN j.jenis_pendampingan IN ('Kelompok Kecil', 'Klasikal') THEN 1 ELSE 0 END) AS total_sesi_kelompok,
    (
        SELECT COUNT(k.id) 
        FROM konsultasi_guru_wali k 
        WHERE k.siswa_id = s.id 
          AND k.tahun_ajaran = '2025/2026'
    ) AS total_inisiatif_mandiri_siswa,
    SUM(CASE WHEN j.kategori_pendampingan = 'Akademik' THEN 1 ELSE 0 END) AS count_akademik,
    SUM(CASE WHEN j.kategori_pendampingan = 'Karakter & Kedisiplinan' THEN 1 ELSE 0 END) AS count_karakter,
    SUM(CASE WHEN j.kategori_pendampingan = 'Minat & Bakat / Ekskul' THEN 1 ELSE 0 END) AS count_minat_bakat,
    SUM(CASE WHEN j.kategori_pendampingan = 'Sosial & Psikologis' THEN 1 ELSE 0 END) AS count_sosial
FROM siswa s
LEFT JOIN jurnal_guru_wali j ON s.id = j.siswa_id
WHERE s.id = :siswa_id
  AND j.tanggal_waktu BETWEEN :tanggal_mulai_ta AND :tanggal_selesai_ta
GROUP BY s.id, s.nama_lengkap;
```

---

## 3. Matriks Tata Letak & Pemetaan Komponen Tabel (DOCX / PDF Engine)

Generator laporan (Docx / PDF Engine) menyusun dokumen dengan urutan komponen sebagai berikut:

| No | Nama Blok Komponen | Sumber Data | Format Tampilan |
| :---: | :--- | :--- | :--- |
| **1** | **Identitas Murid & Tim Pendamping** | `siswa`, `kelompok_guru_wali`, `guru`, `guru_bk`, `wali_kelas` | Tabel 4 Kolom (Label bold dengan background light grey `#F0F4F8`). |
| **2** | **Matriks 4 Pilar (Sem 1 vs Sem 2)** | Agregasi Narasi `jurnal_guru_wali` terfilter per Semester & Kategori | Tabel 3 Kolom (`Pilar Utama`, `Semester 1 (Ganjil)`, `Semester 2 (Genap)`). Header Navy `#2F5496`. |
| **3** | **Rekap Sesi & Konsultasi Mandiri** | Count Kueri SQL | Tabel Matriks 4 Kolom dengan highlight angka total sesi. |
| **4** | **Refleksi Siswa & Catatan Guru Wali** | `refleksi_siswa_portofolio` & Catatan Kesimpulan Guru Wali | Tabel 2 Kolom (Pertanyaan Refleksi vs Jawaban Naratif Siswa/Guru). |
| **5** | **Lembar Pengesahan** | Dynamic Sign Block | 3 Kolom Tanda Tangan (Murid, Orang Tua, Guru Wali) + 1 Center Block (Kepala Sekolah). |

---

## 4. Spesifikasi REST API Specification

### A. GET `/api/guru-wali/portofolio/:siswa_id`
Digunakan oleh portal guru dan portal siswa untuk menarik seluruh payload data portofolio.

* **Headers**: `Authorization: Bearer <token>`
* **Query Parameters**: `tahun_ajaran=2025/2026`
* **Response Sample (JSON)**:

```json
{
  "status": "success",
  "data": {
    "identitas_murid": {
      "siswa_id": "sis-001",
      "nama_lengkap": "Rizky Pratama",
      "nis_nisn": "202510102 / 0089212345",
      "kelas": "X-1 (Fase E)",
      "nama_kelompok": "Kelompok Dandelion",
      "tahun_ajaran": "2025/2026",
      "guru_wali": "Dra. Siti Aminah, M.Pd",
      "nip_guru_wali": "19780512 200501 2 003",
      "wali_kelas": "Budi Santoso, S.Pd",
      "guru_bk": "Lestari, S.Psi, M.Si"
    },
    "capaian_4_pilar": {
      "akademik": {
        "semester_1": "Menunjukkan kemajuan pada pemahaman Aljabar setelah sesi peer tutoring. Konsisten mengumpulkan tugas tepat waktu.",
        "semester_2": "Nilai rata-rata akademik stabil. Mampu mengaplikasikan teknik membuat peta konsep dalam merangkum materi."
      },
      "karakter": {
        "semester_1": "Sangat aktif dalam komitmen kelompok. Tingkat kehadiran 98% dan patuh pada kesepakatan tata tertib kelas.",
        "semester_2": "Menjadi teladan kedisiplinan di kelompok dampingan. Menunjukkan sikap menghargai pendapat teman."
      },
      "minat_bakat": {
        "semester_1": "Aktif dalam Ekskul Robotik dan berhasil menyelesaikan proyek kelompok IPA Energi Alternatif.",
        "semester_2": "Mewakili sekolah dalam kompetisi Robotik tingkat kota. Memiliki bakat kepemimpinan kelompok yang menonjol."
      },
      "sosial_psikologis": {
        "semester_1": "Berhasil mengatasi kebiasaan prokrastinasi melalui pembuatan jadwal harian pribadi.",
        "semester_2": "Mempunyai hubungan sosial yang harmonis dengan teman sebaya. Mampu mengelola stres saat minggu ujian."
      }
    },
    "statistik_pendampingan": {
      "total_sesi": 12,
      "inisiatif_mandiri_siswa": 3,
      "sesi_individu": 5,
      "sesi_kelompok": 7
    },
    "refleksi_diri_siswa": {
      "capaian_dibanggakan": "Saya merasa bangga bisa mengalahkan kebiasaan menunda tugas dan berhasil mewakili sekolah dalam kompetisi Robotik.",
      "tantangan_solusi": "Membagi waktu antara jadwal latihan ekskul dan persiapan ujian harian.",
      "cita_cita_target": "Ingin terus mengasah kemampuan Aljabar & Pemrograman serta menjadi ketua tim kompetisi Robotik di Kelas XI."
    },
    "catatan_guru_wali": "Rizky menunjukkan peningkatan kedewasaan dan motivasi belajar yang luar biasa. Pertahankan kedisiplinan dan jiwa kepemimpinan positif ini."
  }
}
```

### B. POST `/api/siswa/portofolio/refleksi`
Endpoint di Portal Siswa untuk mengisi/mengedit refleksi mandiri sebelum dokumen portofolio di-generate.

* **Payload Request**:
```json
{
  "tahun_ajaran": "2025/2026",
  "capaian_dibanggakan": "Saya merasa bangga bisa mengalahkan kebiasaan menunda tugas...",
  "tantangan_solusi": "Membagi waktu antara jadwal latihan ekskul...",
  "cita_cita_target": "Ingin terus mengasah kemampuan Aljabar..."
}
```

---

## 5. Matriks Hak Akses Data & Keamanan (Role-Based Access)

| Peran (Role) | Hak Akses Input Refleksi | Hak Akses Generate DOCX / PDF | Keterangan Security |
| :--- | :---: | :---: | :--- |
| **Guru Wali Pengampu** | Read-Only | **FULL ACCESS** | Hanya untuk siswa anggota kelompok dampingannya sendiri. |
| **Siswa Dampingan** | **FULL ACCESS** | **FULL ACCESS** | Hanya untuk dokumen portofolio dirinya sendiri. |
| **Orang Tua / Wali** | Read-Only | **FULL ACCESS** | Mengakses via akun portal siswa terkait. |
| **Kepala Sekolah / Admin** | Read-Only | **FULL ACCESS** | Untuk verifikasi evaluasi tahunan & tanda tangan pengesahan. |
| **Guru Wali Lain / Wali Kelas / BK** | **NO ACCESS** | **NO ACCESS** | Terisolasi (*403 Forbidden*) untuk menjaga kerahasiaan catatan refleksi. |

---

## 6. Checklist Pengujian AI Agent & Developer (Acceptance Criteria)

1. [ ] **Verifikasi Agregasi Data**: Generator mengompilasi seluruh record jurnal dan konsultasi mandiri siswa dalam rentang tahun ajaran yang dipilih secara presisi.
2. [ ] **Verifikasi 4 Pilar**: Narasi perkembangan terbagi sempurna ke dalam 4 kategori (Akademik, Karakter, Minat/Bakat, Sosial/Psikologis) per semester.
3. [ ] **Integrasi Input Refleksi Siswa**: Data yang diisi oleh siswa pada portal siswa secara otomatis ditarik masuk ke dalam tabel refleksi dokumen.
4. [ ] **Pengesahan 4 Tanda Tangan**: Layout dokumen DOCX/PDF menyediakan 4 blok pengesahan resmi (Siswa, Orang Tua, Guru Wali, Kepala Sekolah).
