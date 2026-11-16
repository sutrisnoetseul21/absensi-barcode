<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class EkstrakurikulerPertemuan extends Model
{
    use HasUuids;

    protected $table = 'ekstrakurikuler_pertemuans';

    protected $fillable = [
        'ekstrakurikuler_id',
        'pertemuan_ke',
        'tanggal',
        'judul_kegiatan',
        'catatan_pembina',
        'created_by_teacher_id',
        'created_by_user_id',
        'status_sesi',
    ];

    protected $casts = [
        'pertemuan_ke' => 'integer',
        'tanggal'      => 'date',
    ];

    public function ekstrakurikuler(): BelongsTo
    {
        return $this->belongsTo(Ekstrakurikuler::class, 'ekstrakurikuler_id');
    }

    public function presensis(): HasMany
    {
        return $this->hasMany(EkstrakurikulerPresensi::class, 'pertemuan_id');
    }

    public function creatorTeacher(): BelongsTo
    {
        return $this->belongsTo(Guru::class, 'created_by_teacher_id');
    }

    public function creatorUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    /**
     * Ambil sesi pertemuan pada tanggal tertentu (default hari ini WIB), atau buat otomatis bila belum ada.
     */
    public static function findOrCreateForDate(
        Ekstrakurikuler $ekskul,
        ?string $tanggal = null,
        ?User $actor = null,
        ?string $judulKegiatan = null
    ): self {
        $date = $tanggal ?: Carbon::now('Asia/Jakarta')->toDateString();

        return DB::transaction(function () use ($ekskul, $date, $actor, $judulKegiatan) {
            $existing = static::where('ekstrakurikuler_id', $ekskul->id)
                ->whereDate('tanggal', $date)
                ->lockForUpdate()
                ->first();

            if ($existing) {
                if (filled($judulKegiatan) && blank($existing->judul_kegiatan)) {
                    $existing->update(['judul_kegiatan' => $judulKegiatan]);
                }
                return $existing;
            }

            $nextNumber = ((int) static::where('ekstrakurikuler_id', $ekskul->id)->max('pertemuan_ke')) + 1;

            return static::create([
                'ekstrakurikuler_id'    => $ekskul->id,
                'pertemuan_ke'          => $nextNumber,
                'tanggal'               => $date,
                'judul_kegiatan'        => filled($judulKegiatan) ? $judulKegiatan : null,
                'created_by_teacher_id' => $actor?->teacher?->id,
                'created_by_user_id'    => $actor?->id,
                'status_sesi'           => 'aktif',
            ]);
        });
    }
}

