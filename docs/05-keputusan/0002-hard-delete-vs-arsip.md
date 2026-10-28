# ADR 0002: Kebijakan Hard Delete vs Status Arsip Data Siswa & Presensi

## Status
**Accepted** (2026-09-02)

## Konteks Masalah
Dalam operasional sekolah, siswa yang lulus (*alumni*) atau pindah (*mutasi*) tidak boleh serta merta dihapus (*hard delete*) dari database karena riwayat kehadiran, nilai, dan pinjaman perpustakaan mereka terikat pada laporan tahun ajaran sebelumnya.

## Keputusan yang Diambil
1. **Tidak Menggunakan Hard Delete untuk Master Siswa**:
   - Status siswa dikelola via kolom status (`aktif`, `alumni`, `mutasi`).
   - Siswa yang lulus dialihkan ke modul Alumni / Tracer Study tanpa menghapus foreign key relasi presensinya.
2. **Koleksi Log**:
   - Data presensi (`attendances`) dan log WhatsApp (`whats_app_notification_logs`) tidak dihapus per sesi, melainkan dapat diarsipkan tahunan jika ukuran tabel sudah melebihi batas performa.

## Konsekuensi
### Positif:
- Integritas data historis sekolah 100% aman dan dapat diaudit sewaktu-waktu.
- Rekap tahunan tidak akan mengalami *broken relationship*.
### Negatif:
- Query filtering harus selalu menyertakan kondisi `status = 'aktif'` agar siswa alumni tidak muncul di daftar scan presensi aktif.
