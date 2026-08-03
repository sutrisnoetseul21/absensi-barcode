<?php

namespace App\Services;

use App\Models\Buku;

class CallNumberService
{
    /**
     * Kata-kata sandang yang harus diabaikan saat mengambil inisial judul.
     * Array ini menggunakan huruf kecil.
     */
    protected static array $skippedWords = [
        'a', 'an', 'the',
        'sebuah', 'seekor', 'seorang', 'sepotong', 'secarik', 'seutas',
        'buku', 'kumpulan'
    ];

    public static function generate(Buku $buku): string
    {
        // Baris 1: Kode Koleksi (SR = Sirkulasi, RF = Referensi)
        $kategori = strtolower(trim($buku->kategoriBuku?->nama_kategori ?? ''));
        $baris0 = ($kategori === 'referensi') ? 'RF' : 'SR';

        // Baris 2: Kode DDC
        $baris1 = $buku->klasifikasiDdc ? $buku->klasifikasiDdc->kode_ddc : 'XXX';

        // Baris 3: 3 huruf awal kata pertama penulis, uppercase
        $penulis = trim($buku->penulis ?? '');
        $baris2 = 'XXX';
        if ($penulis !== '') {
            $cleanPenulis = preg_replace('/^(dr|drs|dra|prof|ir|h|hj)\.?\s+/i', '', $penulis);
            $words = preg_split('/\s+/', $cleanPenulis);
            $firstWord = preg_replace('/[^a-zA-Z]/', '', $words[0] ?? '');
            if ($firstWord === '' && isset($words[1])) {
                $firstWord = preg_replace('/[^a-zA-Z]/', '', $words[1]);
            }
            if ($firstWord !== '') {
                $baris2 = strtoupper(substr($firstWord, 0, 3));
            }
        }

        // Baris 4: 1 huruf pertama kata bermakna pertama dari judul, lowercase
        $judul = trim($buku->judul ?? '');
        $baris3 = 'x';
        if ($judul !== '') {
            $words = preg_split('/[\s,\.]+/', strtolower($judul));
            foreach ($words as $word) {
                $cleanWord = preg_replace('/[^a-z]/', '', $word);
                if ($cleanWord !== '' && !in_array($cleanWord, self::$skippedWords)) {
                    $baris3 = substr($cleanWord, 0, 1);
                    break;
                }
            }
        }

        return $baris0 . "\n" . $baris1 . "\n" . $baris2 . "\n" . $baris3;
    }
}
