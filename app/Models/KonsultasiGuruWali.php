<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class KonsultasiGuruWali extends Model
{
    use HasUuids;

    protected $table = 'konsultasi_guru_wali';

    protected $fillable = [
        'student_id',
        'teacher_id',
        'kelompok_id',
        'kategori_pendampingan',
        'topik_konsultasi',
        'detail_permasalahan',
        'mode_konsultasi',
        'usulan_tanggal_waktu',
        'jadwal_pasti',
        'status_pengajuan',
        'tanggapan_guru',
        'alasan_penolakan',
        'jurnal_id',
        'academic_year_id',
        'class_id',
        'student_feedback_rating',
        'student_feedback_emoji',
        'student_feedback_note',
    ];

    protected $casts = [
        'usulan_tanggal_waktu'   => 'datetime',
        'jadwal_pasti'           => 'datetime',
        'student_feedback_emoji' => \App\Enums\StudentFeedbackEmoji::class,
        'status_pengajuan'       => \App\Enums\StatusPengajuan::class,
    ];

    /**
     * Siswa yang mengajukan permohonan konsultasi.
     */
    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class, 'student_id');
    }

    /**
     * Guru Wali yang dituju.
     */
    public function guru(): BelongsTo
    {
        return $this->belongsTo(Guru::class, 'teacher_id');
    }

    /**
     * Alias relasi: teacher -> Guru Wali
     */
    public function teacher(): BelongsTo
    {
        return $this->guru();
    }

    /**
     * Kelompok dampingan tempat siswa terdaftar.
     */
    public function kelompok(): BelongsTo
    {
        return $this->belongsTo(KelompokGuruWali::class, 'kelompok_id');
    }

    /**
     * Jurnal pendampingan hasil konversi dari sesi konsultasi ini.
     */
    public function jurnal(): BelongsTo
    {
        return $this->belongsTo(JurnalGuruWali::class, 'jurnal_id');
    }

    /**
     * Badge karakter yang diberikan dalam konsultasi ini.
     */
    public function badge(): MorphOne
    {
        return $this->morphOne(BadgeKarakterSiswa::class, 'source');
    }

    /**
     * Pesan / Percakapan dalam konsultasi mode pesan portal.
     */
    public function pesan(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PesanKonsultasiGuruWali::class, 'konsultasi_id')->orderBy('created_at', 'asc');
    }
}
