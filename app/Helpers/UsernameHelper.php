<?php

namespace App\Helpers;

use App\Models\Guru;

class UsernameHelper
{
    /**
     * Generate username untuk Guru.
     *
     * Aturan:
     * 1. Jika NIP ada → username = NIP
     * 2. Jika NIP kosong → strip gelar dari nama lengkap → hapus non-huruf → lowercase
     *    Contoh: "Dr. H. Budi Santoso, S.Pd., M.Pd." → "budisantoso"
     * 3. Jika hasil username sudah ada di DB → append angka (budisantoso2, budisantoso3, ...)
     */
    public static function generateForGuru(string $name, ?string $nip = null, ?string $excludeId = null): string
    {
        if ($nip) {
            return $nip;
        }

        $username = self::nameToUsername($name);

        return self::ensureUnique($username, 'teachers', $excludeId);
    }

    /**
     * Strip gelar akademik/keagamaan Indonesia dari nama lengkap,
     * lalu konversi jadi username (lowercase, tanpa spasi, tanpa simbol).
     */
    public static function nameToUsername(string $name): string
    {
        // Hapus gelar depan (Prof., Dr., Drs., Ir., H., Hj., KH., Ustadz, Ustadzah)
        $name = preg_replace(
            '/\b(Prof|Dr|Drs|Ir|H|Hj|KH|Ustadz|Ustadzah)\.?\s+/iu',
            '',
            $name
        );

        // Hapus gelar belakang: pola setelah koma atau titik akhir
        // Contoh: ", S.Pd., M.Pd." atau "S.T." di akhir nama
        $name = preg_replace(
            '/,?\s*\b[A-Z][A-Za-z\.]{1,10}(\s*,?\s*[A-Z][A-Za-z\.]{1,10})*\s*\.?\s*$/u',
            '',
            $name
        );

        // Hapus semua karakter non-huruf (titik, koma, spasi, angka)
        $name = preg_replace('/[^a-zA-Z]/u', '', $name);

        return strtolower($name);
    }

    /**
     * Pastikan username unik di tabel yang diberikan.
     * Jika sudah ada → append angka kecil (2, 3, 4, ...)
     */
    public static function ensureUnique(string $base, string $table, ?string $excludeId = null): string
    {
        $username  = $base;
        $counter   = 2;
        $query     = \DB::table($table)->where('username', $username);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        while ($query->exists()) {
            $username = $base . $counter;
            $counter++;
            $query = \DB::table($table)->where('username', $username);
            if ($excludeId) {
                $query->where('id', '!=', $excludeId);
            }
        }

        return $username;
    }
}
