<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Jabatan extends Model
{
    protected $fillable = ['nama_jabatan'];

    public function gurus()
    {
        return $this->belongsToMany(\App\Models\Guru::class, 'teacher_jabatan', 'jabatan_id', 'teacher_id');
    }
}
