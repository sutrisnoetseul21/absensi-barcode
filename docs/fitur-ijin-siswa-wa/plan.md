# Rencana Pengembangan Fitur Persetujuan Ijin Siswa via WhatsApp

## Konsep Utama
Fitur ini memungkinkan wali kelas untuk menerima notifikasi pengajuan ijin siswa lengkap beserta lampiran bukti (gambar/PDF) melalui WhatsApp. Wali kelas dapat langsung memberikan persetujuan (Approve) atau penolakan (Reject) melalui link pendek unik yang dikirimkan bersama pesan WA tersebut.

## Alasan Pemilihan Konsep
- **Menggunakan Link Pendek (Short URL):** Dipilih sebagai pengganti Button (Tombol) interaktif bawaan WhatsApp karena kebijakan Meta saat ini sering memblokir pesan berjenis Button pada API unofficial (seperti Evolution API / Baileys) di aplikasi WhatsApp Android. Link pendek memastikan kompatibilitas 100% di semua perangkat (Android, iOS, WA Web).
- **Akurasi Data:** Dengan link persetujuan unik per pengajuan (contoh: `/a/8X9J`), sistem tidak akan bingung membedakan ijin mana yang sedang disetujui, meskipun ada banyak siswa yang mengajukan ijin secara bersamaan.
- **Keamanan Media:** Evolution API terbukti aman dan mampu mengirimkan media (Base64 atau Public URL) sebagai lampiran dokumen/foto secara langsung ke chat WA wali kelas.

## Alur Sistem (Flow)

1. **Siswa Mengisi Form Ijin:** 
   Siswa login ke portal, mengisi form pengajuan ijin (sakit/ijin/dll), dan mengunggah bukti (misalnya foto surat dokter).

2. **Sistem Mengirim Pesan WA ke Wali Kelas:**
   Sistem (via `WhatsAppGatewayService`) mengambil file lampiran, mengubahnya (atau mengambil URL-nya), dan memanggil endpoint `/message/sendMedia/` pada Evolution API.
   Format Pesan WA (Caption Lampiran) kira-kira sebagai berikut:
   ```text
   🔔 [PERMINTAAN IJIN SISWA]

   Nama: Budi Santoso
   Kelas: 10 IPA 1
   Alasan: Sakit 
   Tanggal: 10 Nov - 12 Nov

   Mohon Bapak/Ibu segera memberikan persetujuan melalui link berikut:
   ✅ Setuju: http://domainsekolah.com/approve/X8K2/accept
   ❌ Tolak: http://domainsekolah.com/approve/X8K2/reject
   ```

3. **Proses Persetujuan (Approval):**
   - Wali kelas mengklik link persetujuan di chat WhatsApp.
   - Sistem membuka halaman web singkat yang memvalidasi token/ID persetujuan.
   - Sistem memperbarui status `LeaveRequest` di database menjadi `approved` (atau `rejected`).
   - (Opsional) Sistem menjalankan service presensi (`LeaveRequestService`) untuk otomatis membuatkan absen ijin/sakit di tanggal tersebut.
   - (Opsional) Siswa mendapat notifikasi WA balasan bahwa ijinnya telah disetujui.

## Kebutuhan Modifikasi Kode (Action Plan)

1. **Service WhatsApp (`app/Services/WhatsAppGatewayService.php`):**
   - Menambahkan method baru `sendMedia()` untuk mengakomodasi pengiriman gambar/PDF melalui endpoint Evolution API.

2. **Model Ijin (`app/Models/LeaveRequest.php`):**
   - Menambahkan field/mekanisme token unik, atau menggunakan ID UUID bawaan yang sudah ada. Jika UUID terlalu panjang untuk link WA, kita bisa membuat kolom `approval_token` yang pendek (misal 5-6 karakter unik).

3. **Route & Controller (`routes/web.php` & Controller Terkait):**
   - Membuat route khusus untuk menangani klik link dari WA, misal: `GET /approve/{token}/{action}`.
   - Membuat controller sederhana untuk menampilkan pesan "Sukses" setelah link diklik.

4. **Service Notifikasi / Portal Siswa:**
   - Memodifikasi logika pengajuan ijin agar men-trigger `sendMedia` ke nomor HP wali kelas setelah data berhasil disimpan di database.
