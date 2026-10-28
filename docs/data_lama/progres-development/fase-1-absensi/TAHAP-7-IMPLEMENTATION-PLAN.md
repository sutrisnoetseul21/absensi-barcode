# TAHAP-7-IMPLEMENTATION-PLAN

Dokumen ini merangkum rencana eksekusi teknis untuk fitur **Portal Siswa** (Tahap 7) tanpa menggunakan fitur wajib ganti password.

## Ringkasan Scope

Portal ini memungkinkan Siswa untuk masuk (*login*) menggunakan NISN dan Password mereka. Setelah berhasil masuk, siswa akan diarahkan ke *dashboard* yang hanya menampilkan data presensi mereka sendiri secara murni *read-only*. Siswa tidak memiliki akses untuk mengubah data absensi apapun.

## Investigasi Skema
- **Tabel `students`**: Menyimpan `nisn`, `password`, dan relasi ke `Enrollment`.
- **Tabel `enrollments`**: Menandakan siswa berada di kelas mana pada suatu tahun ajaran. Siswa hanya bisa login jika status enrollment aktif (`status = 'aktif'`).
- **Tabel `presensi`**: Menyimpan riwayat kehadiran siswa (`student_id`, `date`, `status`, `late_minutes`).

## Rate Limiting Login
Untuk mencegah serangan *brute force* pada *login* siswa:
- Komponen Livewire `SiswaLogin` akan memanfaatkan `RateLimiter` dari Laravel (misal, maksimal 5 kali percobaan gagal per menit).
- Setelah melewati batas, akses login akan diblokir sementara dengan pesan *error* ("Terlalu banyak percobaan. Coba lagi dalam XX detik").
- Pengecekan aktif/tidaknya *enrollment* dilakukan dalam *guard* saat proses otentikasi terjadi.

## Query Riwayat Presensi + Ringkasan
- Data presensi di-*query* dengan secara ketat menggunakan *scope* ID Siswa yang sedang login (melalui `Auth::guard('siswa')->user()->id`).
- Komponen *dashboard* akan menyediakan `select` untuk filter bulan.
- *Query* akan memuat riwayat harian untuk bulan yang dipilih (misal, 1 sampai 31), dan menghitung akumulasi (*sum* untuk `late_minutes`, *count* untuk `H`, `T`, `I`, `S`, `A`) murni dari hasil filter bulan tersebut.

## Urutan Eksekusi
1. Membuat komponen *Livewire* `SiswaLogin`.
2. Menerapkan *Rate Limiter* dan validasi status *enrollment* di form login.
3. Membuat komponen *Livewire* `SiswaDashboard`.
4. Merancang tampilan (UI) tabel kalender absensi khusus siswa.
5. Memastikan rute (`/siswa` dan `/siswa/login`) dilindungi oleh *middleware* `auth:siswa` atau setaranya.

## Test Manual / Verifikasi
1. Menguji login menggunakan NISN valid dan tidak valid.
2. Memicu *rate limiter* dengan sengaja menyalahkan password >5 kali.
3. Mencoba *login* dengan akun siswa yang statusnya sudah "Lulus" atau "Pindah" (harus gagal).
4. Verifikasi bahwa data yang muncul di *dashboard* murni hanya milik siswa tersebut.
5. Verifikasi pergantian filter bulan mengubah ringkasan (Total Alpa, dsb) secara dinamis.
