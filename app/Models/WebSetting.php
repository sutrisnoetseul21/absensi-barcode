<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\ResizesImages;

class WebSetting extends Model
{
    use ResizesImages;

    protected $guarded = [];

    /**
     * Singleton pattern untuk mendapatkan atau membuat baris konfigurasi pertama.
     */
    public static function instance()
    {
        return self::firstOrCreate(
            ['id' => 1],
            [
                'stat_tenaga_kependidikan' => 0,
            ]
        );
    }

    protected static function boot()
    {
        parent::boot();

        static::saved(function ($setting) {
            // Cek menggunakan wasChanged karena event saved() dipanggil setelah data di-sync ke database
            if ($setting->wasChanged('hero_image') && $setting->hero_image) {
                $setting->resizeImage('public', 'hero_image', 1920, true, 80); // max 1920px, force JPEG 80
            }

            if ($setting->wasChanged('foto_kepsek') && $setting->foto_kepsek) {
                $setting->resizeImage('public', 'foto_kepsek', 600); // max 600px
            }
        });
    }
}

