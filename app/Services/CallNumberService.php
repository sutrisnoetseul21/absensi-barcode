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
        // Baris 1: Kode DDC
        $baris1 = $buku->klasifikasiDdc ? $buku->klasifikasiDdc->kode_ddc : 'XXX';

        // Baris 2: 3 huruf awal kata terakhir penulis, uppercase
        $penulis = trim($buku->penulis ?? '');
        $baris2 = 'XXX';
        if ($penulis !== '') {
            $words = preg_split('/\s+/', $penulis);
            $lastWord = end($words);
            // Ambil 3 huruf pertama
            $baris2 = strtoupper(substr($lastWord, 0, 3));
            // Jika kurang dari 3 huruf, ya biarkan saja sesuai panjangnya
        }

        // Baris 3: 1 huruf pertama kata bermakna pertama dari judul, lowercase
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

        return $baris1 . "\n" . $baris2 . "\n" . $baris3;
    }
}
