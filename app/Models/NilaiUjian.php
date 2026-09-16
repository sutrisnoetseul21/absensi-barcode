<?php

namespace App\Models;

use App\Exceptions\InvalidNilaiUjianDataException;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class NilaiUjian extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'nilai_ujians';

    protected $fillable = [
        'ujian_akademik_id',
        'student_id',
        'class_id',
        'academic_year_id',
        'semester',
        'nilai_akhir',
        'score_irt',
        'is_tuntas',
        'jumlah_benar',
        'jumlah_salah',
        'jumlah_kosong',
        'violation_count',
        'status_kehadiran',
        'started_at',
        'submitted_at',
        'synced_at',
        'catatan',
    ];

    protected $casts = [
        'nilai_akhir'      => 'decimal:2',
        'is_tuntas'        => 'boolean',
        'jumlah_benar'     => 'integer',
        'jumlah_salah'     => 'integer',
        'jumlah_kosong'    => 'integer',
        'violation_count'  => 'integer',
        'started_at'       => 'datetime',
        'submitted_at'     => 'datetime',
        'synced_at'        => 'datetime',
    ];

    protected static function booted(): void
    {
        // Validasi integritas data & auto-kalkulasi status ketuntasan KKM
        static::saving(function (self $model) {
            $model->loadMissing('ujianAkademik');

            if ($model->ujianAkademik) {
                $parentYearId = (string) $model->ujianAkademik->academic_year_id;
                $parentSemester = (string) $model->ujianAkademik->semester;

                // 1. Validasi & Auto-isi academic_year_id
                if (empty($model->academic_year_id)) {
                    $model->academic_year_id = $parentYearId;
                } elseif ((string) $model->academic_year_id !== $parentYearId) {
                    throw InvalidNilaiUjianDataException::academicYearMismatch(
                        $parentYearId,
                        (string) $model->academic_year_id
                    );
                }

                // 2. Validasi & Auto-isi semester
                if (empty($model->semester)) {
                    $model->semester = $parentSemester;
                } elseif ((string) $model->semester !== $parentSemester) {
                    throw InvalidNilaiUjianDataException::semesterMismatch(
                        $parentSemester,
                        (string) $model->semester
                    );
                }

                // 3. Auto-kalkulasi ketuntasan KKM
                $kkm = (float) $model->ujianAkademik->kkm;
                $model->is_tuntas = ($model->nilai_akhir !== null && (float) $model->nilai_akhir >= $kkm);
            }
        });
    }

    public function ujianAkademik(): BelongsTo
    {
        return $this->belongsTo(UjianAkademik::class, 'ujian_akademik_id');
    }

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class, 'student_id');
    }

    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class, 'class_id');
    }

    public function tahunAjaran(): BelongsTo
    {
        return $this->belongsTo(TahunAjaran::class, 'academic_year_id');
    }
}
