<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeacherJabatan extends Model
{
    protected $table = 'teacher_jabatan';
    protected $guarded = [];

    public function teacher()
    {
        return $this->belongsTo(Guru::class, 'teacher_id');
    }

    public function jabatan()
    {
        return $this->belongsTo(Jabatan::class, 'jabatan_id');
    }
}
