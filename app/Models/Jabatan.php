<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Jabatan extends Model
{
    protected $fillable = ['nama_jabatan'];

    /**
     * Jabatan yang direferensikan langsung oleh logic notifikasi WA, SPIKAP, dan SI-WALI.
     */
    public const SYSTEM_CORE_NAMES = [
        'Kepala Sekolah',
        'Guru BK',
        'Waka Kesiswaan',
        'Waka Kurikulum',
        'Waka Sarpras',
        'Waka Humas',
    ];

    public function gurus()
    {
        return $this->belongsToMany(\App\Models\Guru::class, 'teacher_jabatan', 'jabatan_id', 'teacher_id');
    }

    /**
     * Daftar jabatan wajib agar modul inti (Kepsek/WA/SPIKAP) berjalan normal per jenjang.
     */
    public static function wajibByJenjang(?string $level): array
    {
        return match (strtoupper((string) $level)) {
            'SD'    => ['Kepala Sekolah'],
            default => ['Kepala Sekolah', 'Guru BK'],
        };
    }

    /**
     * Daftar jabatan standar yang disarankan untuk disinkronkan per jenjang.
     */
    public static function standarByJenjang(?string $level): array
    {
        return match (strtoupper((string) $level)) {
            'SD' => [
                'Kepala Sekolah',
                'Guru Kelas',
                'Guru PJOK',
                'Guru Agama',
                'Operator',
                'Tata Usaha',
                'Pustakawan',
            ],
            'SMK' => [
                'Kepala Sekolah',
                'Waka Kurikulum',
                'Waka Kesiswaan',
                'Waka Sarpras',
                'Waka Humas',
                'Guru BK',
                'Kepala Program Keahlian',
                'Kepala Bengkel',
                'Koordinator BKK',
                'Koordinator PKL',
                'Operator',
                'Tata Usaha',
                'Pustakawan',
            ],
            default => [
                'Kepala Sekolah',
                'Waka Kurikulum',
                'Waka Kesiswaan',
                'Waka Sarpras',
                'Waka Humas',
                'Guru BK',
                'Operator',
                'Tata Usaha',
                'Pustakawan',
                'Pembina Ekstrakurikuler',
                'Kepala Lab',
            ],
        };
    }
}
