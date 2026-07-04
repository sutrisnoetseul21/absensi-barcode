<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasUuids, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_super_admin',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'is_super_admin'    => 'boolean',
        ];
    }

    /**
     * Cek apakah user ini adalah Super Admin.
     * Super Admin punya akses penuh: setting sekolah, tahun ajaran, kenaikan kelas.
     */
    public function isSuperAdmin(): bool
    {
        return (bool) $this->is_super_admin;
    }

    // Absensi yang discan oleh admin ini
    public function absensisScanned(): HasMany
    {
        return $this->hasMany(Presensi::class, 'scanned_by');
    }

    // Absensi manual yang diinput admin ini (polymorphic)
    public function absensisManual()
    {
        return $this->morphMany(Presensi::class, 'manual_input_by');
    }
}
