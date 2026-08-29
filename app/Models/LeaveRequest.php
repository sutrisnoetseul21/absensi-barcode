<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

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

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'approved_at' => 'datetime',
        'duration_days' => 'integer',
        'file_paths' => 'array',
    ];

    public function getAttachmentsAttribute(): array
    {
        $attachments = [];
        if (!empty($this->file_path)) {
            $attachments[] = $this->file_path;
        }
        if (is_array($this->file_paths)) {
            $attachments = array_merge($attachments, $this->file_paths);
        }
        return array_unique($attachments);
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
