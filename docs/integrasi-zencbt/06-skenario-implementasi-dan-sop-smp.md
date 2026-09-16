# 06. Skenario Operasional & SOP Integrasi SMP

Dokumen ini merangkum **Standar Operasional Prosedur (SOP)** alur kerja guru, kurikulum, dan proktor/operator dalam menggunakan sistem integrasi **Laravel Absensi ↔ ZenCBT** sepanjang satu tahun ajaran di jenjang SMP.

---

## 🔄 Siklus Alur Kerja 4 Tahap

```mermaid
graph TD
    subgraph Tahap 1: Awal Tahun Ajaran
        A1[Update Tahun Ajaran Aktif & Semester di Absensi] --> A2[Enrollment Siswa ke Kelas 7, 8, 9]
        A2 --> A3[Klik 'Sinkronkan Master ke ZenCBT']
    end

    subgraph Tahap 2: Menjelang Ujian / Asesmen
        B1[Tentukan Pembagian Sesi Ujian Sesi 1 / Sesi 2] --> B2[Cetak Kartu Peserta Ujian dari Filament]
        B2 --> B3[Klik 'Push Siswa ke ZenCBT']
    end

    subgraph Tahap 3: Hari Pelaksanaan Ujian
        C1[Guru/Proktor Buka ZenCBT & Buat Jadwal Ujian]
        C1 --> C2[Isi event_nama: misal 'ASTS Ganjil 2025/2026']
        C2 --> C3[Siswa Login CBT Menggunakan NISN & Password Kartu]
        C3 --> C4[Proktor Pantau Siswa / Gunakan Fitur Reset jika Terkunci]
    end

    subgraph Tahap 4: Pasca Ujian & Rapor
        D1[Di Filament: Buka Menu Master Ujian] --> D2[Klik 'Tarik Nilai dari CBT']
        D2 --> D3[Nilai Masuk Otomatis ke nilai_ujians]
        D3 --> D4[Cetak Rekap Nilai & Hitung Rapor Per Semester]
    end

    Tahap 1 --> Tahap 2 --> Tahap 3 --> Tahap 4
```

---

## 1. Tahap 1: Awal Tahun Ajaran Baru

* **Pelaksana**: Admin Kurikulum / Operator Sekolah.
* **Langkah Kerja**:
  1. Di aplikasi Laravel Absensi, buka menu **Tahun Ajaran**. Aktifkan tahun ajaran baru (contoh: `2025/2026`).
  2. Buka menu **Pengaturan Sekolah**, pastikan `active_semester` diset ke `'ganjil'`.
  3. Lakukan kenaikan kelas dan pendaftaran siswa baru (tabel `student_enrollments`).
  4. Buka menu **Integrasi ZenCBT** ➡️ Klik tombol **"Sinkronkan Master Data ke CBT"**:
     - Sistem otomatis mendaftarkan Tahun Ajaran ke tabel `programs` ZenCBT (`code: "2025-2026"`).
     - Sistem memperbarui tabel `levels` (Kelas 7, 8, 9) dan `groups` (7A, 7B, dll).
     - Mata pelajaran disinkronkan ke tabel `mapel` ZenCBT.
  5. **Mekanisme Kenaikan Kelas Siswa (Anti-Duplikasi)**:
     - Karena `siswa_id` (NISN) di ZenCBT bersifat `UNIQUE`, siswa **tidak akan diduplikasi/berganda**.
     - Siswa yang naik kelas (misal dari 7A ke 8A) otomatis **di-update** kolom `level_id`, `group_id`, dan `program_id`-nya.
     - Riwayat nilai ujian yang pernah ditempuh siswa saat di kelas 7 **tetap tersimpan aman** di ZenCBT dan di tabel `nilai_ujians` Laravel.

---

## 2. Tahap 2: Menjelang Ujian (UH / ASTS / ASAS)

* **Pelaksana**: Panitia Ujian / Proktor.
* **Langkah Kerja**:
  1. **Tentukan Sesi Ujian**:
     Jika komputer di laboratorium terbatas, atur pembagian siswa:
     - Rombel 7A & 7B ➡️ `Sesi 1` (Pukul 07.30 - 09.30)
     - Rombel 7C & 7D ➡️ `Sesi 2` (Pukul 10.00 - 12.00)
  2. **Push Data Siswa**:
     Di panel Filament, klik tombol **"Kirim Data Siswa ke ZenCBT"**:
     - Seluruh siswa aktif akan terdaftar di tabel `siswa` ZenCBT lengkap dengan `siswa_id` (NISN), `password_plain`, dan `sesi`.
  3. **Cetak Kartu Peserta Ujian**:
     Cetak kartu peserta secara massal per kelas langsung dari Laravel Absensi. Kartu memuat:
     - Foto Siswa & Logo Sekolah
     - Nama & Kelas
     - Nomor Peserta (NISN)
     - Password Login CBT
     - Ruang & Sesi Ujian
     - QR Code untuk presensi ruangan

---

## 3. Tahap 3: Hari Pelaksanaan Ujian (CBT Running)

* **Pelaksana**: Guru Pengampu & Pengawas Ruang.
* **Langkah Kerja**:
  1. Guru membuka antarmuka ZenCBT:
     - Membuat Bank Soal dan butir soal.
     - Membuat Ujian baru dengan format baku:
       * **Judul**: `"Matematika Kelas 7"`
       * **Event**: **`"ASTS Ganjil 2025/2026"`**
       * **Mapel**: `"Matematika"`
       * **Durasi**: `90 Menit` | **KKM**: `75.00`
  2. Siswa login di browser masing-masing dengan NISN dan password dari kartu ujian.
  3. **Penanganan Kendala Lapangan**:
     - Jika ada siswa yang mengalami listrik padam, browser terhenti, atau **terblokir (*blocked*)** karena keluar layar penuh (*anti-cheat violation*):
     - Guru/Pengawas tidak perlu repot membuka database PostgreSQL. Cukup buka dashboard pengawas di web sekolah, klik **"Buka Kunci Siswa"** (memanggil API `reset-session`).

---

## 4. Tahap 4: Pasca Ujian (Penarikan Nilai & Rekapitulasi Rapor)

* **Pelaksana**: Guru Mata Pelajaran / Wali Kelas.
* **Langkah Kerja**:
  1. Setelah ujian selesai, guru membuka menu **Akademik ➡️ Ujian & Asesmen** di Laravel Absensi.
  2. Tambahkan agenda ujian baru atau klik tombol **"Tarik Nilai dari ZenCBT"**.
  3. Sistem akan:
     - Mengambil seluruh daftar skor dari tabel `peserta_ujian` ZenCBT.
     - Menghitung jumlah soal benar, salah, dan kosong dari `jawaban_ujian`.
     - Mengikat nilai ke `academic_year_id` aktif dan `semester` saat ini.
     - Menghitung otomatis status ketuntasan siswa (`is_tuntas` = `nilai >= KKM`).
  4. Guru dapat langsung mengunduh format cetak Excel / PDF berisi:
     - Daftar Nilai Kelas
     - Nilai Tertinggi, Terendah, dan Rata-rata Kelas
     - Daftar Siswa yang Butuh Remedial (Nilai di bawah KKM 75)

---

## 5. Ringkasan Manfaat SOP Ini

1. **Bebas *Human Error***: Guru tidak perlu menginput nilai satu per satu dari CBT ke buku nilai atau Excel.
2. **Histori Terjamin**: Nilai tetap aman tersimpan di MySQL Absensi meskipun ZenCBT di-reset untuk tahun ajaran berikutnya.
3. **Fleksibel**: Tetap mendukung input nilai manual bagi siswa yang mengikuti ujian susulan via kertas/luring.
