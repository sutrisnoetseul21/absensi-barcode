<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;

class BadgeKarakterSiswa extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'badge_karakter_siswa';

    protected $fillable = [
        'student_id',
        'teacher_id',
        'academic_year_id',
        'class_id',
        'nama_badge',
        'catatan_apresiasi',
        'source_type',
        'source_id',
    ];

    public function student()
    {
        return $this->belongsTo(Siswa::class, 'student_id');
    }

    public function teacher()
    {
        return $this->belongsTo(Guru::class, 'teacher_id');
    }

    public function academicYear()
    {
        return $this->belongsTo(TahunAjaran::class, 'academic_year_id');
    }

    public function kelas()
    {
        return $this->belongsTo(Kelas::class, 'class_id');
    }

    public function source()
    {
        return $this->morphTo();
    }
}
