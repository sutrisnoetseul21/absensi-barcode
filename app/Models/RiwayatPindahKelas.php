<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RiwayatPindahKelas extends Model
{
    use \Illuminate\Database\Eloquent\Concerns\HasUuids;

    protected $table = 'riwayat_pindah_kelas';

    protected $fillable = [
        'enrollment_id',
        'student_id',
        'academic_year_id',
        'from_class_id',
        'to_class_id',
        'reason',
    ];

    public function enrollment()
    {
        return $this->belongsTo(EnrollmentSiswa::class, 'enrollment_id');
    }

    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'student_id');
    }

    public function tahunAjaran()
    {
        return $this->belongsTo(TahunAjaran::class, 'academic_year_id');
    }

    public function kelasSebelumnya()
    {
        return $this->belongsTo(Kelas::class, 'from_class_id');
    }

    public function kelasSesudahnya()
    {
        return $this->belongsTo(Kelas::class, 'to_class_id');
    }
}
