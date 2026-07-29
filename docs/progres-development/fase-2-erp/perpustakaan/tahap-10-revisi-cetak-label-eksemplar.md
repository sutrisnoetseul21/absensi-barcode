# Tahap 10: Unifikasi Cetak Barcode & Label SLiMS, Auto-Generate Kode 5 Digit

Fokus tahap ini adalah menyempurnakan alur kerja pustakawan dalam mengelola fisik buku (eksemplar) mulai dari penomoran inventaris (barcode) hingga proses penempelan label yang secara visual dan ukuran di-unifikasi 100% mengikuti standar SLiMS.

## 1. Refactor Auto-Generate Kode Eksemplar
Sistem penomoran barcode telah diubah dari 3 digit lama (`UMM001`) menjadi **5 digit dengan leading zeros (`UMM00001`)**.
- **Logika Per-Prefix**: Nomor urut tidak berjalan global, melainkan me-reset atau independen untuk setiap prefix (Misal: `PAI00001`, `MTK00001`).
- **Regex Pengekstrak Angka**: Agar sistem dapat melanjutkan nomor urut dari format data 3 digit versi lama dengan mulus, sistem kini menggunakan fungsi `preg_replace('/[^0-9]/', '', $kode_terakhir)` untuk mengambil murni rentetan angka terakhir.

## 2. Refactor Logika Cetak Label Spine (Punggung)
- **Cetak Tunggal**: Tombol cetak pada masing-masing baris tabel (single record) kini diganti menjadi form-action di Filament. Sistem menanyakan berapa *jumlah label yang ingin dicetak*, dengan angka maksimal dibatasi otomatis oleh *total eksemplar fisik* buku tersebut.
- **Cetak Massal (Bulk)**: Pada Bulk Action, sistem tidak lagi mengirimkan kumpulan ID "Buku", melainkan mengambil dan meneruskan kumpulan "ID Eksemplar". Sehingga, jika pustakawan menyeleksi 2 judul buku yang masing-masing memiliki 5 fisik eksemplar, sistem akan langsung merender 10 label/barcode secara otomatis.

## 3. Unifikasi Tampilan SLiMS (Frontend PDF/Print HTML)
Demi mempertahankan kompatibilitas estetika dengan pustakawan yang terbiasa menggunakan SLiMS, dilakukan sentuhan CSS murni tanpa package tambahan (seperti DOMPDF/GD manipulation) untuk me-replika format SLiMS:

1. **Ukuran dan Grid**: Memakai ukuran spesifik box `6cm x 3,5cm`. Dalam orientasi A4 Portrait (kertas terkunci via `@page { size: A4; margin: 0.5cm; }`), grid akan menghasilkan layout 3 kolom, memuat maksimum 21 stiker per halamannya.
2. **Desain Spine Label (Punggung)**: Memakai Header berwarna abu-abu cerah untuk nama sekolah, diikuti dengan tiga baris *Call Number* DDC di tengah kotak, dan memiliki border solid hitam agar mudah dipotong.
3. **Desain Barcode (Sirkulasi)**: 
   - **Trik Guard Bar (Garis Panjang Tepi)**: SLiMS mencetak teks angka (`B 0 0 0 0 1`) dengan menyelipkannya ke dalam dasar barcode, sehingga garis bar sebelah tepi kiri dan kanannya tampak "memanjang ke bawah". Di tahap ini, trik CSS overlay digunakan: Barcode digambar setinggi mungkin, lalu sebuah `<div class="barcode-text-overlay">` berwarna putih menimpa titik tengah-bawah barcode tersebut. Trik sederhana ini menyempurnakan ilusi *Guard Bar*.
   - **Spasi Karakter**: Teks kode sengaja di- *implode* menggunakan spasi tambahan `implode(' ', str_split($kode))` untuk hasil cetak yang renggang dan nyaman di mata pustakawan.

## 4. Bugfix Relasi Kolom Pengaturan
- Memperbaiki pemanggilan field database tabel konfigurasi sekolah dari `$sekolah->nama_sekolah` menjadi `$sekolah->school_name` sehingga error *blank header* pada view cetak berhasil teratasi.
