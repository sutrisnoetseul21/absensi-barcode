<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchoolBroadcast extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'school_broadcasts';

    protected $fillable = [
        'title',
        'body',
        'target_group',
        'class_id',
        'class_name',
        'sender_name',
        'total_target_users',
        'total_fcm_sent',
        'status',
    ];

    public function getTargetGroupLabelAttribute(): string
    {
        return match ($this->target_group) {
            'all'   => 'Semua (Siswa & Guru)',
            'siswa' => 'Semua Siswa',
            'guru'  => 'Semua Guru & Staf',
            'kelas' => 'Kelas: ' . ($this->class_name ?: '-'),
            default => ucfirst($this->target_group),
        };
    }
}
