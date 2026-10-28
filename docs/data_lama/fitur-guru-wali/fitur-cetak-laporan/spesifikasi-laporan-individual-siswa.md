# SPESIFIKASI TEKNIS FITUR: LAPORAN PENDAMPINGAN INDIVIDUAL MURID (PER SEMESTER & TAHUNAN)

> **Dokumen Panduan Pengembang & AI Agent**  
> **Versi**: 1.0  
> **Tanggal**: September 2026  
> **Landasan Regulasi**: Permendikdasmen No. 11 Tahun 2025 & Kepmendikdasmen No. 221/P/2025 (Ekuivalensi Beban Kerja Guru Wali 2 JP/Minggu)

---

## 1. Ringkasan Eksekutif & Tujuan

Dokumen ini merupakan spesifikasi teknis untuk menghasilkan **Laporan Pendampingan Individual Murid** yang mengompilasi rekam jejak bimbingan seorang siswa selama **1 Tahun Ajaran (dibagi per Semester Ganjil & Genap)** atau kumulatif **multi-tahun** (sejak masuk hingga lulus).

### Fokus Utama Spesifikasi
* **Individual Student Centric**: Berfokus pada 1 murid spesifik untuk merekam perkembangan holistik di 4 pilar (Akademik, Karakter & Kedisiplinan, Minat & Bakat / Ekskul, serta Sosial & Psikologis).
* **Pembagian Berdasar Semester**: Mengelompokkan transaksi jurnal pendampingan dan konsultasi dua arah ke dalam **Semester 1 (Ganjil)** dan **Semester 2 (Genap)**.
* **Integrasi Fitur Konsultasi Mandiri (Dua Arah)**: Menampilkan rekap pencatatan saat siswa mengajukan konsultasi mandiri via portal.
* **Privasi & Hak Akses Ketat**: Hanya dapat diakses/di-generate oleh **Guru Wali pengampu murid tersebut**, **Siswa/Orang Tua yang bersangkutan**, dan **Kepala Sekolah**.

---

## 2. Pipa Agregasi Data & Kueri Database (Laravel Eloquent Pipeline)

Untuk membuat laporan individual ini, sistem mengambil data dari model Eloquent aktual di proyek: `Siswa` (tabel `students`), `KelompokGuruWali` & `KelompokGuruWaliSiswa` (`kelompok_guru_wali` & `kelompok_guru_wali_siswa`), `JurnalGuruWali` (`jurnal_guru_wali`), dan `KonsultasiGuruWali` (`konsultasi_guru_wali`).

### A. Kueri Profil & Identitas Murid (Eloquent)
```php
$siswa = \App\Models\Siswa::with([
    'enrollmentAktif.kelas',
    'enrollmentAktif.tahunAjaran',
    'kelompokGuruWali.kelompok.guru'
])->findOrFail($studentId);

$kelompokSiswa = $siswa->kelompokGuruWali;
$kelompok = $kelompokSiswa?->kelompok;
$guruWali = $kelompok?->guru;
$kelas = $siswa->enrollmentAktif?->kelas?->name ?? '—';
$tahunAjaran = $siswa->enrollmentAktif?->tahunAjaran?->name ?? '2025/2026';
```

### B. Kueri Rekap Jurnal Per Semester
```php
$jurnals = \App\Models\JurnalGuruWali::where('student_id', $studentId)
    ->where('kelompok_id', $kelompok->id)
    ->where('teacher_id', $guruWali->id)
    ->when($tahunAwal && $tahunAkhir, function ($q) use ($tahunAwal, $tahunAkhir) {
        $q->whereBetween('tanggal_waktu', ["{$tahunAwal}-07-01 00:00:00", "{$tahunAkhir}-06-30 23:59:59"]);
    })
    ->orderBy('tanggal_waktu', 'asc')
    ->get();

// Pengelompokan Semester 1 (Ganjil: Juli-Desember) dan Semester 2 (Genap: Januari-Juni)
$semester1Jurnals = $jurnals->filter(function ($j) {
    $m = \Carbon\Carbon::parse($j->tanggal_waktu)->month;
    return $m >= 7 && $m <= 12;
});

$semester2Jurnals = $jurnals->filter(function ($j) {
    $m = \Carbon\Carbon::parse($j->tanggal_waktu)->month;
    return $m >= 1 && $m <= 6;
});
```

### C. Kueri Konsultasi Mandiri Siswa
```php
$konsultasis = \App\Models\KonsultasiGuruWali::where('student_id', $studentId)
    ->where('teacher_id', $guruWali->id)
    ->whereIn('status_pengajuan', ['Selesai', 'Dikonversi ke Jurnal'])
    ->orderBy('created_at', 'asc')
    ->get();
```

---

## 3. Struktur Data Laporan Individual

```php
[
    'profil' => [
        'nama'               => $siswa->name,
        'nis'                => $siswa->nis,
        'nisn'               => $siswa->nisn,
        'kelas'              => $kelas,
        'kelompok_dampingan' => $kelompok->nama_kelompok,
        'tahun_masuk'        => $kelompokSiswa->tahun_masuk ?? 2024,
        'guru_wali'          => $guruWali->name,
        'nip_guru_wali'      => $guruWali->nip ?? '—',
    ],
    'tahun_ajaran'          => $tahunAjaran,
    'semester_1'            => [
        'total_sesi'  => $semester1Jurnals->count(),
        'daftar_sesi' => $semester1Jurnals,
    ],
    'semester_2'            => [
        'total_sesi'  => $semester2Jurnals->count(),
        'daftar_sesi' => $semester2Jurnals,
    ],
    'kumulatif'             => [
        'total_sesi_tahun_ini'       => $jurnals->count(),
        'total_konsultasi_mandiri'   => $konsultasis->count(),
        'distribusi_4_pilar'         => [
            'Akademik'               => $jurnals->where('kategori_pendampingan', 'Akademik')->count(),
            'Karakter & Kedisiplinan'=> $jurnals->where('kategori_pendampingan', 'Karakter & Kedisiplinan')->count(),
            'Minat & Bakat / Ekskul' => $jurnals->where('kategori_pendampingan', 'Minat & Bakat / Ekskul')->count(),
            'Sosial & Psikologis'    => $jurnals->where('kategori_pendampingan', 'Sosial & Psikologis')->count(),
        ],
    ],
    'pengesahan'            => [
        'kepala_sekolah' => $namaKepsek,
        'nip_kepsek'     => $nipKepsek,
        'guru_wali'      => $guruWali->name,
        'nip_guru_wali'  => $guruWali->nip ?? '—',
        'kota'           => $settings?->kota ?? config('school.kota', 'Kabupaten/Kota'),
        'tanggal'        => now()->translatedFormat('d F Y'),
    ],
]
```

---

## 4. Format Output & Layout Dokumen

Laporan dihasilkan melalui **Blade View terstandar cetak resmi**:
1. **Printable HTML Web View**: Halaman responsif dengan tombol cetak (`window.print()`), margin A4, CSS `@media print` untuk cetak langsung via dialog browser.
2. **Download PDF**: Menggunakan package `Barryvdh\DomPDF\Facade\Pdf::loadView(...)` dengan orientasi kertas **A4 Portrait**.

### Struktur Komponen Cetak:
| No | Bagian Dokumen | Komponen Blade | Keterangan |
| :---: | :--- | :--- | :--- |
| **1** | **Kop Dokumen Sekolah** | Header Instansi | Memuat nama sekolah, alamat, logo, dan garis kop surat ganda. |
| **2** | **Judul Laporan** | Header & Subtitle | *"LAPORAN HASIL PENDAMPINGAN INDIVIDUAL PESERTA DIDIK"* (Permendikdasmen No. 11/2025). |
| **3** | **I. Identitas Peserta Didik** | Tabel 4 Kolom (2x2) | Nama, NISN/NIS, Kelas, Kelompok Guru Wali, Guru Wali Pendamping. |
| **4** | **II. Rekapitulasi Sesi Semester 1** | Tabel Kronologis Sesi | Tanggal, Bentuk, Kategori 4 Pilar, Topik Pembahasan, Status Sesi & Tindak Lanjut. |
| **5** | **III. Rekapitulasi Sesi Semester 2** | Tabel Kronologis Sesi | Format konsisten dengan Semester 1. |
| **6** | **IV. Ringkasan & Konsultasi Mandiri** | Tabel Metrik & 4 Pilar | Statistik kumulatif sesi, konsultasi inisiatif siswa, dan catatan umum capaian. |
| **7** | **V. Lembar Pengesahan** | Tabel Tanda Tangan | Tanda tangan Orang Tua / Wali Murid, Guru Wali, dan Mengetahui Kepala Sekolah. |

---

## 5. Logika Pengelompokan Semester (PHP / Carbon)

```php
public function getSemesterPeriode($tanggalWaktu): string
{
    $carbon = \Carbon\Carbon::parse($tanggalWaktu);
    $month = $carbon->month;
    
    // Semester 1 (Ganjil): Juli - Desember
    if ($month >= 7 && $month <= 12) {
        return 'SEMESTER_1_GANJIL';
    }
    // Semester 2 (Genap): Januari - Juni
    return 'SEMESTER_2_GENAP';
}
```

---

## 6. Checklist Verifikasi & Validasi

1. [ ] **Verifikasi Siswa**: Hanya siswa yang terdaftar aktif dalam kelompok Guru Wali yang dapat dicetak.
2. [ ] **Pemisahan Semester Tepat**: Sesi bulan Juli–Desember masuk ke Semester Ganjil, Januari–Juni masuk ke Semester Genap.
3. [ ] **Integrasi Konsultasi Mandiri**: Sesi yang berasal dari konsultasi dua arah siswa terhitung akurat.
4. [ ] **Kop & Tanda Tangan Otomatis**: Nama dan NIP Kepala Sekolah serta Guru Wali terisi otomatis dari master data.
5. [ ] **Cetak Rapih & Presisi**: Tampilan cetak tidak terpotong margin, mendukung print browser dan unduh PDF.
