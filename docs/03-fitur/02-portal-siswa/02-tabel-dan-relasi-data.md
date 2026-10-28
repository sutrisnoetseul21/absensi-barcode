# 🗄️ Tabel Database & Relasi Data Portal Siswa

Dokumen ini mendokumentasikan tabel-tabel database yang berinteraksi langsung dengan Portal Siswa, pola query otorisasi data mandiri, serta penanganan data historis.

---

## 1. Tabel-Tabel Database yang Diakses Siswa

| Nama Tabel | Peran di Portal Siswa | Hak Akses Siswa |
| :--- | :--- | :--- |
| `students` | Identitas dasar siswa yang sedang login | Read-Only (Read data diri) |
| `attendances` | Riwayat presensi masuk gerbang & kepulangan | Read-Only (Filter `student_id`) |
| `presensi_sholat_dhuhur` | Riwayat ibadah sholat dhuhur & jumat | Read-Only (Filter `student_id`) |
| `leave_requests` | Pengajuan izin/sakit siswa | Create, Read, Update (Draft) |
| `peminjamans` | Sirkulasi buku perpustakaan yang dipinjam | Read-Only (`peminjam_id = student.id`) |
| `spikap_laporan` | Tiket laporan aduan kasus / perundungan | Create, Read (Aduan miliknya) |
| `konsultasi_guru_wali` | Permohonan bimbingan dengan guru wali | Create, Read |
| `badge_karakter_siswa` | Penghargaan karakter yang diperoleh siswa | Read-Only |
| `nilai_ujians` | Perolehan nilai ujian akademik (jika sudah dipublikasi) | Read-Only (`student_id` & `is_published = true`) |
| `pengumuman` | Edaran resmi dan pengumuman sekolah | Read-Only (`aktifSekarang()`) |

---

## 2. Pola Query Otorisasi Data Mandiri (*Self-Data Isolation*)

Untuk menjamin siswa **hanya dapat melihat data miliknya sendiri**, setiap query di controller/komponen Livewire selalu diikat ke model `student` yang terafiliasi dengan user yang sedang login:

```php
// Mengambil model student milik user login
$student = Auth::user()->student;

// 1. Mengambil riwayat presensi
$attendances = Attendance::where('student_id', $student->id)
    ->whereMonth('date', $selectedMonth)
    ->get();

// 2. Mengambil peminjaman buku perpustakaan (Polymorphic)
$borrowedBooks = Peminjaman::where('peminjam_type', Siswa::class)
    ->where('peminjam_id', $student->id)
    ->where('status', 'dipinjam')
    ->get();

// 3. Mengambil permohonan izin
$leaveRequests = LeaveRequest::where('student_id', $student->id)
    ->latest()
    ->get();
```

---

## 3. Penanganan Siswa Lulus (Alumni) & Siswa Mutasi

Di dalam [`SiswaMainDashboard.php`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Livewire/SiswaMainDashboard.php):
- **Status `lulus`**:
  Jika `$student->status === 'lulus'`, dashboard mengunci fitur presensi aktif dan mengarahkan tampilan ke form **Tracer Study Alumni** (`alumnis`, `alumni_jenjangs`) untuk mencatat nama SMA/SMK lanjutan.
- **Status `mutasi`**:
  Jika `$student->status === 'mutasi'`, dashboard mengunci pengajuan izin dan menampilkan konfirmasi data mutasi (`tujuan_mutasi`, `alasan_mutasi`).
- **Data Historis Presensi**:
  Seluruh riwayat presensi (`attendances`) dan nilai akademik tetap aman tersimpan di database dan tidak dihapus.
