<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PengaduanSetting extends Model
{
    protected $guarded = [];

    public static function instance(): self
    {
        return static::firstOrCreate(
            ['id' => 1],
            [
                'module_name' => 'Pengaduan',
                'banner_title' => 'Layanan Aspirasi & Pengaduan',
                'banner_text' => 'Punya saran, kritik, atau laporan? Sampaikan kepada kami dengan mudah, cepat, dan aman.',
                'is_active' => true,
            ]
        );
    }
}
