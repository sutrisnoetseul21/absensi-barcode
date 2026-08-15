<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeacherClassAccess extends Model
{
    use \Illuminate\Database\Eloquent\Concerns\HasUuids;

    protected $table = 'teacher_class_accesses';

    protected $fillable = [
        'teacher_id',
        'class_id',
        'academic_year_id',
    ];

    public function teacher()
    {
        return $this->belongsTo(Guru::class, 'teacher_id');
    }

    public function kelas()
    {
        return $this->belongsTo(Kelas::class, 'class_id');
    }

    public function tahunAjaran()
    {
        return $this->belongsTo(TahunAjaran::class, 'academic_year_id');
    }
}
