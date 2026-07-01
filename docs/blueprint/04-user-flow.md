# 04. User Flow

Buat diagram/flow untuk:

- **Flow scan absensi (High-Speed & Concurrent)**:
  - Siswa tunjuk kartu → scanner (sebagai keyboard wedge) mengirim angka barcode + Enter dengan sangat cepat.
  - Browser menangkap input secara *Asynchronous*.
  - Jika **Barcode tidak ada di database** → UI langsung menolak dan mengeluarkan suara peringatan "Barcode tidak terdaftar".
  - Jika **Barcode valid** → cek status (Hadir/Telat/Sudah Absen). Jika Telat, hitung otomatis `late_minutes`.
  - Layar & Suara langsung merespon (misal: "Terima Kasih Budi" atau "Maaf Budi, Telat 15 Menit").
  - Sistem di *front-end* langsung siap menerima scan siswa berikutnya tanpa menunggu proses simpan DB selesai (non-blocking), menampung scan puluhan siswa yang berurutan.
- **Flow login admin → buka menu absensi** (wajib login sebelum menu scan aktif — ini kunci anti-kecurangan).
- **Flow cetak kartu OSIS**: admin pilih satu/beberapa siswa → klik "Cetak Kartu" → sistem meng-generate file PDF layout OSIS.
- **Flow lihat dashboard publik**: pengguna buka halaman depan → melihat Wall of Fame (5 kelas terajin) → pilih tahun ajaran/kelas/bulan → melihat visualisasi Chart.js/ApexCharts (Donut, Bar, Line).
- **Flow kenaikan kelas**: Super Admin buka menu tahun ajaran baru → mapping massal siswa → arsip tahun lama.
- **Flow portal Wali Kelas**: Wali kelas login → lihat rekap kelas → lihat peringatan *Alert* siswa bermasalah (akumulasi telat banyak) → input absensi manual (bisa dicari pakai nama/NISN) jika ada yang izin/sakit.
- **Flow import/export Excel**: Admin masuk menu import → download template / upload file Excel → update data (jika NISN ada) atau insert baru.
- **Flow login siswa**: siswa login via NISN → tampil riwayat absensinya sendiri.
