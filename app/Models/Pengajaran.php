<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pengajaran extends Model
{
    use HasUuids;

    protected $fillable = [
        'class_academic_year_id',
        'teacher_id',
        'mata_pelajaran_id',
    ];

    public function kelasAjaran(): BelongsTo
    {
        return $this->belongsTo(KelasAjaran::class, 'class_academic_year_id');
    }

    public function guru(): BelongsTo
    {
        return $this->belongsTo(Guru::class, 'teacher_id');
    }

    public function mataPelajaran(): BelongsTo
    {
        return $this->belongsTo(MataPelajaran::class, 'mata_pelajaran_id');
    }

    /**
     * Sinkronkan penugasan mengajar seorang guru pada satu mata pelajaran untuk daftar kelas terpilih.
     */
    public static function syncClassesForTeacher(string $teacherId, int $mapelId, array $selectedClassIds, string $academicYearId): void
    {
        $selectedClassIds = array_values(array_filter($selectedClassIds));

        Guru::find($teacherId)?->mataPelajarans()->syncWithoutDetaching([$mapelId]);

        static::where('teacher_id', $teacherId)
            ->where('mata_pelajaran_id', $mapelId)
            ->whereHas('kelasAjaran', function ($q) use ($academicYearId, $selectedClassIds) {
                $q->where('academic_year_id', $academicYearId);
                if (!empty($selectedClassIds)) {
                    $q->whereNotIn('class_id', $selectedClassIds);
                }
            })
            ->delete();

        foreach ($selectedClassIds as $classId) {
            $kelasAjaran = KelasAjaran::firstOrCreate(
                ['class_id' => $classId, 'academic_year_id' => $academicYearId],
                ['teacher_id' => null]
            );

            static::updateOrCreate(
                [
                    'class_academic_year_id' => $kelasAjaran->id,
                    'mata_pelajaran_id'      => $mapelId,
                ],
                [
                    'teacher_id' => $teacherId,
                ]
            );
        }
    }

    /**
     * Opsi Select mata pelajaran yang memprioritaskan bidang ajar guru tertentu.
     *
     * @return array{options: array, defaultId: ?int}
     */
    public static function mapelOptionsForTeacher(?Guru $guru): array
    {
        $preferredIds = $guru ? $guru->mataPelajarans()->pluck('mata_pelajarans.id')->toArray() : [];
        $preferred = MataPelajaran::whereIn('id', $preferredIds)->orderBy('nama_mapel')->pluck('nama_mapel', 'id')->toArray();
        $others = MataPelajaran::whereNotIn('id', $preferredIds)->orderBy('nama_mapel')->pluck('nama_mapel', 'id')->toArray();

        $options = [];
        if (!empty($preferred)) {
            $options['Mapel Diampu Guru Ini'] = $preferred;
        }
        if (!empty($others)) {
            $options[!empty($preferred) ? 'Mata Pelajaran Lainnya' : 'Daftar Mata Pelajaran'] = $others;
        }

        return [
            'options'   => $options,
            'defaultId' => $preferredIds[0] ?? null,
        ];
    }

    /**
     * Daftar ID kelas yang diajar guru pada mapel tertentu di tahun ajaran aktif.
     */
    public static function assignedClassIdsForTeacherMapel(string $teacherId, int $mapelId, string $academicYearId): array
    {
        return static::where('teacher_id', $teacherId)
            ->where('mata_pelajaran_id', $mapelId)
            ->whereHas('kelasAjaran', fn ($q) => $q->where('academic_year_id', $academicYearId))
            ->with('kelasAjaran')
            ->get()
            ->pluck('kelasAjaran.class_id')
            ->filter()
            ->values()
            ->toArray();
    }
}
