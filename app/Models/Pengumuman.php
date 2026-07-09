<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class Pengumuman extends Model
{
    use HasUuids;

    protected $table = 'pengumuman';

    protected $fillable = [
        'judul',
        'isi',
        'tipe',
        'aktif',
        'tanggal_mulai',
        'tanggal_selesai',
        'urutan',
    ];

    protected $casts = [
        'aktif'           => 'boolean',
        'tanggal_mulai'   => 'date',
        'tanggal_selesai' => 'date',
        'urutan'          => 'integer',
    ];

    /**
     * Scope: hanya pengumuman yang aktif dan dalam rentang tanggal (atau tanpa rentang).
     */
    public function scopeAktifSekarang(Builder $query): Builder
    {
        $today = Carbon::today()->toDateString();

        return $query->where('aktif', true)
            ->where(function ($q) use ($today) {
                $q->whereNull('tanggal_mulai')
                  ->orWhere('tanggal_mulai', '<=', $today);
            })
            ->where(function ($q) use ($today) {
                $q->whereNull('tanggal_selesai')
                  ->orWhere('tanggal_selesai', '>=', $today);
            });
    }
}
