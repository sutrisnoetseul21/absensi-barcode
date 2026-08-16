<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveRequestLog extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'leave_request_logs';

    protected $fillable = [
        'leave_request_id',
        'user_id',
        'action',
        'reason',
        'changes',
    ];

    protected $casts = [
        'changes' => 'array',
    ];

    public function leaveRequest(): BelongsTo
    {
        return $this->belongsTo(LeaveRequest::class, 'leave_request_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
