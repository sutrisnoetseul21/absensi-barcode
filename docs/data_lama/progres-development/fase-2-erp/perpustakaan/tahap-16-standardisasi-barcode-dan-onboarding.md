# Tahap 16: Standardisasi Barcode Global, Onboarding Wizard, dan Sistem Gembok Keamanan

Pada tahap ini, kita telah menyelesaikan sinkronisasi penomoran eksemplar buku (barcode) secara global (satu counter untuk seluruh perpustakaan) dan membangun sistem perlindungan keamanan (gembok) serta Onboarding Wizard khusus untuk instalasi baru perpustakaan.

## Ringkasan Pekerjaan

### 1. Perbaikan Bug Validasi & Form
- **Grade Level (BukuForm.php):** Memperbaiki error `Incorrect integer value: 'umum' for column 'grade_level'` dengan menggunakan `dehydrateStateUsing` untuk memastikan konversi string tipe (contoh: "umum") menjadi ID/integer yang valid sebelum disimpan ke database.
- **Harga Eksemplar (CreateBuku.php):** Memperbaiki error `Incorrect integer value: '' for column 'harga'` dengan mendeteksi string kosong dan menyimpannya sebagai `null` atau `0` (sanitasi).

### 2. Standardisasi Barcode Global (Global Counter)
Sistem sekarang memegang teguh filosofi: **Sistem penomoran barcode itu SATU COUNTER GLOBAL, bukan per-prefix.**
- Diimplementasikan di `App\Services\BarcodeService` dan `EksemplarBuku::generateKodeEksemplar()`.
- Menggunakan `lockForUpdate()` (pessimistic locking) pada tabel `school_settings` saat menggenerate barcode, mencegah terjadinya *Race Condition* / Tabrakan Nomor saat ada 2 admin menginput buku di detik yang sama.
- Menerapkan perlindungan batas maksimum hingga **999.999.999** (sebelumnya hanya 99.999) agar sistem bisa memproses barcode warisan lama SLiMS (misalnya 6 digit seperti `170100`).

### 3. EksemplarBukuObserver
- Memastikan setiap kali Eksemplar Buku ditambahkan baik lewat Form (generate otomatis) maupun manual (import/edit), urutan counter barcode (`last_barcode_number`) di tabel `school_settings` tetap sinkron dengan data tertinggi yang ada di database fisik.
- Method cerdas `autoSyncBarcodeNumber()` ditambahkan untuk mencari eksemplar dengan nomor tertinggi.

### 4. Onboarding Wizard Perpustakaan Baru
Untuk mempermudah penggunaan bagi admin yang baru pertama kali menjalankan sistem:
- Ditambahkan kolom `is_barcode_setup_completed` pada tabel `school_settings`.
- Mengambil alih (override) `Filament\Pages\Dashboard` menjadi halaman `Dashboard.php` custom.
- Jika setup belum selesai, Admin Perpustakaan akan disambut dengan **Popup Setup Barcode (Tidak Bisa di-Cancel / Close)**.
- **Tiga Opsi Setup:**
  1. *Perpustakaan Baru:* Memulai counter dari angka 1.
  2. *Migrasi dari SLiMS:* Mengarahkan (*redirect*) ke halaman Import SLiMS. Fitur import (`ImportSlimsBukuJob`) kini akan otomatis menyentang `is_barcode_setup_completed = true` setelah sinkronisasi selesai dan sukses. Terdapat juga tombol "Batal Import, Mulai dari Nomor Awal" sebagai jalan keluar (*escape hatch*).
  3. *Lanjutkan Manual:* Memasukkan angka manual untuk menjadi acuan nomor lanjutan.

### 5. Sistem Gembok Pengaturan (Password Protected)
- **File:** `PengaturanPerpustakaan.php`
- Setelah *Setup Onboarding* selesai, konfigurasi nomor urut barcode akan dikunci total secara otomatis (Read-Only).
- **Keamanan Lapis Ganda:** Untuk mengedit kembali urutan tersebut, admin harus mengklik tombol "Buka Kunci Pengaturan", yang mewajibkan admin **mengetik ulang password** akunnya (divalidasi dengan `Hash::check()`).
- **Anti Brute-Force:** Dilengkapi dengan *Rate Limiter* (maksimal 5 kali salah per 5 menit).
- **Auto Re-Lock:** State "terbuka" hanya berlaku di memori sementara. Jika form berhasil di-*Save* atau browser di-*Refresh*, sistem akan kembali tergembok dengan sendirinya demi keamanan jangka panjang.

## Kesimpulan
Sistem sekarang memiliki perlindungan mutlak terhadap duplikasi barcode eksemplar. Tidak akan ada lagi nomor barcode ganda, walau buku di-input secara berbarengan, atau diimport dalam jumlah masif dari sistem SLiMS lama. Alur Setup di awal menjamin bahwa sistem akan selalu tahu dari mana ia harus mulai menghitung barcode barunya.
