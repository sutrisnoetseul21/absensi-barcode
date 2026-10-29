# 📖 Plan Matang Tahap 3 (Menu 3): Jurnal Mengajar (`/portal-guru/jurnal-mengajar`)

Dokumen ini merupakan **Blueprint Teknis & Panduan Implementasi Komprehensif** untuk **Menu 3: Jurnal Mengajar** pada aplikasi mobile Flutter (`apk-android-presensi`), yang diselaraskan 100% dengan aturan bisnis web Livewire:
- **Daftar & Riwayat Jurnal**: [`JurnalMengajarList.php`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Livewire/PortalGuru/JurnalMengajar/JurnalMengajarList.php) & [`jurnal-mengajar-list.blade.php`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/resources/views/livewire/portal-guru/jurnal-mengajar/jurnal-mengajar-list.blade.php) (`/portal-guru/jurnal-mengajar`).
- **Form Tambah & Edit Jurnal**: [`JurnalMengajarForm.php`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Livewire/PortalGuru/JurnalMengajar/JurnalMengajarForm.php) & [`jurnal-mengajar-form.blade.php`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/resources/views/livewire/portal-guru/jurnal-mengajar/jurnal-mengajar-form.blade.php) (`/portal-guru/jurnal-mengajar/tambah` & `/portal-guru/jurnal-mengajar/{id}/edit`).
- **Model Database**: [`JurnalPembelajaran.php`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Models/JurnalPembelajaran.php) & [`JurnalPembelajaranPresensi.php`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Models/JurnalPembelajaranPresensi.php).

---

## 1. Bedah Logika Bisnis & Fitur Inti (Core Business Logic)

### A. Hak Akses & Penugasan Mengajar
1. **Guru Pengampu vs Mode Supervisi**:
   - Secara default, guru hanya dapat melihat dan mengelola jurnal mengajarnya sendiri (`teacher_id === auth()->user()->teacher?->id`).
   - Akun dengan hak akses *Supervisi / SuperAdmin* (`isSupervisor`) dapat memantau jurnal guru lain di sekolah.
2. **Keterkaitan Penugasan (`Pengajaran`)**:
   - Guru memilih **Mata Pelajaran** terlebih dahulu:
     - Jika guru hanya mengajar 1 mapel pada tahun ajaran aktif, sistem **otomatis memilih dan menguncinya (*auto-select & lock*)**.
     - Jika mengajar lebih dari 1 mapel, disediakan seleksi mapel yang diampu guru.
   - **Kelas Rombel** bersifat *dependent* (bergantung pada mapel terpilih):
     - Kelas yang muncul hanya kelas rombel yang ditugaskan kepada guru tersebut untuk mata pelajaran terkait pada tahun ajaran aktif.
     - Jika untuk mapel tersebut guru hanya mengajar 1 kelas, sistem otomatis memilih kelas tersebut.

### B. Kalkulasi Waktu & Jam Pelajaran KBM (*Smart JP Calculator*)
1. **Aturan Jam Pelajaran (JP)**:
   - **Jam Mulai (Ke-)**: Rentang nilai `1` sampai `9` (Maksimal Jam ke-9).
   - **Durasi JP**: Rentang nilai `1` sampai `5` JP (Standar durasi: `1 JP = 40 Menit`).
   - **Kalkulasi Jam Selesai**: `jam_selesai = jam_mulai + durasi_jp - 1`.
   - **Format Label Resmi**:
     - Jika `durasi_jp == 1`: `"Jam ke {jam_mulai} (1 JP)"`
     - Jika `durasi_jp > 1`: `"Jam ke {jam_mulai} - {jam_selesai} ({durasi_jp} JP)"`
   - **Total Menit KBM**: `durasi_jp * 40 Menit`.

### C. Dokumentasi Pembelajaran & Refleksi
1. **Materi Pokok / Bab / Topik KBM**: Wajib diisi (`required|string|max:255`), contoh: *"Bab 3 - SPLDV"*.
2. **Uraian Aktivitas KBM & Penugasan**: Wajib diisi (`required|string|min:5`), mencakup apersepsi, penyampaian materi, diskusi/praktikum, atau tugas latihan.
3. **Refleksi / Catatan Tindak Lanjut Guru**: Opsional (`nullable|string`), berisi catatan kedisiplinan, remedial siswa, atau rencana KBM berikutnya.

### D. Sinkronisasi Presensi Siswa Sesi KBM (*Smart Gate Sync*)
Ketika guru memilih kelas dan tanggal KBM, sistem mengambil daftar siswa aktif di rombel tersebut beserta status kehadiran mereka di gerbang pagi (`attendances`):
1. **Siswa Sakit / Izin / Alpa di Gerbang (`is_locked = true`)**:
   - Status KBM otomatis ditetapkan sebagai **`tidak_mengikuti`** dan **TERKUNCI (Disabled)**.
   - Dilarang diubah menjadi hadir oleh guru mapel karena sudah terkonfirmasi resmi berhalangan di tingkat sekolah.
2. **Siswa Hadir / Telat di Gerbang**:
   - Default status KBM adalah **`hadir`**.
   - Guru mapel dapat mengubahnya menjadi **`tidak_mengikuti`** jika siswa membolos dari jam pelajaran atau izin ke ruang UKS.
3. **Siswa Belum Absen di Gerbang (`status_gerbang === 'belum_absen'`)**:
   - Disediakan tombol cerdas: **`+ Hadirkan (Wajib Telat)`**.
   - Jika guru menandai siswa ini hadir di kelas:
     - Sistem backend otomatis membuat/memperbarui data di tabel `attendances` pada tanggal tersebut dengan status **`telat`** (*late_minutes = 1, is_manual_input = true, source = 'jurnal_guru', note = 'Diabsenkan oleh guru mata pelajaran di Jurnal Pembelajaran'*).
     - Menjamin keadilan data presensi sekolah sehingga siswa yang masuk tanpa scan gerbang tetap tercatat masuk namun berstatus telat.
4. **Tombol Tindakan Massal**:
   - Tombol **"Tandai Semua Hadir KBM"** untuk mempercepat input guru di awal jam KBM.

---

## 2. Rencana Desain UI/UX Mobile Flutter (Bagus & Kekinian)

### 🎨 A. Prinsip Desain Mobile Modern:
1. **Ergonomis Satu Tangan (One-Hand Friendly)**:
   - Tombol-tombol utama (seleksi jam, durasi, simpan) diletakkan di area jangkauan jempol (bagian tengah ke bawah).
   - Menghindari tabel lebar mendatar (`<table>`), menggantikannya dengan **Kartu Siswa Vertikal Ramping (Student Card)**.
2. **Visual Hierarchy & Deep Indigo / Purple Theme**:
   - Mengikuti estetika desain sistem: Gradient `AppColors.primary` & `AppColors.secondary`, kartu putih berbayang halus (*soft elevation*), dan aksen warna status yang tegas:
     - 🟢 *Hadir Tepat Waktu* (Emerald)
     - 🟠 *Telat Gerbang / Diabsenkan Telat* (Amber)
     - 🔴 *Sakit / Izin / Alpa Terkunci* (Rose)
     - ⚪ *Belum Scan Gerbang* (Slate)
3. **Micro-Interactions & Haptic Feedback**:
   - Getaran taktil halus saat memilih tombol angka Jam Mulai atau Durasi JP.
   - Indikator jam KBM dinamis yang berubah seketika (*real-time reactive UI*).

---

### 📱 B. Struktur Layar 1: Riwayat & Daftar Jurnal (`JurnalMengajarScreen`)
```
┌────────────────────────────────────────────────────────┐
│ ‹ Jurnal Mengajar                     [🔍] [Filter]   │
├────────────────────────────────────────────────────────┤
│ 🔮 HERO BANNER KEKINIAN                                │
│    Agenda Pembelajaran & Dokumentasi KBM Harian        │
│    Tahun Ajaran: 2025/2026 Ganjil • Aktif             │
│    [ + Tulis Jurnal Baru ]                            │
├────────────────────────────────────────────────────────┤
│ 📊 3 KARTU STATISTIK RINGKASAN (Horizontal Scroll / 3 Col)│
│  ┌──────────────┐ ┌──────────────┐ ┌────────────────┐  │
│  │ 📚 24 Sesi   │ │ ⏱️ 48 JP     │ │ 📈 96.5%       │  │
│  │ Total KBM    │ │ Terlaksana   │ │ Rata Kehadiran │  │
│  └──────────────┘ └──────────────┘ └────────────────┘  │
├────────────────────────────────────────────────────────┤
│ 🏷️ CHIP FILTER CEPAT (Bulan KBM • Kelas • Mapel)       │
│  [Semua Bulan ▾]  [Semua Kelas ▾]  [Semua Mapel ▾]     │
├────────────────────────────────────────────────────────┤
│ 📋 DAFTAR KARTU JURNAL MENGAJAR (ListView)             │
│  ┌──────────────────────────────────────────────────┐  │
│  │ 📅 Kamis, 29 Okt 2026   ⏰ Jam ke 1 - 2 (2 JP)   │  │
│  │ 🎓 Kelas 7A            📖 Matematika             │  │
│  │                                                  │  │
│  │ 📌 Bab 3: SPLDV & Metode Eliminasi               │  │
│  │    Siswa mendiskusikan studi kasus belanjaan... │  │
│  │                                                  │  │
│  │ 💡 Refleksi: 2 siswa perlu bimbingan mandiri     │  │
│  │ ──────────────────────────────────────────────── │  │
│  │ 👥 Kehadiran: 31/32 Siswa (96.9%)  [Semua Hadir] │  │
│  │                              [✏️ Edit]  [🗑️ Hapus] │  │
│  └──────────────────────────────────────────────────┘  │
└────────────────────────────────────────────────────────┘
```

---

### 📱 C. Struktur Layar 2: Form Tambah/Edit Jurnal (`FormJurnalMengajarScreen`)
```
┌────────────────────────────────────────────────────────┐
│ ‹ Tulis Jurnal Mengajar                                │
├────────────────────────────────────────────────────────┤
│ 📌 LANGKAH 01: Parameter KBM & Jadwal Waktu            │
│  • Tanggal KBM: [ Kamis, 29 Oktober 2026 📅 ]         │
│  • Mata Pelajaran: [ Matematika (Otomatis) 🔒 ]        │
│  • Kelas Rombel: [ Pilih Kelas 7A ▾ ]                  │
│                                                        │
│  ┌─ SMART JP CALCULATOR ────────────────────────────┐  │
│  │ 1. Jam Mulai (Ke-):                              │  │
│  │    [ 1 ] [ 2 ] [ 3 ] [ 4 ] [ 5 ] [ 6 ] [ 7 ] ... │  │
│  │ 2. Durasi Pembelajaran:                          │  │
│  │    [ 1 JP ] [ 2 JP ] [ 3 JP ] [ 4 JP ] [ 5 JP ]  │  │
│  │ 3. Hasil Kalkulasi:                              │  │
│  │    🟣 Jam ke 1 - 2 (2 JP) • 80 Menit KBM         │  │
│  └──────────────────────────────────────────────────┘  │
├────────────────────────────────────────────────────────┤
│ 📌 LANGKAH 02: Materi Pokok & Refleksi Pembelajaran    │
│  • Materi Pokok / Bab / Topik *                        │
│    [ Contoh: Bab 3 - Persamaan Linier Dua Variabel... ]│
│  • Uraian Aktivitas KBM & Penugasan *                  │
│    [ Deskripsi apersepsi, materi, diskusi, tugas...  ] │
│  • Refleksi Guru / Tindak Lanjut (Opsional)            │
│    [ Catatan remedial, kedisiplinan, target sesi...  ] │
├────────────────────────────────────────────────────────┤
│ 📌 LANGKAH 03: Presensi Siswa di Jam KBM (32 Siswa)    │
│  [ Petunjuk Status: 🟢Tepat  🟠Telat  🔴Terkunci ]    │
│  [ ✓ Tandai Semua Hadir KBM ]                         │
│                                                        │
│  ┌─ KARTU SISWA #1 ─────────────────────────────────┐  │
│  │ (AA) Ahmad Albar • NIS: 2024001                  │  │
│  │ Status Gerbang: 🟢 Hadir Tepat Waktu             │  │
│  │ Presensi KBM: [● Hadir] [○ Tidak Hadir]          │  │
│  └──────────────────────────────────────────────────┘  │
│  ┌─ KARTU SISWA #2 ─────────────────────────────────┐  │
│  │ (BS) Budi Santoso • NIS: 2024002                 │  │
│  │ Status Gerbang: ⚪ Belum Scan Gerbang            │  │
│  │ Presensi KBM: [ + Hadirkan (Wajib Telat) ⏰ ]     │  │
│  └──────────────────────────────────────────────────┘  │
│  ┌─ KARTU SISWA #3 ─────────────────────────────────┐  │
│  │ (CD) Citra Dewi • NIS: 2024003                   │  │
│  │ Status Gerbang: 🔴 Sakit (Surat Dokter)          │  │
│  │ Presensi KBM: 🔒 Tidak Mengikuti (Terkunci)      │  │
│  └──────────────────────────────────────────────────┘  │
├────────────────────────────────────────────────────────┤
│ 💾 FLOATING BOTTOM BAR                                 │
│  [ Batal ]      [ 💾 Simpan Jurnal Mengajar ]          │
└────────────────────────────────────────────────────────┘
```

---

## 3. Spesifikasi Teknis Backend API Laravel

Dibuat controller baru: `app/Http/Controllers/Api/Mobile/GuruJurnalMengajarController.php` dengan rute di `routes/api.php` di dalam grup `auth:sanctum`:

### A. Endpoint List & Filter Jurnal
* **Method & URL**: `GET /api/v1/guru/jurnal-mengajar`
* **Query Params**:
  - `academic_year_id` (optional, default: tahun aktif)
  - `month_year` (optional, format: `MM-YYYY`, misal: `10-2026`)
  - `class_id` (optional, filter per rombel)
  - `mata_pelajaran_id` (optional, filter per mapel)
  - `search` (optional, pencarian kata kunci materi/refleksi)
  - `page` & `per_page` (default: 15 item)
* **Response JSON**:
  ```json
  {
    "status": true,
    "message": "Daftar jurnal berhasil dimuat.",
    "data": {
      "summary": {
        "total_sesi": 24,
        "total_jp": 48,
        "rata_kehadiran": 96.5,
        "jurnal_hari_ini_count": 2
      },
      "jurnals": [
        {
          "id": "uuid-jurnal",
          "tanggal": "2026-10-29",
          "tanggal_formatted": "Kamis, 29 Oktober 2026",
          "jam_mulai": 1,
          "durasi_jp": 2,
          "jam_selesai": 2,
          "jam_ke_label": "Jam ke 1 - 2 (2 JP)",
          "durasi_menit": 80,
          "class_id": "uuid-class",
          "class_name": "Kelas 7A",
          "mata_pelajaran_id": 1,
          "mata_pelajaran_name": "Matematika",
          "materi": "Bab 3 - SPLDV",
          "uraian_materi": "Pembahasan metode eliminasi dan substitusi",
          "refleksi": "Dua siswa perlu bimbingan mandiri",
          "total_siswa": 32,
          "total_hadir": 31,
          "total_tidak_mengikuti": 1,
          "persentase_kehadiran": 96.9,
          "can_edit": true
        }
      ],
      "meta": {
        "current_page": 1,
        "last_page": 2,
        "total": 24
      }
    }
  }
  ```

### B. Endpoint Form Options (Mapel, Kelas, Jam)
* **Method & URL**: `GET /api/v1/guru/jurnal-mengajar/form-options`
* **Response JSON**:
  ```json
  {
    "status": true,
    "data": {
      "academic_year": { "id": "uuid", "name": "2025/2026 Ganjil" },
      "teacher": { "id": "uuid", "name": "Drs. Subagyo, M.Pd" },
      "is_single_mapel": true,
      "available_mapels": [
        { "id": 1, "nama_mapel": "Matematika" }
      ],
      "available_classes": [
        { "id": "uuid-7a", "name": "Kelas 7A", "mata_pelajaran_id": 1 },
        { "id": "uuid-7b", "name": "Kelas 7B", "mata_pelajaran_id": 1 }
      ],
      "available_months": {
        "07-2026": "Juli 2026",
        "08-2026": "Agustus 2026",
        "10-2026": "Oktober 2026"
      },
      "default_jam_mulai": 1,
      "default_durasi_jp": 2
    }
  }
  ```

### C. Endpoint Siswa & Presensi Gerbang
* **Method & URL**: `GET /api/v1/guru/jurnal-mengajar/students?class_id={id}&tanggal={YYYY-MM-DD}`
* **Response JSON**:
  ```json
  {
    "status": true,
    "data": {
      "class_id": "uuid-7a",
      "tanggal": "2026-10-29",
      "students": [
        {
          "student_id": "uuid-s1",
          "nis": "2024001",
          "name": "Ahmad Albar",
          "status_gerbang": "hadir",
          "status_jurnal": "hadir",
          "is_locked": false,
          "diabsenkan_telat": false,
          "keterangan": ""
        },
        {
          "student_id": "uuid-s2",
          "nis": "2024002",
          "name": "Budi Santoso",
          "status_gerbang": "belum_absen",
          "status_jurnal": "tidak_mengikuti",
          "is_locked": false,
          "diabsenkan_telat": false,
          "keterangan": ""
        },
        {
          "student_id": "uuid-s3",
          "nis": "2024003",
          "name": "Citra Dewi",
          "status_gerbang": "sakit",
          "status_jurnal": "tidak_mengikuti",
          "is_locked": true,
          "diabsenkan_telat": false,
          "keterangan": "Sakit di gerbang pagi"
        }
      ]
    }
  }
  ```

### D. Endpoint Simpan & Update Jurnal
* **Method & URL**: `POST /api/v1/guru/jurnal-mengajar` (atau `PUT /api/v1/guru/jurnal-mengajar/{id}`)
* **Payload Request**:
  ```json
  {
    "tanggal": "2026-10-29",
    "class_id": "uuid-7a",
    "mata_pelajaran_id": 1,
    "jam_mulai": 1,
    "durasi_jp": 2,
    "materi": "Bab 3 - SPLDV",
    "uraian_materi": "Apersepsi, penjelasan metode eliminasi, dan latihan soal berkelompok.",
    "refleksi": "Mayoritas siswa memahami eliminasi dengan baik.",
    "students": [
      {
        "student_id": "uuid-s1",
        "status_jurnal": "hadir",
        "diabsenkan_telat": false,
        "keterangan": ""
      },
      {
        "student_id": "uuid-s2",
        "status_jurnal": "hadir",
        "diabsenkan_telat": true,
        "keterangan": "Hadir di kelas jam 07.25"
      }
    ]
  }
  ```
* **Proses di Backend**:
  1. Validasi input request.
  2. Transaksi database (`DB::beginTransaction()`).
  3. Simpan/update record `JurnalPembelajaran`.
  4. Loop siswa:
     - Jika `diabsenkan_telat == true` atau (`status_gerbang === 'belum_absen'` & `status_jurnal === 'hadir'`), buat data di tabel `attendances` dengan status **`telat`** (source: `jurnal_guru`).
     - Simpan record detail `JurnalPembelajaranPresensi`.
  5. Commit transaksi database dan kirim respon sukses.

### E. Endpoint Hapus Jurnal
* **Method & URL**: `DELETE /api/v1/guru/jurnal-mengajar/{id}`
* **Ketentuan**: Hanya guru pemilik atau admin yang berhak menghapus. Otomatis menghapus relasi di `jurnal_pembelajaran_presensis`.

### F. Endpoint Detail Jurnal (Mode Edit & Lihat Kehadiran Sesi)
* **Method & URL**: `GET /api/v1/guru/jurnal-mengajar/{id}`
* **Ketentuan**: Mengambil data lengkap jurnal pembelajaran beserta daftar kehadiran siswa (`presensis.siswa`) dan daftar mapel/kelas yang diampu guru untuk mode Edit maupun modal rincian kehadiran sesi KBM.

### G. Endpoint Cetak Laporan KBM (PDF A4 Landscape & Excel `.xlsx`)
* **Method & URL**: `GET /api/v1/guru/jurnal-mengajar/export`
* **Query Params**:
  - `format`: `pdf` | `excel`
  - `jenis`: `bulanan` | `semester` | `tahunan` | `kustom`
  - `month_year`: `MM-YYYY` (untuk periode bulanan)
  - `semester`: `ganjil` | `genap` (untuk periode semester)
  - `mata_pelajaran_id` & `class_id` (opsional)
* **Ketentuan**: Menggunakan template `pdf.jurnal-mengajar-rekap` (A4 Landscape) dan `JurnalMengajarExport` yang sama persis dengan versi web Livewire.

---

## 4. Urutan Langkah Kerja Eksekusi (Implementation Steps)

1. **Tahap 1: Backend Laravel API**
   - Buat [`app/Http/Controllers/Api/Mobile/GuruJurnalMengajarController.php`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Http/Controllers/Api/Mobile/GuruJurnalMengajarController.php).
   - Daftarkan rute di [`routes/api.php`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/routes/api.php).
   - Uji verifikasi endpoint menggunakan unit test / curl sederhana.
2. **Tahap 2: Frontend Flutter Services & Endpoints**
   - Tambahkan endpoint URL di [`lib/core/constants/api_endpoints.dart`](file:///home/sutris-remote/Documents/projek-laravel/apk-android-presensi/lib/core/constants/api_endpoints.dart).
3. **Tahap 3: Frontend Flutter UI (Kekinian & Modern)**
   - Buat [`lib/features/guru/screens/jurnal_mengajar_screen.dart`](file:///home/sutris-remote/Documents/projek-laravel/apk-android-presensi/lib/features/guru/screens/jurnal_mengajar_screen.dart) (Daftar & Riwayat Jurnal + Filter + Modal Cetak Laporan KBM PDF/Excel + Detail Kehadiran Sesi).
   - Buat [`lib/features/guru/screens/form_jurnal_mengajar_screen.dart`](file:///home/sutris-remote/Documents/projek-laravel/apk-android-presensi/lib/features/guru/screens/form_jurnal_mengajar_screen.dart) (Form Tambah & Edit Jurnal).
   - Terapkan widget *Interactive JP Calculator Chip*, *Modern Student Card*, dan *Sticky Bottom Action Bar*.
4. **Tahap 4: Integrasi Dashboard & Pengujian**
   - Buka [`lib/features/guru/screens/dashboard_guru_screen.dart`](file:///home/sutris-remote/Documents/projek-laravel/apk-android-presensi/lib/features/guru/screens/dashboard_guru_screen.dart), ubah aksi `_showStepNotice('Jurnal Mengajar', 3)` menjadi navigasi langsung ke `JurnalMengajarScreen()`.
   - Hubungkan *badge counter* jurnal hari ini secara live dari API.
   - Uji coba di browser Chrome & Android emulator/perangkat fisik.

