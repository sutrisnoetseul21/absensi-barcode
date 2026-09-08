<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class WebPillar extends Model
{
    use HasUuids;

    protected $table = 'web_pillars';
    protected $guarded = [];

    protected $casts = [
        'is_active' => 'boolean',
        'urutan'    => 'integer',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('urutan');
    }

    /**
     * Daftar pilihan tema warna
     */
    public static function colorThemes(): array
    {
        return [
            'blue'    => 'Biru (Teknologi / Modern)',
            'emerald' => 'Hijau Zamrud (Karakter / Religius)',
            'amber'   => 'Kuning Emas (Prestasi / Bakat)',
            'teal'    => 'Tosca (Lingkungan / Asri)',
            'purple'  => 'Ungu (Kreativitas / Inovasi)',
            'rose'    => 'Merah Muda (Kepedulian / Harmoni)',
            'indigo'  => 'Indigo (Edukasi / Global)',
        ];
    }

    /**
     * Konfigurasi CSS berdasarkan color_theme
     */
    public function getThemeStylesAttribute(): array
    {
        $theme = $this->color_theme ?? 'blue';

        $styles = [
            'blue' => [
                'badge_color'  => 'bg-blue-50 text-blue-700 border-blue-200/60',
                'icon_bg'      => 'bg-blue-600 text-white shadow-blue-500/25',
                'hover_border' => 'hover:border-blue-200',
                'gradient'     => 'from-blue-500 to-indigo-500',
                'text_color'   => 'text-blue-600',
            ],
            'emerald' => [
                'badge_color'  => 'bg-emerald-50 text-emerald-700 border-emerald-200/60',
                'icon_bg'      => 'bg-emerald-600 text-white shadow-emerald-500/25',
                'hover_border' => 'hover:border-emerald-200',
                'gradient'     => 'from-emerald-500 to-teal-500',
                'text_color'   => 'text-emerald-600',
            ],
            'amber' => [
                'badge_color'  => 'bg-amber-50 text-amber-700 border-amber-200/60',
                'icon_bg'      => 'bg-amber-500 text-white shadow-amber-500/25',
                'hover_border' => 'hover:border-amber-200',
                'gradient'     => 'from-amber-500 to-orange-500',
                'text_color'   => 'text-amber-600',
            ],
            'teal' => [
                'badge_color'  => 'bg-teal-50 text-teal-700 border-teal-200/60',
                'icon_bg'      => 'bg-teal-600 text-white shadow-teal-500/25',
                'hover_border' => 'hover:border-teal-200',
                'gradient'     => 'from-teal-500 to-emerald-500',
                'text_color'   => 'text-teal-600',
            ],
            'purple' => [
                'badge_color'  => 'bg-purple-50 text-purple-700 border-purple-200/60',
                'icon_bg'      => 'bg-purple-600 text-white shadow-purple-500/25',
                'hover_border' => 'hover:border-purple-200',
                'gradient'     => 'from-purple-500 to-indigo-500',
                'text_color'   => 'text-purple-600',
            ],
            'rose' => [
                'badge_color'  => 'bg-rose-50 text-rose-700 border-rose-200/60',
                'icon_bg'      => 'bg-rose-600 text-white shadow-rose-500/25',
                'hover_border' => 'hover:border-rose-200',
                'gradient'     => 'from-rose-500 to-pink-500',
                'text_color'   => 'text-rose-600',
            ],
            'indigo' => [
                'badge_color'  => 'bg-indigo-50 text-indigo-700 border-indigo-200/60',
                'icon_bg'      => 'bg-indigo-600 text-white shadow-indigo-500/25',
                'hover_border' => 'hover:border-indigo-200',
                'gradient'     => 'from-indigo-500 to-violet-500',
                'text_color'   => 'text-indigo-600',
            ],
        ];

        return $styles[$theme] ?? $styles['blue'];
    }
}
