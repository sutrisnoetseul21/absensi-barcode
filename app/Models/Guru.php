<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Guru extends Authenticatable
{
    use HasUuids, SoftDeletes;

    protected $table = 'teachers';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'name',
        'nip',
        'username',
        'password',
        'must_change_password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'must_change_password' => 'boolean',
        'password' => 'hashed',
    ];

    // Kelas yang diampu (bisa > 1 kelas per tahun ajaran)
    public function kelasAjarans(): HasMany
    {
        return $this->hasMany(KelasAjaran::class, 'teacher_id');
    }

    // Absensi manual yang diinput wali kelas ini (polymorphic)
    public function absensisManual()
    {
        return $this->morphMany(Presensi::class, 'manual_input_by');
    }
}
