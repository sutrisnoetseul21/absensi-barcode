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

use Illuminate\Database\Eloquent\Casts\Attribute;

class User extends Authenticatable implements FilamentUser
{
    use HasFactory, HasUuids, Notifiable, HasRoles;

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
        'no_hp',
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
     *   admin-akademik → admin_akademik atau super_admin
     *   admin-presensi → admin_presensi atau super_admin
     */
    public function canAccessPanel(Panel $panel): bool
    {
        // Tolak akses jika user adalah siswa (memiliki relasi student)
        if ($this->student()->exists()) {
            return false;
        }

        // Super Admin (role Spatie) boleh masuk ke semua panel
        $superAdminRole = config('filament-shield.super_admin.name', 'super_admin');
        if ($this->hasRole($superAdminRole)) {
            return true;
        }

        // Fallback: is_super_admin kolom lama
        if ($this->isSuperAdmin()) {
            return true;
        }

        $panelRolePrefixes = [
            'admin-akademik' => 'admin_akademik',
            'admin-presensi' => 'admin_presensi',
        ];

        $panelId = $panel->getId();
        
        // Panel utama 'admin' (Bisa diakses oleh semua jenis admin)
        if ($panelId === 'admin') {
            return $this->roles->contains(function ($role) {
                return str_starts_with($role->name, 'admin_');
            });
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
     * Super Admin punya akses penuh: setting sekolah, tahun ajaran, kenaikan kelas, dan seluruh modul.
     */
    public function isSuperAdmin(): bool
    {
        return (bool) $this->is_super_admin || $this->hasRole('super_admin');
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

    // Relasi Single-Auth ke tabel teachers
    public function teacher(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(\App\Models\Guru::class, 'user_id');
    }

    // Relasi Single-Auth ke tabel students
    public function student(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(\App\Models\Siswa::class, 'user_id');
    }

    /**
     * Nama Tampilan Dinamis berdasarkan Relasi Guru / Siswa / User Name
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->teacher?->nama 
            ?? $this->guru?->name 
            ?? $this->student?->nama 
            ?? $this->name;
    }

    /**
     * Badge Role/Hak Akses Utama Dinamis
     */
    public function getRoleBadgeAttribute(): string
    {
        if ($this->isSuperAdmin() || $this->hasRole('super_admin')) {
            return 'Super Admin';
        }

        if ($this->roles->contains(fn($r) => str_starts_with($r->name, 'admin_akademik'))) {
            return 'Admin Akademik';
        }

        if ($this->roles->contains(fn($r) => str_starts_with($r->name, 'admin_presensi'))) {
            return 'Admin Presensi';
        }

        if ($this->roles->contains(fn($r) => str_contains($r->name, 'admin_perpustakaan'))) {
            return 'Admin Perpus';
        }

        if ($this->hasRole('wali_kelas') || $this->hasRole('guru')) {
            return 'Wali Kelas';
        }

        if ($this->hasRole('petugas_perpustakaan')) {
            return 'Petugas Perpus';
        }

        if ($this->hasRole('petugas_presensi')) {
            return 'Petugas Presensi';
        }

        if ($this->hasRole('siswa') || $this->student !== null) {
            return 'Siswa';
        }

        $firstRole = $this->getRoleNames()->first();
        return $firstRole ? ucwords(str_replace('_', ' ', $firstRole)) : 'Pengguna';
    }

    /**
     * Mendapatkan daftar portal/panel yang diizinkan untuk diakses user ini secara dinamis.
     */
    public function getAccessiblePortals(): array
    {
        $portals = [];

        $isSuper = $this->isSuperAdmin() || $this->hasRole('super_admin');

        // 1. Panel Admin Utama (/admin)
        if ($isSuper) {
            $portals[] = [
                'name' => 'Panel Admin Utama',
                'url'  => url('/admin'),
                'desc' => 'Pengaturan sistem & manajemen pengguna',
                'badge' => 'Super Admin',
                'bg'   => 'bg-purple-50 text-purple-700 border-purple-200 hover:bg-purple-100',
            ];
        }

        // 2. Panel Admin Akademik (/admin-akademik)
        if ($isSuper || $this->roles->contains(fn($r) => str_starts_with($r->name, 'admin_akademik'))) {
            $portals[] = [
                'name' => 'Panel Admin Akademik',
                'url'  => url('/admin-akademik'),
                'desc' => 'Kelola siswa, kelas, guru & tahun ajaran',
                'badge' => 'Admin Akademik',
                'bg'   => 'bg-blue-50 text-blue-700 border-blue-200 hover:bg-blue-100',
            ];
        }

        // 3. Panel Admin Presensi (/admin-presensi)
        if ($isSuper || $this->roles->contains(fn($r) => str_starts_with($r->name, 'admin_presensi'))) {
            $portals[] = [
                'name' => 'Panel Admin Presensi',
                'url'  => url('/admin-presensi'),
                'desc' => 'Kelola laporan & rekapitulasi kehadiran',
                'badge' => 'Admin Presensi',
                'bg'   => 'bg-emerald-50 text-emerald-700 border-emerald-200 hover:bg-emerald-100',
            ];
        }

        // 4. Panel Admin Perpustakaan (/admin-perpustakaan)
        if ($isSuper || $this->roles->contains(fn($r) => str_contains($r->name, 'perpustakaan'))) {
            $portals[] = [
                'name' => 'Panel Admin Perpustakaan',
                'url'  => url('/admin-perpustakaan'),
                'desc' => 'Kelola katalog buku & sirkulasi perpus',
                'badge' => 'Admin Perpus',
                'bg'   => 'bg-amber-50 text-amber-700 border-amber-200 hover:bg-amber-100',
            ];
        }

        // 5. Portal Guru / Wali Kelas (/portal-guru)
        if ($isSuper || $this->hasRole('wali_kelas') || $this->hasRole('guru') || $this->teacher_id !== null || $this->teacher !== null) {
            $portals[] = [
                'name' => 'Portal Guru & Wali Kelas',
                'url'  => url('/portal-guru'),
                'desc' => 'Absensi kelas binaan & kegiatan siswa',
                'badge' => 'Wali Kelas',
                'bg'   => 'bg-teal-50 text-teal-700 border-teal-200 hover:bg-teal-100',
            ];
        }

        // 6. Portal Sirkulasi Perpustakaan (/portal-perpustakaan)
        if ($isSuper || $this->hasRole('petugas_perpustakaan') || $this->roles->contains(fn($r) => str_contains($r->name, 'perpustakaan'))) {
            $portals[] = [
                'name' => 'Portal Sirkulasi Perpustakaan',
                'url'  => url('/portal-perpustakaan'),
                'desc' => 'Peminjaman, pengembalian & katalog',
                'badge' => 'Perpustakaan',
                'bg'   => 'bg-cyan-50 text-cyan-700 border-cyan-200 hover:bg-cyan-100',
            ];
        }

        // 7. Kiosk Presensi Mandiri (/portal-presensi/scan)
        if ($isSuper || $this->hasRole('petugas_presensi') || $this->roles->contains(fn($r) => str_contains($r->name, 'presensi'))) {
            $portals[] = [
                'name' => 'Kiosk Scan Presensi',
                'url'  => url('/portal-presensi/scan'),
                'desc' => 'Layar kiosk absensi barcode siswa/guru',
                'badge' => 'Kiosk Presensi',
                'bg'   => 'bg-rose-50 text-rose-700 border-rose-200 hover:bg-rose-100',
            ];
        }

        // 8. Portal Siswa (/portal-siswa)
        if ($this->hasRole('siswa') || $this->student !== null) {
            $portals[] = [
                'name' => 'Portal Siswa',
                'url'  => url('/portal-siswa'),
                'desc' => 'Riwayat presensi & peminjaman buku',
                'badge' => 'Siswa',
                'bg'   => 'bg-indigo-50 text-indigo-700 border-indigo-200 hover:bg-indigo-100',
            ];
        }

        return $portals;
    }

    protected function noHp(): Attribute
    {
        return Attribute::make(
            set: function (?string $value) {
                if (!$value) return null;
                $digits = preg_replace('/\D/', '', $value);
                if (str_starts_with($digits, '0')) {
                    $digits = '62' . substr($digits, 1);
                } elseif (!str_starts_with($digits, '62')) {
                    $digits = '62' . $digits;
                }
                return $digits;
            }
        );
    }
}
