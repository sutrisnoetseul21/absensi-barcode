# SPESIFIKASI TEKNIS FITUR: REKAP PEMANTAUAN BULANAN GURU WALI

> **Dokumen Panduan Pengembang & AI Agent**  
> **Versi**: 1.0  
> **Tanggal**: September 2026  
> **Target Document Artifact**: `rekap-pemantauan-bulanan-guru-wali.docx`  
> **Landasan Regulasi**: Permendikdasmen No. 11 Tahun 2025 & Kepmendikdasmen No. 221/P/2025 (Ekuivalensi Beban Kerja 2 JP/Minggu)

---

## 1. Ringkasan Eksekutif & Tujuan Fitur

Dokumen spesifikasi ini mengatur pembuatan dan generasi otomatis **Lembar Presensi & Rekap Pemantauan Bulanan Guru Wali**. Fitur ini berfungsi sebagai instrumen pengawasan administratif dan bukti fisik (*evidence*) ekuivalensi beban kerja Guru Wali sebesar 2 JP/minggu.

### Fokus & Indikator Kunci:
* **Target Minimum Regulasi**: Memastikan setiap murid bimbingan dalam kelompok dampingan mendapatkan interaksi pendampingan langsung **sekurang-kurangnya 1 kali dalam sebulan** sepanjang tahun ajaran (Juli s.d. Juni).
* **Automasi Matriks Matrix**: Sistem secara otomatis mengagregasi data transaksi dari `jurnal_guru_wali` menjadi matriks pemantauan 12 bulan.
* **Akuntabilitas Supervisi**: Laporan menyajikan ringkasan persentase ketercapaian kelompok serta kolom verifikasi dan pengesahan resmi oleh Kepala Sekolah.

---

## 2. Pipa Logika Data & Kueri SQL (Database Pipeline)

Sistem menarik data dari tabel `jurnal_guru_wali`, `kelompok_guru_wali_siswa`, dan `siswa` untuk membentuk matriks pemantauan 12 bulan.

### A. Kueri Agregasi Matriks Bulanan (Juli - Juni)

```sql
SELECT 
    s.id AS siswa_id,
    s.nama_lengkap AS nama_siswa,
    s.nisn,
    -- Hitung jumlah sesi pendampingan per bulan (Tahun Ajaran Juli YYYY - Juni YYYY+1)
    COUNT(CASE WHEN MONTH(j.tanggal_waktu) = 7 THEN 1 END) AS juli,
    COUNT(CASE WHEN MONTH(j.tanggal_waktu) = 8 THEN 1 END) AS agustus,
    COUNT(CASE WHEN MONTH(j.tanggal_waktu) = 9 THEN 1 END) AS september,
    COUNT(CASE WHEN MONTH(j.tanggal_waktu) = 10 THEN 1 END) AS oktober,
    COUNT(CASE WHEN MONTH(j.tanggal_waktu) = 11 THEN 1 END) AS november,
    COUNT(CASE WHEN MONTH(j.tanggal_waktu) = 12 THEN 1 END) AS desember,
    COUNT(CASE WHEN MONTH(j.tanggal_waktu) = 1 THEN 1 END) AS januari,
    COUNT(CASE WHEN MONTH(j.tanggal_waktu) = 2 THEN 1 END) AS februar,
    COUNT(CASE WHEN MONTH(j.tanggal_waktu) = 3 THEN 1 END) AS maret,
    COUNT(CASE WHEN MONTH(j.tanggal_waktu) = 4 THEN 1 END) AS april,
    COUNT(CASE WHEN MONTH(j.tanggal_waktu) = 5 THEN 1 END) AS mei,
    COUNT(CASE WHEN MONTH(j.tanggal_waktu) = 6 THEN 1 END) AS juni,
    COUNT(j.id) AS total_sesi
FROM kelompok_guru_wali_siswa kgws
JOIN siswa s ON kgws.siswa_id = s.id
LEFT JOIN jurnal_guru_wali j ON j.siswa_id = s.id 
    AND j.tanggal_waktu BETWEEN :tanggal_mulai_ta AND :tanggal_selesai_ta
WHERE kgws.kelompok_id = :kelompok_id 
  AND kgws.status_aktif = TRUE
GROUP BY s.id, s.nama_lengkap, s.nisn
ORDER BY s.nama_lengkap ASC;
```

---

## 3. Logika Bisnis & Perhitungan Ketercapaian

### A. Evaluasi Per Bulan Per Murid
* Untuk setiap bulan \\(m \in \{1, 2, \dots, 12\}\\):
  * Jika \\(\text{Sesi}(m) \ge 1\\) \\(\rightarrow\\) Tanda Centang `✓` (Memenuhi).
  * Jika \\(\text{Sesi}(m) = 0\\) \\(\rightarrow\\) Tanda Silang `✗` atau Strip `-` (Belum Memenuhi).

### B. Status Ketercapaian Individu
* **Jumlah Bulan Terpenuhi**:
  \\[N_{\text{memenuhi}} = \sum_{m=1}^{12} \mathbb{I}(\text{Sesi}(m) \ge 1)\\]
* **Kriteria Status**:
  * Jika \\(N_{\text{memenuhi}} = 12\\) \\(\rightarrow\\) **"100% Memenuhi (12/12 Bulan)"** (🟢).
  * Jika \\(N_{\text{memenuhi}} < 12\\) \\(\rightarrow\\) **"Belum Memenuhi (N/12 Bulan)"** (🔴).

### C. Ringkasan Ketercapaian Kelompok (Summary Box)
* **Persentase Kelompok Tuntas**:
  \\[\text{Ketercapaian Kelompok (\%)} = \left( \frac{\text{Jumlah Murid dengan Status 100\% Memenuhi}}{\text{Total Murid Dampingan}} \right) \times 100\%\\]

---

## 4. Spesifikasi Struktur Tabel pada Dokumen `.docx`

Dokumen `rekap-pemantauan-bulanan-guru-wali.docx` dicetak dengan **Orientasi Landscape (A4)** agar memuat 17 kolom berikut:

| No Kolom | Header Kolom | Tipe Data / Tampilan | Lebar Relatif |
| :---: | :--- | :--- | :--- |
| **1** | No | Angka (1, 2, 3, ...) | 0.4 inch |
| **2** | Nama Murid Dampingan | String | 1.8 inch |
| **3** | NISN | String | 1.0 inch |
| **4–15** | Jul, Ags, Sep, Okt, Nov, Des, Jan, Feb, Mar, Apr, Mei, Jun | Symbol (`✓` / `-`) / Angka Sesi | @ 0.45 inch (x12) |
| **16** | Total Sesi | Integer (Jumlah Akumulasi) | 0.8 inch |
| **17** | Status Ketercapaian | String (`100% Memenuhi` / `Belum Memenuhi`) | 1.4 inch |

### Komponen Tambahan Dokumen:
1. **Header Informasi**:
   * Nama Guru Wali, NIP, Kelompok Dampingan, Tahun Ajaran, Unit Kerja/Sekolah.
2. **Ringkasan Indikator Kinerja**:
   * Total Murid Dampingan, Total Sesi Dilaksanakan, Persentase Ketercapaian Target Bulanan.
3. **Catatan Supervisi & Pengesahan**:
   * Kolom uraian evaluasi dari Kepala Sekolah.
   * Tanda tangan Guru Wali Pengampu dan Pengesahan Kepala Sekolah (+ NIP & Tanggal).

---

## 5. Spesifikasi API (Backend Endpoints)

### Endpoint: `GET /api/guru-wali/rekap-pemantauan`

Mendapatkan matriks rekapitulasi pemantauan bulanan dalam bentuk JSON.

* **Headers**: `Authorization: Bearer <token_guru>`
* **Query Parameters**:
  * `kelompok_id` (string, required): ID kelompok dampingan.
  * `tahun_ajaran` (string, required): Format `"2025/2026"`.

#### Sample JSON Response Payload:
```json
{
  "status": "success",
  "data": {
    "guru_wali": {
      "nama": "Dra. Siti Aminah, M.Pd",
      "nip": "19780512 200501 2 003",
      "sekolah": "SMA Negeri 1 Pembelajaran"
    },
    "kelompok": {
      "id": "kel-dandelion-101",
      "nama": "Kelompok Dandelion (Kelas X-1)",
      "tahun_ajaran": "2025/2026",
      "total_murid": 5
    },
    "ringkasan": {
      "total_sesi_terlaksana": 24,
      "murid_tuntas_target": 5,
      "persentase_ketercapaian_kelompok": 100.0
    },
    "matriks_murid": [
      {
        "siswa_id": "sis-001",
        "nama_siswa": "Rizky Pratama",
        "nisn": "0081234567",
        "bulanan": {
          "juli": 2,
          "agustus": 1,
          "september": 2,
          "oktober": 1,
          "november": 1,
          "desember": 2,
          "januari": 1,
          "februari": 2,
          "maret": 1,
          "april": 1,
          "mei": 2,
          "juni": 1
        },
        "total_sesi": 17,
        "bulan_terpenuhi": 12,
        "status": "100% Memenuhi (12/12 Bulan)"
      }
    ]
  }
}
```

---

## 6. Checklist Pengujian AI Agent & Developer

Saat membangun dan memverifikasi fitur rekapitulasi bulanan ini, pastikan memenuhi kriteria berikut:

1. [ ] **Akurasi Agregasi**: Jumlah sesi per bulan pada matriks sesuai dengan total transaksi di tabel `jurnal_guru_wali`.
2. [ ] **Perhitungan Kalender Pendidikan**: Bulan Juli hingga Desember terhitung sebagai Semester Ganjil, dan Januari hingga Juni sebagai Semester Genap tahun ajaran berjalan.
3. [ ] **Kesesuaian Target Minimum**: Jika ada bulan dengan 0 sesi, status ketercapaian secara otomatis menandai "Belum Memenuhi".
4. [ ] **Format Layout Docx**: Generasi berkas `.docx` menggunakan orientasi *Landscape* sehingga tabel 17 kolom tidak terpotong saat dicetak/dikonversi ke PDF.
5. [ ] **Keamanan & Privasi Access**: API hanya mengembalikan data kelompok dampingan milik Guru Wali yang sedang login.
