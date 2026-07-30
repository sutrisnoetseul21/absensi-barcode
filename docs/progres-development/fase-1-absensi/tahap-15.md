# Tahap 15: Pengembangan Kamera Kios Presensi dan Anti-Spam

## Tujuan
Memperbarui Kios Presensi (`/scan` dan `/scan-nis`) agar mendukung pemindaian kartu barcode secara langsung menggunakan kamera internal (Webcam/Kamera HP) tanpa memerlukan alat scanner fisik (USB Scanner), sekaligus menangani bug pemotongan UUID panjang dan masalah presensi ganda berturut-turut.

## Perubahan yang Dilakukan

1. **Integrasi HTML5-QRCode (Offline)**
   - Menambahkan library JavaScript `html5-qrcode.min.js` secara lokal di folder `public/js/` agar kios presensi bisa berjalan 100% secara offline.
   - Kamera memindai menggunakan format deteksi 1D (Barcode Batang) dan juga sanggup mendeteksi kode QR.
   
2. **Penyesuaian Antarmuka (UI) Kios**
   - Menambahkan kotak kamera yang menggantikan logo visualizer di tengah saat kamera aktif.
   - Menambahkan menu kendali kamera di pojok kanan bawah yang berisi:
     - Tombol "Gunakan Kamera" / "Matikan Kamera".
     - Menu Dropdown (Select) perangkat kamera yang tersedia.
     - Indikator izin sistem.

3. **Multi-Field UUID Backend (`ProcessScanAction.php`)**
   - Siswa sering kali menggunakan kartu berisikan ID UUID.
   - Backend `ProcessScanAction.php` telah diperbarui dengan `where('id', $barcode)` beserta `orWhere` untuk `nisn`, `nis`, dan `barcode_code`.

4. **Pencegahan Pemotongan Teks Otomatis (Bug Truncation)**
   - Menghapus listener `$watch('barcode')` di Livewire/Alpine yang sebelumnya memotong teks panjang secara otomatis ketika mencapai panjang karakter >= 10. Ini memperbaiki bug di mana UUID 36 karakter terpotong menjadi 10 karakter.
   
5. **Pencegahan Presensi Beruntun (Smart Anti-Spam Memory)**
   - Mengubah logika `onCameraScan` agar **kamera tetap menyala terus-menerus** seperti mesin kasir, alih-alih dimatikan per-scan.
   - Menanamkan **Logika Anti-Spam (Smart Cooldown)**:
     - Jika hasil scan *SAMA* dengan hasil sukses barusan (siswa menahan kartu), sistem mengabaikan scan tersebut selama **6 detik**.
     - Jika hasil scan *BERBEDA* (kartu siswa selanjutnya), sistem hanya memberikan jeda pendek **1 detik** agar antrean berjalan kilat.

## File Terpengaruh
- `public/js/html5-qrcode.min.js` (Baru)
- `resources/views/livewire/attendance-kiosk.blade.php`
- `resources/views/livewire/attendance-kiosk-nis.blade.php`
- `app/Actions/ProcessScanAction.php`

## Status
**Selesai (Deployed to GitHub)**
