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

        return self::ensureUnique($username, $excludeId);
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

        // Hapus gelar belakang: mulai dari koma pertama hingga akhir string
        // Karena gelar Indonesia biasanya ditulis setelah koma (contoh: "Budi, S.Pd.")
        $name = preg_replace('/,.*$/u', '', $name);

        // Hapus semua karakter non-huruf (titik, koma, spasi, angka)
        $name = preg_replace('/[^a-zA-Z]/u', '', $name);

        // Jika setelah dihapus ternyata kosong, berikan random string agar tidak error null
        if (empty($name)) {
            $name = 'user' . strtolower(\Illuminate\Support\Str::random(5));
        }

        return strtolower($name);
    }

    /**
     * Pastikan username unik di tabel yang diberikan.
     * Jika sudah ada → append angka kecil (2, 3, 4, ...)
     */
    public static function ensureUnique(string $base, ?string $excludeTeacherId = null): string
    {
        $username  = $base;
        $counter   = 2;
        $domain    = config('school.email_domain', 'sekolah.sch.id');
        
        $email = $username . '@' . $domain;
        $query = \DB::table('users')->where('email', $email);

        if ($excludeTeacherId) {
            $query->where('teacher_id', '!=', $excludeTeacherId);
        }

        while ($query->exists()) {
            $username = $base . $counter;
            $counter++;
            
            $email = $username . '@' . $domain;
            $query = \DB::table('users')->where('email', $email);
            
            if ($excludeTeacherId) {
                $query->where('teacher_id', '!=', $excludeTeacherId);
            }
        }

        return $username;
    }
}
