# 🎒 Transkrip Nilai Siswa & Evaluasi Ketuntasan KKM

Dokumen ini mendokumentasikan antarmuka akses mandiri peserta didik dan orang tua untuk melihat rekam jejak nilai hasil ujian akademik melalui rute `/portal-siswa/nilai` yang dikelola oleh komponen Livewire [`App\Livewire\PortalSiswa\SiswaNilai`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Livewire/PortalSiswa/SiswaNilai.php).

---

## 1. Lapisan Pengamanan & Filter Akses Publikasi

Untuk mencegah kebocoran skor mentah sebelum guru selesai melakukan telaah atau sebelum seluruh rombel menyelesaikan tes susulan, Portal Siswa dilindungi oleh query filter keamanan tingkat basis data:

### A. Validasi Identitas Siswa
```php
$user = Auth::user();
if (!$user || !$user->student) {
    abort(403, 'Akses ditolak: Anda tidak memiliki profil siswa.');
}
```

### B. Filter JSON `published_classes`
```php
$nilaiUjians = NilaiUjian::with([
        'ujianAkademik.mataPelajaran', 
        'ujianAkademik.jenisUjian', 
        'ujianAkademik.tahunAjaran'
    ])
    ->where('student_id', $siswa->id)
    ->whereHas('ujianAkademik', function ($query) {
        // Hanya ambil jika class_id siswa ada di dalam JSON array published_classes
        $query->whereRaw('JSON_CONTAINS(published_classes, JSON_QUOTE(nilai_ujians.class_id))');
    })
    ->get();
```
Jika guru pengampu belum mengaktifkan toggle publikasi untuk kelas yang bersangkutan, record nilai tidak akan muncul di layar siswa (transparan, tanpa error/crash).

---

## 2. Pengelompokan Hierarkis Transkrip Asesmen

Nilai siswa disajikan secara rapi dengan pengelompokan bertingkat agar mudah ditinjau per semester:

```mermaid
graph TD
    A[Akun Siswa Login] --> B[Tahun Ajaran: 2026/2027 Ganjil]
    B --> C1[Event: Sumatif Akhir Semester - SAS]
    B --> C2[Event: Sumatif Tengah Semester - STS]
    
    C1 --> D1[Matematika: Skor 88 | KKM 75 -> TUNTAS]
    C1 --> D2[Bahasa Inggris: Skor 92 | KKM 75 -> TUNTAS]
    C1 --> D3[IPA: Skor 70 | KKM 75 -> BELUM TUNTAS]
```

### Format Kartu Nilai Siswa:
1. **Nama Mata Pelajaran**: Label mapel resmi (contoh: *Pendidikan Pancasila*, *Informatika*).
2. **Kategori Asesmen**: Jenis evaluasi (contoh: *Sumatif Tengah Semester*, *Asesmen Akhir Semester*).
3. **Nilai Akhir Peserta**: Skor riil yang diperoleh siswa (skala 0 - 100).
4. **Target KKM/KKTP**: Ambang batas minimal yang ditetapkan sekolah untuk mata pelajaran tersebut.
5. **Indikator Ketuntasan Visual**:
   - 🟢 **Tuntas KKM**: Nilai siswa mencapai atau melampaui batas KKM.
   - 🔴 **Belum Tuntas (Perlu Remidi)**: Nilai siswa berada di bawah KKM, menjadi acuan bagi siswa dan wali murid untuk segera mengikuti bimbingan remidial dengan guru pengampu.

---

## 3. Manfaat Transparansi bagi Siswa & Wali Murid

1. **Akses Real-Time Tanpa Menunggu Cetak Rapor**: Siswa dapat langsung mengetahui hasil evaluasi belajar beberapa menit setelah guru merilis nilai.
2. **Portofolio Perkembangan Akademik**: Siswa dapat memantau grafik perolehan nilai dari semester ke semester untuk mengetahui mata pelajaran mana yang perlu ditingkatkan prestasinya.
3. **Penyelarasan dengan Modul Guru Wali**: Nilai yang belum tuntas akan menjadi bahan diskusi pembinaan karakter saat sesi bimbingan rutin guru wali asuh.
