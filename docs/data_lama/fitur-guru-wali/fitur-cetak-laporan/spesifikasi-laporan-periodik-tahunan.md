# SPESIFIKASI TEKNIS FITUR: GENERATOR LAPORAN PENDAMPINGAN PERIODIK / TAHUNAN GURU WALI

> **Dokumen Panduan Pengembang & AI Agent**  
> **Versi**: 1.0  
> **Tanggal**: September 2026  
> **Landasan Regulasi**: Permendikdasmen No. 11 Tahun 2025 & Kepmendikdasmen No. 221/P/2025 (Ekuivalensi Beban Kerja 2 JP/Minggu)

---

## 1. Ringkasan Eksekutif & Tujuan Fitur

Dokumen ini berisi spesifikasi teknis untuk fitur **Generator Laporan Pendampingan Periodik / Tahunan Guru Wali** pada aplikasi portal guru. 

Fitur ini berfungsi untuk **merekapitulasi seluruh transaksi data jurnal harian, rujukan, dan pemantauan bulanan** selama 1 tahun ajaran penuh (atau 1 semester) menjadi laporan resmi berbasis dokumen (`.docx` / `.pdf`).

### Tujuan Utama
1. **Otomatisasi Laporan Akhir**: Mengeliminasi penyusunan laporan manual dengan mengagregasi data dari database `jurnal_guru_wali` dan `rujukan_guru_wali`.
2. **Bukti Fisik Ekuivalensi 2 JP/Minggu**: Menghasilkan dokumen resmi berseri yang ditandatangani oleh Guru Wali dan disahkan oleh Kepala Sekolah untuk kebutuhan supervisi, verifikasi jam kerja, dan akreditasi.
3. **Generasi Sintesis AI (LLM Synthesis)**: Menggunakan AI Agent untuk merangkum catatan naratif harian (*Uraian Pembahasan & Rencana Tindak Lanjut*) menjadi paragraf capaian perkembangan individu murid yang koheren tanpa manipulasi data.

---

## 2. Pipa Agregasi Data & Kueri Database (Laravel Eloquent Pipeline)

Untuk menghasilkan Laporan Periodik / Tahunan Kelompok Guru Wali, backend mengeksekusi agregasi data dari model `KelompokGuruWali`, `JurnalGuruWali`, `KelompokGuruWaliSiswa`, dan `Siswa`.

### A. Agregasi Kinerja Keseluruhan (Metrik Utama)
```php
$jurnals = \App\Models\JurnalGuruWali::where('kelompok_id', $kelompok->id)
    ->where('teacher_id', $guruWali->id)
    ->when($tanggalMulai && $tanggalSelesai, function ($q) use ($tanggalMulai, $tanggalSelesai) {
        $q->whereBetween('tanggal_waktu', [$tanggalMulai, $tanggalSelesai]);
    })
    ->get();

$totalSesi = $jurnals->count();
$totalIndividu = $jurnals->where('jenis_pendampingan', 'Individu')->count();
$totalKelompok = $jurnals->whereIn('jenis_pendampingan', ['Kelompok Kecil', 'Klasikal'])->count();

// Distribusi 4 Pilar
$distribusiPilar = [
    'Akademik'                => $jurnals->where('kategori_pendampingan', 'Akademik')->count(),
    'Karakter & Kedisiplinan' => $jurnals->where('kategori_pendampingan', 'Karakter & Kedisiplinan')->count(),
    'Minat & Bakat / Ekskul'  => $jurnals->where('kategori_pendampingan', 'Minat & Bakat / Ekskul')->count(),
    'Sosial & Psikologis'     => $jurnals->where('kategori_pendampingan', 'Sosial & Psikologis')->count(),
];
```

### B. Agregasi Perkembangan per Murid Dampingan
```php
$anggotaSiswa = $kelompok->anggotaAktif()
    ->with(['siswa.enrollmentAktif.kelas'])
    ->get()
    ->map(function ($anggota) use ($jurnals) {
        $siswa = $anggota->siswa;
        $jurnalSiswa = $jurnals->where('student_id', $siswa->id);
        
        // Kategori dominan yang paling sering dibahas
        $kategoriDominan = $jurnalSiswa->groupBy('kategori_pendampingan')
            ->sortByDesc->count()
            ->keys()
            ->first() ?? '—';
            
        // Sesi terakhir
        $sesiTerakhir = $jurnalSiswa->sortByDesc('tanggal_waktu')->first();
        
        return [
            'nama'              => $siswa->name,
            'nisn'              => $siswa->nisn ?? '—',
            'kelas'             => $siswa->enrollmentAktif?->kelas?->name ?? '—',
            'total_sesi'        => $jurnalSiswa->count(),
            'kategori_dominan'  => $kategoriDominan,
            'status_terakhir'   => $sesiTerakhir?->status_sesi ?? 'Belum ada sesi',
            'catatan_terakhir'  => $sesiTerakhir?->uraian_pembahasan ?? '—',
        ];
    });
```

### C. Agregasi Rujukan & Kolaborasi
```php
// Diambil dari kolom rujukan_kolaborasi pada jurnal_guru_wali
$rekapRujukan = $jurnals->groupBy('rujukan_kolaborasi')->map(function ($items, $pihak) {
    return [
        'pihak'        => $pihak, // 'Mandiri', 'Wali Kelas', 'Guru BK', 'Orang Tua / Wali'
        'jumlah_kasus' => $items->count(),
        'kategori'     => $items->pluck('kategori_pendampingan')->unique()->implode(', '),
        'tuntas'       => $items->where('status_sesi', 'Tuntas / Selesai')->count(),
        'lanjutan'     => $items->whereIn('status_sesi', ['Dalam Pemantauan', 'Bimbingan Lanjutan'])->count(),
    ];
});
```

---

## 3. Matriks Struktur Dokumen Laporan Periodik / Tahunan

Laporan Tahunan Kelompok yang dihasilkan mengikuti struktur hirarki cetak berikut:

| Bagian Dokumen | Komponen Blade | Sumber Data | Keterangan Format |
| :--- | :--- | :--- | :--- |
| **Kop Sekolah** | Header Resmi | `PengaturanSekolah` | Logo sekolah, nama instansi, alamat, kontak, garis kop ganda. |
| **Judul Laporan** | Judul Resmi | Permen 11/2025 | *"LAPORAN KINERJA PENDAMPINGAN KELOMPOK GURU WALI"* & Periode Tahun Ajaran. |
| **I. Identitas Guru Wali** | Tabel 4 Kolom | Master Data | Nama Guru, NIP, Kelompok Binaan, Jumlah Siswa, Beban Ekuivalensi (2 JP). |
| **II. Ringkasan Kinerja** | Tabel & Barometer | Agregasi Metrik | Total Sesi (Individu vs Kelompok), Persentase Kepatuhan (Min 1x/Bulan). |
| **III. Tabel Siswa Binaan** | Tabel Matriks Siswa | Agregasi Siswa | Kolom: No, Nama Murid, Kelas, Total Sesi, Dominasi Kasus, Status Terakhir. |
| **IV. Distribusi 4 Pilar** | Tabel Persentase | Data Pilar | Rincian jumlah dan persentase sesi bimbingan pada tiap pilar. |
| **V. Rekap Kolaborasi** | Tabel Rujukan | Data Rujukan | Rekap koordinasi dengan Wali Kelas, Guru BK, dan Orang Tua Murid. |
| **VI. Lembar Pengesahan** | Tanda Tangan Resmi | Master Guru | Tanda Tangan Guru Wali Pengampu dan Mengetahui/Mengesahkan Kepala Sekolah. |

---

## 4. Format Output & Arsitektur Implementasi

Fitur ini diimplementasikan di **Portal Guru** pada sub-menu khusus:
* **Rute**: `/portal-guru/guru-wali/cetak` (`portal-guru.guru-wali.cetak`)
* **Komponen Livewire**: `App\Livewire\PortalGuru\GuruWali\CetakLaporanGuruWali`
* **Blade View Portal**: `resources/views/livewire/portal-guru/guru-wali/cetak-laporan-guru-wali.blade.php`
* **Format Cetak**:
  1. **Printable HTML (Browser Print)**: Preview interaktif dengan dialog `window.print()` (styling cetak A4 rapi).
  2. **Download PDF**: Ekspor instan format `.pdf` melalui package `Barryvdh\DomPDF\Facade\Pdf`.

---

## 5. Checklist Verifikasi & Validasi

1. [ ] **Akurasi Agregasi**: Total sesi dan hitungan pilar cocok 100% dengan database `jurnal_guru_wali`.
2. [ ] **Kelengkapan Daftar Siswa**: Seluruh siswa aktif dalam kelompok tampil pada matriks laporan.
3. [ ] **Kop & Tanda Tangan Resmi**: Nama Kepala Sekolah dan Guru Wali beserta NIP terisi otomatis.
4. [ ] **Kesesuaian Regulasi**: Mencantumkan klausul ekuivalensi beban kerja 2 JP/Minggu sesuai Kepmendikdasmen No. 221/P/2025.
5. [ ] **Responsif & Presisi Cetak**: Desain cetak A4 tidak terpotong dan margin proporsional.
