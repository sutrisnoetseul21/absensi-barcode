<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JurnalPembelajaran extends Model
{
    use HasUuids;

    protected $table = 'jurnal_pembelajarans';

    protected $fillable = [
        'academic_year_id',
        'teacher_id',
        'class_id',
        'mata_pelajaran_id',
        'tanggal',
        'jam_mulai',
        'durasi_jp',
        'jam_selesai',
        'jam_ke_label',
        'materi',
        'uraian_materi',
        'refleksi',
        'total_siswa',
        'total_hadir',
        'total_tidak_mengikuti',
        'created_by',
    ];

    protected $casts = [
        'tanggal'              => 'date',
        'jam_mulai'            => 'integer',
        'durasi_jp'            => 'integer',
        'jam_selesai'          => 'integer',
        'total_siswa'          => 'integer',
        'total_hadir'          => 'integer',
        'total_tidak_mengikuti'=> 'integer',
    ];

    /**
     * Relasi ke Guru Pengampu
     */
    public function guru(): BelongsTo
    {
        return $this->belongsTo(Guru::class, 'teacher_id');
    }

    public function teacher(): BelongsTo
    {
        return $this->guru();
    }

    /**
     * Relasi ke Kelas Rombel
     */
    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class, 'class_id');
    }

    /**
     * Relasi ke Mata Pelajaran
     */
    public function mataPelajaran(): BelongsTo
    {
        return $this->belongsTo(MataPelajaran::class, 'mata_pelajaran_id');
    }

    /**
     * Relasi ke Tahun Ajaran
     */
    public function tahunAjaran(): BelongsTo
    {
        return $this->belongsTo(TahunAjaran::class, 'academic_year_id');
    }

    /**
     * Relasi ke User pembuat
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Relasi ke Detail Presensi Siswa pada sesi ini
     */
    public function presensis(): HasMany
    {
        return $this->hasMany(JurnalPembelajaranPresensi::class, 'jurnal_pembelajaran_id');
    }

    /**
     * Hitung persentase kehadiran siswa
     */
    public function getPersentaseKehadiranAttribute(): float
    {
        if ($this->total_siswa <= 0) {
            return 0.0;
        }

        return round(($this->total_hadir / $this->total_siswa) * 100, 1);
    }
}
