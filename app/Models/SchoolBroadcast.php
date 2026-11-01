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
        'active_hours',
        'expires_at',
        'total_target_users',
        'total_fcm_sent',
        'status',
    ];

    protected $casts = [
        'expires_at'   => 'datetime',
        'active_hours' => 'integer',
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

    public function getIsExpiredAttribute(): bool
    {
        return $this->expires_at && now()->greaterThan($this->expires_at);
    }

    public function getSisaWaktuAttribute(): string
    {
        if ($this->status === 'cancelled') {
            return 'Dihentikan';
        }

        if ($this->is_expired) {
            return 'Kadaluarsa (Selesai)';
        }

        if (!$this->expires_at) {
            return '-';
        }

        $diff = now()->diff($this->expires_at);
        $parts = [];
        if ($diff->d > 0) {
            $parts[] = "{$diff->d} hr";
        }
        if ($diff->h > 0) {
            $parts[] = "{$diff->h} jam";
        }
        $parts[] = "{$diff->i} mnt";

        return 'Sisa ' . implode(' ', $parts);
    }

    public function getStatusLabelAttribute(): string
    {
        if ($this->status === 'cancelled') {
            return 'Dihentikan';
        }

        if ($this->is_expired) {
            return 'Kadaluarsa';
        }

        return match ($this->status) {
            'sent'       => 'Aktif',
            'processing' => 'Diproses',
            'failed'     => 'Gagal',
            default      => ucfirst($this->status),
        };
    }

    public function getStatusColorAttribute(): string
    {
        if ($this->status === 'cancelled') {
            return 'gray';
        }

        if ($this->is_expired) {
            return 'gray';
        }

        return match ($this->status) {
            'sent'       => 'success',
            'processing' => 'warning',
            'failed'     => 'danger',
            default      => 'gray',
        };
    }
}
