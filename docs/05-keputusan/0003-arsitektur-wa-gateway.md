# ADR 0003: Standardisasi Service Gateway WhatsApp Terpadu

## Status
**Accepted** (2026-09-09)

## Konteks Masalah
Sebelumnya, pengiriman pesan WhatsApp tersebar di beberapa controller dan event handler secara sporadis dengan format parameter API gateway yang tidak konsisten.

## Keputusan yang Diambil
Membuat satu pintu gerbang layanan terpusat: [`App\Services\WhatsAppGatewayService`](file:///home/sutris-remote/Documents/projek-laravel/projek-absensi-barcode/app/Services/WhatsAppGatewayService.php).
- Seluruh modul (Presensi Gerbang, Presensi Sholat, Guru Wali, SPIKAP, dan Broadcast Manual) wajib memanggil method service terpusat.
- Logging status pengiriman (sukses/gagal/respon JSON dari provider) otomatis dicatat ke tabel `whats_app_notification_logs`.

## Konsekuensi
### Positif:
- Mudah mengganti provider WhatsApp Gateway di masa depan tanpa harus mengubah puluhan file controller.
- Monitoring kegagalan kirim terpusat di dashboard admin.
### Negatif:
- Perlu penanganan timeout dan retry policy agar request API tidak memperlambat respon antarmuka (dipindahkan ke background queue/scheduler).
