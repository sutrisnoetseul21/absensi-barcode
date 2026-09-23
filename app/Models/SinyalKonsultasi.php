<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;

class SinyalKonsultasi extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'sinyal_konsultasi';

    protected $fillable = [
        'student_id',
        'academic_year_id',
        'class_id',
        'teacher_id',
        'kategori_masalah',
        'tingkat_urgensi',
        'deskripsi_awal',
        'status',
        'catatan_hasil_guru',
        'student_feedback_rating',
        'student_feedback_emoji',
    ];

    protected $casts = [
        'kategori_masalah' => \App\Enums\KategoriMasalah::class,
        'tingkat_urgensi' => \App\Enums\TingkatUrgensi::class,
        'status' => \App\Enums\StatusKonsultasi::class,
        'student_feedback_emoji' => \App\Enums\StudentFeedbackEmoji::class,
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

    public function teacher()
    {
        return $this->belongsTo(Guru::class, 'teacher_id');
    }
}
