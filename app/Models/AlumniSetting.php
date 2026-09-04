<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AlumniSetting extends Model
{
    protected $fillable = [
        'is_active',
        'show_table',
        'banner_title',
        'banner_text',
        'button_text',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'show_table' => 'boolean',
    ];

    public static function instance(): self
    {
        return static::firstOrCreate(['id' => 1], [
            'is_active' => true,
            'show_table' => true,
            'banner_title' => 'Tracer Study Alumni',
            'banner_text' => 'Mari terus menjalin silaturahmi dan berbagi inspirasi. Data Anda sangat berharga bagi pengembangan kualitas pendidikan di sekolah tercinta kita.',
            'button_text' => 'Daftarkan Data Saya',
        ]);
    }
}
