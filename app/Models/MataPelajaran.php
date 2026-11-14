<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MataPelajaran extends Model
{
    protected $fillable = [
        'nama_mapel',
        'kode_mapel',
        'kategori',
        'kkm_default',
    ];

    protected $casts = [
        'kkm_default' => 'decimal:2',
    ];

    public function getNamaAttribute(): ?string
    {
        return $this->nama_mapel;
    }

    public function getKodeAttribute(): ?string
    {
        return $this->kode_mapel;
    }

    public function pengajarans(): HasMany
    {
        return $this->hasMany(Pengajaran::class, 'mata_pelajaran_id');
    }

    public function ujianAkademiks(): HasMany
    {
        return $this->hasMany(UjianAkademik::class, 'mata_pelajaran_id');
    }

    /**
     * Daftar mata pelajaran standar berdasarkan jenjang sekolah.
     * Untuk SMK hanya menyertakan mapel umum/normatif-adaptif; mapel kejuruan diinput manual.
     *
     * @return array<int, array{nama_mapel: string, kode_mapel: string, kategori: string}>
     */
    public static function standardByJenjang(?string $level): array
    {
        return match (strtoupper((string) $level)) {
            'SD' => [
                ['nama_mapel' => 'Pendidikan Agama dan Budi Pekerti', 'kode_mapel' => 'PAI', 'kategori' => 'Umum'],
                ['nama_mapel' => 'Pendidikan Pancasila', 'kode_mapel' => 'PPKN', 'kategori' => 'Umum'],
                ['nama_mapel' => 'Bahasa Indonesia', 'kode_mapel' => 'BIND', 'kategori' => 'Umum'],
                ['nama_mapel' => 'Matematika', 'kode_mapel' => 'MTK', 'kategori' => 'Umum'],
                ['nama_mapel' => 'Ilmu Pengetahuan Alam dan Sosial (IPAS)', 'kode_mapel' => 'IPAS', 'kategori' => 'Umum'],
                ['nama_mapel' => 'Pendidikan Jasmani, Olahraga, dan Kesehatan', 'kode_mapel' => 'PJOK', 'kategori' => 'Umum'],
                ['nama_mapel' => 'Seni Budaya', 'kode_mapel' => 'SBK', 'kategori' => 'Umum'],
                ['nama_mapel' => 'Bahasa Inggris', 'kode_mapel' => 'BING', 'kategori' => 'Umum'],
            ],
            'SMA' => [
                ['nama_mapel' => 'Pendidikan Agama dan Budi Pekerti', 'kode_mapel' => 'PAI', 'kategori' => 'Umum'],
                ['nama_mapel' => 'Pendidikan Pancasila', 'kode_mapel' => 'PPKN', 'kategori' => 'Umum'],
                ['nama_mapel' => 'Bahasa Indonesia', 'kode_mapel' => 'BIND', 'kategori' => 'Umum'],
                ['nama_mapel' => 'Matematika', 'kode_mapel' => 'MTK', 'kategori' => 'Umum'],
                ['nama_mapel' => 'Bahasa Inggris', 'kode_mapel' => 'BING', 'kategori' => 'Umum'],
                ['nama_mapel' => 'Pendidikan Jasmani, Olahraga, dan Kesehatan', 'kode_mapel' => 'PJOK', 'kategori' => 'Umum'],
                ['nama_mapel' => 'Sejarah', 'kode_mapel' => 'SEJ', 'kategori' => 'Umum'],
                ['nama_mapel' => 'Seni Budaya', 'kode_mapel' => 'SBK', 'kategori' => 'Umum'],
                ['nama_mapel' => 'Informatika', 'kode_mapel' => 'INF', 'kategori' => 'Umum'],
                ['nama_mapel' => 'Bimbingan dan Konseling (BK)', 'kode_mapel' => 'BK', 'kategori' => 'Umum'],
                ['nama_mapel' => 'Fisika', 'kode_mapel' => 'FIS', 'kategori' => 'Pilihan'],
                ['nama_mapel' => 'Kimia', 'kode_mapel' => 'KIM', 'kategori' => 'Pilihan'],
                ['nama_mapel' => 'Biologi', 'kode_mapel' => 'BIO', 'kategori' => 'Pilihan'],
                ['nama_mapel' => 'Ekonomi', 'kode_mapel' => 'EKO', 'kategori' => 'Pilihan'],
                ['nama_mapel' => 'Sosiologi', 'kode_mapel' => 'SOS', 'kategori' => 'Pilihan'],
                ['nama_mapel' => 'Geografi', 'kode_mapel' => 'GEO', 'kategori' => 'Pilihan'],
            ],
            'SMK' => [
                ['nama_mapel' => 'Pendidikan Agama dan Budi Pekerti', 'kode_mapel' => 'PAI', 'kategori' => 'Umum'],
                ['nama_mapel' => 'Pendidikan Pancasila', 'kode_mapel' => 'PPKN', 'kategori' => 'Umum'],
                ['nama_mapel' => 'Bahasa Indonesia', 'kode_mapel' => 'BIND', 'kategori' => 'Umum'],
                ['nama_mapel' => 'Matematika', 'kode_mapel' => 'MTK', 'kategori' => 'Umum'],
                ['nama_mapel' => 'Bahasa Inggris', 'kode_mapel' => 'BING', 'kategori' => 'Umum'],
                ['nama_mapel' => 'Pendidikan Jasmani, Olahraga, dan Kesehatan', 'kode_mapel' => 'PJOK', 'kategori' => 'Umum'],
                ['nama_mapel' => 'Sejarah', 'kode_mapel' => 'SEJ', 'kategori' => 'Umum'],
                ['nama_mapel' => 'Seni Budaya', 'kode_mapel' => 'SBK', 'kategori' => 'Umum'],
                ['nama_mapel' => 'Informatika', 'kode_mapel' => 'INF', 'kategori' => 'Umum'],
                ['nama_mapel' => 'Projek IPAS', 'kode_mapel' => 'IPAS', 'kategori' => 'Umum'],
                ['nama_mapel' => 'Bimbingan dan Konseling (BK)', 'kode_mapel' => 'BK', 'kategori' => 'Umum'],
            ],
            default => [ // SMP
                ['nama_mapel' => 'Pendidikan Agama dan Budi Pekerti', 'kode_mapel' => 'PAI', 'kategori' => 'Umum'],
                ['nama_mapel' => 'Pendidikan Pancasila', 'kode_mapel' => 'PPKN', 'kategori' => 'Umum'],
                ['nama_mapel' => 'Bahasa Indonesia', 'kode_mapel' => 'BIND', 'kategori' => 'Umum'],
                ['nama_mapel' => 'Matematika', 'kode_mapel' => 'MTK', 'kategori' => 'Umum'],
                ['nama_mapel' => 'Ilmu Pengetahuan Alam (IPA)', 'kode_mapel' => 'IPA', 'kategori' => 'Umum'],
                ['nama_mapel' => 'Ilmu Pengetahuan Sosial (IPS)', 'kode_mapel' => 'IPS', 'kategori' => 'Umum'],
                ['nama_mapel' => 'Bahasa Inggris', 'kode_mapel' => 'BING', 'kategori' => 'Umum'],
                ['nama_mapel' => 'Pendidikan Jasmani, Olahraga, dan Kesehatan', 'kode_mapel' => 'PJOK', 'kategori' => 'Umum'],
                ['nama_mapel' => 'Informatika', 'kode_mapel' => 'INF', 'kategori' => 'Umum'],
                ['nama_mapel' => 'Seni Budaya / Prakarya', 'kode_mapel' => 'SBK', 'kategori' => 'Umum'],
                ['nama_mapel' => 'Bimbingan dan Konseling (BK)', 'kode_mapel' => 'BK', 'kategori' => 'Umum'],
            ],
        };
    }
}
