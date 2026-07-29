Saya memiliki aplikasi perpustakaan berbasis SLiMS (Senayan Library Management System) 9 Bulian. Saat ini desain cetakan Barcode Eksemplar dan Label Buku memiliki ukuran dan layout tabel yang berbeda. Saya ingin menyamakan desain, ukuran (dimensi), dan tata letak keduanya agar seragam saat dicetak di kertas A4.

Tugas Anda:
1. Buka file konfigurasi `printed_settings.inc.php`. Ubah pengaturan ukuran cetak (box_width, box_height, items_per_row, margin) di bagian Label (`$sysconf['print']['label']`) dan Barcode (`$sysconf['print']['barcode']`) agar persis sama. Misalnya, setel keduanya menggunakan: items_per_row = 3, box_width = 7, box_height = 4.
2. Buka file `item_barcode_generator.php` (untuk Barcode) dan `dl_print.php` (untuk Label).
3. Modifikasi kode HTML, CSS `<style>`, dan struktur tabel (`<table class="outline">`) di kedua file tersebut agar memiliki standar desain (border, padding, font, tata letak) yang identik satu sama lain.
4. Tambahkan deklarasi CSS `@media print { @page { size: A4; margin: 0.5cm; } }` pada kedua file tersebut untuk memastikan format kertas A4 terkunci saat jendela Print browser muncul.
5. Pastikan logika pemotongan baris (`if ($print_count % $items_per_row == 0) { ... }`) tetap berjalan normal.

Tolong berikan kode modifikasi lengkap untuk ketiga file tersebut!
