<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Guru;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use BezhanSalleh\FilamentShield\Traits\HasPanelShield;

class User extends Authenticatable implements FilamentUser
{
    use HasFactory, HasUuids, Notifiable, HasRoles, HasPanelShield;

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
        'teacher_id',
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
     * Otorisasi akses per Panel berdasarkan Role.
     *
     * Pemetaan Panel ID → Role yang diizinkan:
     *   admin          → super_admin (dihandle oleh HasPanelShield)
     *   admin-master   → admin_master atau super_admin
     *   admin-akademik → admin_akademik atau super_admin
     *   admin-presensi → admin_presensi atau super_admin
     */
    public function canAccessPanel(Panel $panel): bool
    {
        // Super Admin (role Spatie) boleh masuk ke semua panel
        $superAdminRole = config('filament-shield.super_admin.name', 'super_admin');
        if ($this->hasRole($superAdminRole)) {
            return true;
        }

        // Fallback: is_super_admin kolom lama
        if ($this->isSuperAdmin()) {
            return true;
        }

        // Pemetaan Panel ID ke prefix Role yang diperlukan
        $panelRolePrefixes = [
            'admin-master'   => 'admin_master',
            'admin-akademik' => 'admin_akademik',
            'admin-presensi' => 'admin_presensi',
        ];

        $panelId = $panel->getId();
        
        // Panel utama 'admin' (Super Admin)
        if ($panelId === 'admin') {
            return false; // Hanya lolos jika super_admin (sudah di-check di atas)
        }

        if (isset($panelRolePrefixes[$panelId])) {
            $prefix = $panelRolePrefixes[$panelId];
            // Cek apakah user punya role yang berawalan dengan prefix tersebut
            return $this->roles->contains(function ($role) use ($prefix) {
                return str_starts_with($role->name, $prefix);
            });
        }

        return false;
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

    // Relasi ke Guru (jika admin ini adalah guru)
    public function guru()
    {
        return $this->belongsTo(Guru::class, 'teacher_id');
    }
}
