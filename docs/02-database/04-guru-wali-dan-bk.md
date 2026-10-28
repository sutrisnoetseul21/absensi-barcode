# 🗄️ Klaster 4: Pembinaan Karakter Guru Wali & Bimbingan Konseling (SPIKAP)

Dokumen ini memuat skema tabel pembimbingan kelompok guru wali, buku jurnal harian, badge karakter siswa, serta modul pengaduan kasus dan konseling BK.

---

## 1. Daftar Tabel dalam Klaster

| Nama Tabel | Peran Utama | Model Eloquent |
| :--- | :--- | :--- |
| `kelompok_guru_wali` | Master kelompok asuh bimbingan guru | `App\Models\KelompokGuruWali` |
| `kelompok_guru_wali_siswa` | Anggota siswa per kelompok asuh | Pivot Table |
| `jurnal_guru_wali` | Catatan harian bimbingan & tindak lanjut guru | `App\Models\JurnalGuruWali` |
| `badge_karakter_siswa` | Penghargaan karakter digital siswa | `App\Models\BadgeKarakterSiswa` |
| `konsultasi_guru_wali` | Sesi konsultasi tatap muka siswa ke guru wali | `App\Models\KonsultasiGuruWali` |
| `pesan_konsultasi_guru_wali` | Pesan / catatan interaksi konsultasi | `App\Models\PesanKonsultasiGuruWali` |
| `konseling_bk` | Sesi penanganan kasus formal oleh guru BK | `App\Models\KonselingBk` |
| `spikap_laporan` | Tiket laporan aduan / perundungan / fasilitas | `App\Models\SpikapLaporan` |
| `spikap_lampiran` | Foto bukti / dokumen pendukung aduan | `App\Models\SpikapLampiran` |
| `spikap_notif_settings` | Kontak darurat dan notifikasi alert tim BK | `App\Models\SpikapNotifSetting` |

---

## 2. Definisi Skema Kolom Utama

### A. Tabel `kelompok_guru_wali`
- `id` (char(36), PK): UUID kelompok.
- `nama_kelompok` (varchar(100)): Nama kelompok bimbingan.
- `teacher_id` (char(36), FK, Unique): Guru pembina kelompok (1 guru memiliki 1 kelompok aktif).
- `status_aktif` (boolean): Status keaktifan kelompok.

### B. Tabel `kelompok_guru_wali_siswa` (Constraint Unik Level DB)
- `id` (char(36), PK): UUID keanggotaan.
- `kelompok_id` (char(36), FK): Relasi ke `kelompok_guru_wali.id`.
- `student_id` (char(36), FK): Relasi ke `students.id`.
- `tahun_masuk` (year): Tahun penetapan kelompok.
- `status_aktif` (boolean): Flag keaktifan status siswa di kelompok.
- `active_student_id` (varchar(36), Virtual Generated Column, Unique):
  - *Expression*: `CASE WHEN status_aktif = 1 THEN student_id ELSE NULL END`
  - **Efek Constraint**: Menjamin siswa yang statusnya aktif **tidak dapat diduplikasi** ke lebih dari 1 kelompok di level MySQL engine.

### C. Tabel `jurnal_guru_wali`
- `id` (char(36), PK).
- `kelompok_id` (char(36), FK): Kelompok guru wali terkait.
- `tanggal` (date): Tanggal pelaksanaan bimbingan.
- `kegiatan` (text): Uraian materi pembinaan karakter.
- `kendala` & `rencana_tindak_lanjut` (text, nullable).

### D. Tabel `konseling_bk`
- `id` (char(36), PK).
- `student_id` (char(36), FK): Siswa yang menjalani konseling.
- `teacher_id` (char(36), FK, nullable): Guru BK konselor penanggung jawab.
- `tanggal_konseling` (date): Tanggal sesi pertemuan.
- `kasus` / `masalah` (text): Uraian permasalahan.
- `hasil_konseling` (text): Kesepakatan / penyelesaian kasus.
- `status` (enum('proses','selesai','rujukan_eksternal')).
- `rujukan_dari_jurnal_id` (char(36), FK, nullable): Relasi jika kasus berasal dari eskalasi guru wali.

---

## 3. Diagram Relasi

```mermaid
erDiagram
    TEACHERS ||--o| KELOMPOK_GURU_WALI : "mentors"
    KELOMPOK_GURU_WALI ||--o{ KELOMPOK_GURU_WALI_SISWA : "has_members"
    STUDENTS ||--o| KELOMPOK_GURU_WALI_SISWA : "member_of"
    
    KELOMPOK_GURU_WALI ||--o{ JURNAL_GURU_WALI : "activity_logs"
    STUDENTS ||--o{ BADGE_KARAKTER_SISWA : "earns"
    
    STUDENTS ||--o{ KONSELING_BK : "counseled"
    JURNAL_GURU_WALI -. eskalasi_kasus .-> KONSELING_BK : "refers"
    SPIKAP_LAPORAN -. aduan_masuk .-> KONSELING_BK : "triggers"
```
