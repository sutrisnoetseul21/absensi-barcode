# Tahap 17: Implementasi Fitur Presensi Pulang (State-Based Logic)

**Status:** Selesai  
**Fokus:** Perubahan skema database & refactoring alur presensi Kiosk.

## Latar Belakang
Pada desain tahap awal, sistem hanya melayani presensi kedatangan (satu kali scan). Kebutuhan lanjutan mengharuskan sistem untuk dapat mencatat presensi pulang dalam hari yang sama untuk melacak kedisiplinan dan menjaga *audit trail*.

## Perubahan yang Dilakukan
1. **Modifikasi Database (`attendances` & `school_settings`)**
   - Menambahkan parameter waktu mutlak `batas_scan_datang_time` (09:00:00) dan `start_scan_out_time` (13:00:00) ke pengaturan sekolah.
   - Mengubah tipe `status` pada tabel presensi dari ENUM menjadi VARCHAR guna menghindari masalah duplikasi skema/pemotongan string ke depan.
   - Menambahkan kolom `scan_out_time` (TIME) dan `status_pulang` (VARCHAR) ke dalam presensi harian.

2. **Refactoring Logika Presensi (`ProcessScanAction`)**
   - Merombak arsitektur scanning menjadi model berbasis-state yang transaksional (`DB::transaction`).
   - Menerapkan mekanisme proteksi *Pessimistic Locking* (`lockForUpdate()`) selama memproses request barcode, demi mencegah isu kompetisi data (race conditions) saat tombol ditekan ganda.
   - Mengubah pesan notifikasi spesifik sesuai status, misalnya penolakan scan pulang terlalu awal atau penolakan scan ketika siswa tercatat alpa/izin di sistem.

3. **Pembaruan Interface Kiosk & Dashboard**
   - Panel Filament: Penambahan *Time Picker* di menu pengaturan presensi untuk admin, serta form untuk memodifikasi log kepulangan manual.
   - Livewire Portal Kiosk: Mendukung dan memetakan kode respons baru (error/ditolak, berhasil datang, berhasil pulang) di log aktivitas.
   - Livewire Dashboard Siswa & Detail Siswa (Wali Kelas): Menambahkan badge tampilan data **Datang/In** vs **Pulang/Out** di kalender bulanan dan log riwayat aktivitas agar lebih terperinci.

4. **Pembaruan UI Input Presensi Manual (Admin & Guru)**
   - Menambahkan kolom `Pulang` pada form input massal untuk Admin (`InputPresensiManual.php`) dan Wali Kelas (`WaliKelasDashboard.php`).
   - Menyempurnakan logika otorisasi *(authorization)* agar Guru tetap dapat menginput absensi pulang meskipun kedatangan siswa sudah tercatat otomatis oleh alat (scanner).
   - Menambahkan sinkronisasi otomatis: Jika status kedatangan dipilih sebagai *Izin*, *Sakit*, atau *Alpa*, maka form absensi pulang akan otomatis terkunci (disabled) secara visual, dan datanya akan di-sinkronisasikan menjadi Izin/Sakit/Alpa secara atomik saat disimpan.

## Catatan
Perubahan ini dilakukan tanpa menyebabkan perusakan (*breaking changes*) pada data presensi yang sudah lama berjalan, karena constraint unik (siswa, tanggal) di database tidak dicabut, dan nilai absensi pulang yang kosong diperbolehkan (nullable) bagi record yang tidak memiliki histori pulang sebelumnya.
