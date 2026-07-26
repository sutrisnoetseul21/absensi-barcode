<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Buku extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'kategori_id',
        'mapel_id',
        'grade_level',
        'judul',
        'penulis',
        'penerbit',
        'tahun_terbit',
        'isbn',
        'lokasi_rak',
    ];

    protected $casts = [
        'tahun_terbit' => 'integer',
        'grade_level' => 'integer',
    ];

    public function kategoriBuku(): BelongsTo
    {
        return $this->belongsTo(KategoriBuku::class, 'kategori_id');
    }

    public function mataPelajaran(): BelongsTo
    {
        return $this->belongsTo(MataPelajaran::class, 'mapel_id');
    }

    public function eksemplarBukus(): HasMany
    {
        return $this->hasMany(EksemplarBuku::class, 'buku_id');
    }
}
