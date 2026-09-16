<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model pendamping siswa khusus untuk profil CBT (Profile Pattern).
 * 
 * @property string $student_id
 * @property string|null $cbt_password @deprecated Tidak lagi disinkronkan ke ZenCBT. Login ZenCBT menggunakan satu password seragam per-sekolah via CBT_DEFAULT_PASSWORD. Kolom tetap dipertahankan untuk arsip / cetak kartu lama.
 * @property string|null $cbt_sesi
 * @property string $cbt_status
 */
class StudentCbtProfile extends Model
{
    use HasUuids;

    protected $table = 'student_cbt_profiles';

    protected $fillable = [
        'student_id',
        'cbt_password',
        'cbt_sesi',
        'cbt_status',
    ];

    protected $casts = [
        'cbt_password' => 'encrypted',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Siswa::class, 'student_id');
    }
}
