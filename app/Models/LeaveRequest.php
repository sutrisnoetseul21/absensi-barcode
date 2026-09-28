<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class LeaveRequest extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'leave_requests';

    protected $fillable = [
        'student_id',
        'academic_year_id',
        'type',
        'duration_days',
        'start_date',
        'end_date',
        'reason',
        'file_path',
        'file_paths',
        'status',
        'approved_by',
        'approved_by_type',
        'approved_at',
    ];

    /**
     * Kolom yang disembunyikan dari serialisasi JSON / toArray().
     * approval_token tidak boleh bocor ke response API, Livewire, atau log.
     */
    protected $hidden = [
        'approval_token',
    ];

    protected $casts = [
        'start_date'       => 'date',
        'end_date'         => 'date',
        'approved_at'      => 'datetime',
        'duration_days'    => 'integer',
        'file_paths'       => 'array',
        'token_used_at'    => 'datetime',
        'token_expires_at' => 'datetime',
        'wa_text_sent_at'  => 'datetime',
        'wa_sent_files'    => 'array',
    ];

    /**
     * Accessor: Gabungkan file_path + file_paths, deduplikasi, cek file benar-benar ada di disk,
     * dan kembalikan metadata lengkap per item untuk dipakai Job & Route media.
     *
     * Return format per item:
     *   [ 'index' => int, 'path' => string, 'name' => string, 'mime' => string, 'mediatype' => 'image'|'document' ]
     *
     * Hanya mendukung: jpg, jpeg, png, webp (→ 'image') dan pdf (→ 'document').
     * Tipe lain di-skip dan dicatat ke log sebagai warning.
     */
    public function getAttachmentsAttribute(): array
    {
        $rawPaths = [];

        if (!empty($this->file_path)) {
            $rawPaths[] = $this->file_path;
        }

        if (is_array($this->file_paths)) {
            foreach ($this->file_paths as $p) {
                if (!empty($p)) {
                    $rawPaths[] = $p;
                }
            }
        }

        // Deduplikasi path
        $rawPaths = array_unique($rawPaths);

        $result = [];
        $index  = 0;

        foreach ($rawPaths as $path) {
            // Skip file yang tidak ada di disk
            if (!Storage::disk('public')->exists($path)) {
                Log::warning("LeaveRequest #{$this->id}: file lampiran tidak ditemukan di storage: {$path}");
                continue;
            }

            $mime      = Storage::disk('public')->mimeType($path) ?: 'application/octet-stream';
            $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

            $mediatype = match ($extension) {
                'jpg', 'jpeg', 'png', 'webp' => 'image',
                'pdf'                        => 'document',
                default                      => null,
            };

            if ($mediatype === null) {
                Log::warning("LeaveRequest #{$this->id}: tipe lampiran tidak didukung untuk WA: {$extension} ({$path})");
                continue;
            }

            $result[] = [
                'index'     => $index,
                'path'      => $path,
                'name'      => basename($path),
                'mime'      => $mime,
                'mediatype' => $mediatype,
            ];

            $index++;
        }

        return $result;
    }

    /**
     * Generate token approval unik (8 karakter Base62) dan simpan ke database.
     * Hanya dieksekusi jika token belum ada (idempoten).
     * Menggunakan forceFill() untuk bypass $fillable (token tidak boleh di $fillable).
     *
     * @throws \RuntimeException jika 5 percobaan generate token gagal karena konflik unik.
     */
    public function generateApprovalToken(): void
    {
        // Idempoten: jangan timpa token yang sudah ada
        if (!empty($this->approval_token)) {
            return;
        }

        $maxAttempts = 5;

        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            $token = Str::random(8);

            try {
                $this->forceFill([
                    'approval_token'    => $token,
                    'token_expires_at'  => now()->addDays(7),
                ])->saveQuietly();

                return; // Berhasil, keluar dari loop
            } catch (UniqueConstraintViolationException) {
                // Token bentrok, coba lagi
                if ($attempt === $maxAttempts) {
                    throw new \RuntimeException(
                        "LeaveRequest #{$this->id}: gagal generate approval_token unik setelah {$maxAttempts} percobaan."
                    );
                }
            }
        }
    }

    /**
     * Cek apakah token masih berlaku untuk dipakai konfirmasi.
     */
    public function isTokenValid(): bool
    {
        return $this->token_used_at === null
            && $this->token_expires_at !== null
            && $this->token_expires_at->isFuture()
            && $this->status === 'pending';
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Siswa::class, 'student_id');
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(TahunAjaran::class, 'academic_year_id');
    }

    public function approvedBy(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, 'approved_by_type', 'approved_by');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(LeaveRequestLog::class, 'leave_request_id')->latest();
    }

    public function recordLog(string $action, ?string $reason = null, array $changes = []): LeaveRequestLog
    {
        return $this->logs()->create([
            'user_id' => auth()->id(),
            'action'  => $action,
            'reason'  => $reason,
            'changes' => $changes,
        ]);
    }
}
