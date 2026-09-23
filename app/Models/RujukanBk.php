<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;

class RujukanBk extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'rujukan_bk';

    protected $fillable = [
        'student_id',
        'academic_year_id',
        'class_id',
        'guru_wali_id',
        'guru_bk_id',
        'alasan_rujukan',
        'status',
        'hasil_penanganan_bk',
        'source_type',
        'source_id',
    ];

    protected $casts = [
        'status' => \App\Enums\StatusRujukan::class,
    ];

    public function student()
    {
        return $this->belongsTo(Siswa::class, 'student_id');
    }

    public function academicYear()
    {
        return $this->belongsTo(TahunAjaran::class, 'academic_year_id');
    }

    public function kelas()
    {
        return $this->belongsTo(Kelas::class, 'class_id');
    }

    public function guruWali()
    {
        return $this->belongsTo(Guru::class, 'guru_wali_id');
    }

    public function guruBk()
    {
        return $this->belongsTo(Guru::class, 'guru_bk_id');
    }

    public function source()
    {
        return $this->morphTo();
    }
}
