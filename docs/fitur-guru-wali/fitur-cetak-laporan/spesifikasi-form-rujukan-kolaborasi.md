# SPESIFIKASI TEKNIS FITUR: MODUL FORM RUJUKAN & KOLABORASI GURU WALI

> **Dokumen Panduan Pengembang & AI Agent**  
> **Versi**: 1.0  
> **Tanggal**: September 2026  
> **Landasan Regulasi**: Permendikdasmen No. 11 Tahun 2025 & Kepmendikdasmen No. 221/P/2025 (Mekanisme Rujukan & Eskalasi Pendampingan Murid)

---

## 1. Ringkasan Eksekutif & Tujuan Fitur

Dokumen ini menjelaskan spesifikasi teknis penambahan **Modul Form Rujukan dan Kolaborasi** pada sistem portal sekolah. 

### Latar Belakang & Solusi Arsitektur
* **Bridging Kerahasiaan (Privacy Gateway)**: Sebagaimana ditetapkan pada modul jurnal, catatan harian Guru Wali bersifat *Strict Isolated* (rahasia). Namun, jika ditemukan kendala mendesak yang membutuhkan intervensi spesifik, Guru Wali dapat menerbitkan **Form Rujukan Resmi**.
* **Delegasi Akses Terbatas (Granted Access)**: Menerbitkan rujukan secara otomatis memberikan izin baca (*read access*) **hanya untuk berkas rujukan tersebut** kepada pihak penerima (Guru BK, Wali Kelas, atau TPPK), tanpa membocorkan seluruh riwayat jurnal privat siswa lainnya.
* **Siklus Kolaborasi Tertutup**: Menyediakan alur *feedback loop* di mana penerima rujukan dapat menginput catatan hasil penanganan yang langsung terhubung kembali ke portal Guru Wali.

---

## 2. Arsitektur Data & Skema Basis Data (Database Schema)

### A. Tabel `rujukan_guru_wali`
Menyimpan berkas rujukan dan status tindak lanjut antar peran.

```sql
CREATE TABLE rujukan_guru_wali (
    id VARCHAR(36) PRIMARY KEY,
    nomor_berkas VARCHAR(50) UNIQUE NOT NULL, -- Contoh: "RJK/GW/2026/009"
    jurnal_id VARCHAR(36) NULL,               -- Link opsional ke jurnal asal
    guru_wali_id VARCHAR(36) NOT NULL,        -- Pengaju (Guru Wali)
    siswa_id VARCHAR(36) NOT NULL,            -- Siswa yang dirujuk
    
    penerima_tipe ENUM('Guru BK', 'Wali Kelas', 'Orang Tua / Wali', 'TPPK / Kepala Sekolah') NOT NULL,
    penerima_id VARCHAR(36) NULL,             -- User ID penerima spesifik (jika ada)
    tingkat_prioritas ENUM('Biasa', 'Penting', 'Segera / Prioritas Tinggi') DEFAULT 'Biasa',
    
    kategori_pendampingan ENUM(
        'Akademik',
        'Karakter & Kedisiplinan',
        'Minat & Bakat / Ekskul',
        'Sosial & Psikologis'
    ) NOT NULL,
    
    ringkasan_peristiwa TEXT NOT NULL,         -- Latar belakang & temuan awal Guru Wali
    alasan_rujukan TEXT NOT NULL,              -- Mengapa perlu dirujuk / eskalasi
    rekomendasi_tindak_lanjut TEXT NOT NULL,   -- Harapan tindakan dari penerima
    
    -- Field Umpan Balik (Diisi oleh Penerima Rujukan)
    tanggal_penanganan DATETIME NULL,
    catatan_umpan_balik TEXT NULL,            -- Hasil konseling/penanganan penerima
    status_rujukan ENUM(
        'TERKIRIM',
        'DIPROSES',
        'SELESAI',
        'ALIH_TANGAN_KASUS'
    ) DEFAULT 'TERKIRIM',
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (guru_wali_id) REFERENCES guru(id),
    FOREIGN KEY (siswa_id) REFERENCES siswa(id),
    FOREIGN KEY (jurnal_id) REFERENCES jurnal_guru_wali(id) ON DELETE SET NULL
);
```

---

## 3. Matriks Hak Akses & Alur Keamanan (Access Control)

| Peran (Role) | Hak Akses Dokumen Rujukan | Keterangan |
| :--- | :--- | :--- |
| **Guru Wali (Pengaju)** | **Full Access (Create, Read, Cancel)** | Dapat membuat, memantau status, dan mencetak berkas rujukan miliknya. |
| **Penerima Rujukan (Guru BK / Wali Kelas)** | **Read & Update Status** | Hanya dapat membaca dokumen rujukan yang ditujukan kepadanya dan menginput umpan balik. |
| **Kepala Sekolah / TPPK** | **Read-Only / Escalation Target** | Memantau seluruh kasus rujukan aktif & menjadi target penanganan kasus berat. |
| **Siswa / Orang Tua** | **Restricted Read (Surat Resmi)** | Hanya membaca resume keputusan/surat pemanggilan jika sifat rujukan melibatkan Orang Tua. |

---

## 4. Integrasi dengan Modul Input Jurnal Guru Wali

### Trigger Automasi (One-Click Rujukan)
Pada form input **Jurnal Guru Wali**, ketika Guru Wali memilih opsi `rujukan_kolaborasi` selain `'Mandiri'` (misal: `'Guru BK'`):
1. Tombol pintar **`[+ Terbitkan Form Rujukan Resmi]`** akan muncul secara otomatis.
2. Ketika diklik, sistem membuka modal / halaman Form Rujukan dengan data yang sudah terisi otomatis (*pre-filled*):
   * `siswa_id` $ightarrow$ Terisi sesuai siswa di jurnal.
   * `kategori_pendampingan` $ightarrow$ Terisi sesuai kategori jurnal.
   * `ringkasan_peristiwa` $ightarrow$ Terisi dari uraian pembahasan jurnal.

---

## 5. Spesifikasi REST API Endpoints

### 1. POST `/api/guru-wali/rujukan`
Menerbitkan berkas rujukan baru.
* **Header**: `Authorization: Bearer <token_guru_wali>`
* **Payload Request**:
```json
{
  "jurnal_id": "jrn-99102",
  "siswa_id": "sis-005",
  "penerima_tipe": "Guru BK",
  "penerima_id": "guru-bk-01",
  "tingkat_prioritas": "Segera / Prioritas Tinggi",
  "kategori_pendampingan": "Sosial & Psikologis",
  "ringkasan_peristiwa": "Siswa mengalami penurunan motivasi dan tidak hadir 3 hari berturut-turut.",
  "alasan_rujukan": "Membutuhkan konseling psikologis intensif dan pemanggilan orang tua.",
  "rekomendasi_tindak_lanjut": "Konseling individu dan mediasi bersama orang tua."
}
```

### 2. GET `/api/rujukan/inbox`
Mendapatkan daftar rujukan masuk untuk role penerima (misal Guru BK atau Wali Kelas).
* **Header**: `Authorization: Bearer <token_penerima>`
* **Response Sample**:
```json
{
  "status": "success",
  "data": [
    {
      "rujukan_id": "rjk-2026-009",
      "nomor_berkas": "RJK/GW/2026/009",
      "tanggal_pengajuan": "2026-09-12 10:00:00",
      "pengaju_nama": "Dra. Siti Aminah, M.Pd",
      "siswa_nama": "Ahmad Fajar",
      "kelas": "X-1",
      "tingkat_prioritas": "Segera / Prioritas Tinggi",
      "status_rujukan": "TERKIRIM"
    }
  ]
}
```

### 3. PATCH `/api/rujukan/:id/tanggapi`
Penerima rujukan menginput hasil penanganan dan memperbarui status.
* **Header**: `Authorization: Bearer <token_penerima>`
* **Payload Request**:
```json
{
  "tanggal_penanganan": "2026-09-14 09:30:00",
  "catatan_umpan_balik": "Siswa telah mengikuti sesi konseling individu. Orang tua telah dihubungi dan menyepakati dampingan belajar di rumah.",
  "status_rujukan": "SELESAI"
}
```

### 4. GET `/api/rujukan/:id/cetak-docx`
Mengunduh dokumen template cetak resmi `.docx` yang terisi otomatis berdasarkan data rujukan ID tersebut.

---

## 6. Checklist Verifikasi & Pengujian (AI Agent Validation Checklist)

Saat AI Agent / Software Developer membangun modul ini, pastikan memenuhi kriteria sukses berikut:
1. [ ] **Pre-fill Data Jurnal**: Membuat rujukan dari form jurnal berhasil mengisi data siswa, kategori, dan deskripsi secara otomatis.
2. [ ] **Isolasi Kerahasiaan Penerima**: Guru BK yang menerima rujukan hanya bisa membaca data rujukan ID tersebut, dan tidak bisa membuka jurnal-jurnal privat Guru Wali lainnya.
3. [ ] **Update Status Realtime**: Ketika Guru BK menyelesaikan rujukan, status pada portal Guru Wali otomatis berubah dari `TERKIRIM` menjadi `SELESAI`.
4. [ ] **Generasi Dokumen Cetak**: API ekspor `.docx` menghasilkan dokumen resmi berformat Rujukan & Kolaborasi dengan tata letak tanda tangan 3 pihak (Guru Wali, Penerima, Kepala Sekolah).
