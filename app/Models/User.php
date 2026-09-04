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
        $hasAdminRole = $isSuper || $this->roles->contains(fn($r) => str_starts_with($r->name, 'admin_'));

        // 1. Portal Admin (/admin)
        if ($hasAdminRole) {
            $portals[] = [
                'id'    => 'admin',
                'name'  => 'Portal Admin',
                'url'   => url('/admin'),
                'desc'  => 'Pusat administrasi master data akademik, presensi, perpustakaan & pengaturan sistem.',
                'badge' => $isSuper ? 'Super Admin' : 'Admin Panel',
                'badge_color' => 'bg-purple-500/20 text-purple-200 border-purple-400/30',
                'gradient' => 'from-purple-600 to-indigo-700',
                'icon_bg'  => 'bg-purple-500/20 text-purple-300 group-hover:bg-purple-500 group-hover:text-white',
                'border_hover' => 'hover:border-purple-400/50 hover:shadow-purple-500/20',
                'icon'  => 'shield',
            ];
        }

        // 2. Portal Guru (/portal-guru)
        if ($isSuper || $this->hasRole('wali_kelas') || $this->hasRole('guru') || $this->teacher_id !== null || $this->teacher !== null) {
            $portals[] = [
                'id'    => 'portal_guru',
                'name'  => 'Portal Guru',
                'url'   => url('/portal-guru'),
                'desc'  => 'Monitoring absensi kelas binaan, permohonan ijin siswa, dan profil guru.',
                'badge' => 'Guru',
                'badge_color' => 'bg-emerald-500/20 text-emerald-200 border-emerald-400/30',
                'gradient' => 'from-emerald-600 to-teal-700',
                'icon_bg'  => 'bg-emerald-500/20 text-emerald-300 group-hover:bg-emerald-500 group-hover:text-white',
                'border_hover' => 'hover:border-emerald-400/50 hover:shadow-emerald-500/20',
                'icon'  => 'user-group',
            ];
        }

        // 3. Portal Presensi & Kiosk (/portal-presensi)
        if ($isSuper || $this->hasRole('admin_portal_presensi') || $this->hasRole('petugas_presensi') || $this->roles->contains(fn($r) => str_starts_with($r->name, 'admin_presensi'))) {
            $portals[] = [
                'id'    => 'portal_presensi',
                'name'  => 'Portal Presensi',
                'url'   => url('/portal-presensi'),
                'desc'  => 'Dashboard rekap absensi, input presensi manual, cetak laporan & kartu presensi.',
                'badge' => 'Petugas Presensi',
                'badge_color' => 'bg-amber-500/20 text-amber-200 border-amber-400/30',
                'gradient' => 'from-amber-500 to-orange-600',
                'icon_bg'  => 'bg-amber-500/20 text-amber-300 group-hover:bg-amber-500 group-hover:text-white',
                'border_hover' => 'hover:border-amber-400/50 hover:shadow-amber-500/20',
                'icon'  => 'clock',
            ];
        }

        // 4. Portal Perpustakaan (/portal-perpustakaan)
        if ($isSuper || $this->hasRole('petugas_perpustakaan') || $this->roles->contains(fn($r) => str_contains($r->name, 'admin_perpustakaan'))) {
            $portals[] = [
                'id'    => 'portal_perpustakaan',
                'name'  => 'Portal Perpustakaan',
                'url'   => url('/portal-perpustakaan'),
                'desc'  => 'Katalog buku, sirkulasi peminjaman, buku tamu pengunjung & barcode buku.',
                'badge' => 'Petugas Perpustakaan',
                'badge_color' => 'bg-cyan-500/20 text-cyan-200 border-cyan-400/30',
                'gradient' => 'from-cyan-600 to-blue-700',
                'icon_bg'  => 'bg-cyan-500/20 text-cyan-300 group-hover:bg-cyan-500 group-hover:text-white',
                'border_hover' => 'hover:border-cyan-400/50 hover:shadow-cyan-500/20',
                'icon'  => 'book',
            ];
        }

        // 5. Portal Siswa (/portal-siswa) - HANYA untuk akun Siswa yang valid
        $isPureStudent = ($this->hasRole('siswa') || $this->student !== null) && !$isSuper && $this->teacher === null;
        if ($isPureStudent) {
            $portals[] = [
                'id'    => 'portal_siswa',
                'name'  => 'Portal Siswa',
                'url'   => url('/portal-siswa'),
                'desc'  => 'Riwayat kehadiran harian, kartu pelajar digital, dan riwayat peminjaman buku.',
                'badge' => 'Siswa',
                'badge_color' => 'bg-indigo-500/20 text-indigo-200 border-indigo-400/30',
                'gradient' => 'from-indigo-600 to-violet-700',
                'icon_bg'  => 'bg-indigo-500/20 text-indigo-300 group-hover:bg-indigo-500 group-hover:text-white',
                'border_hover' => 'hover:border-indigo-400/50 hover:shadow-indigo-500/20',
                'icon'  => 'academic-cap',
            ];
        }

        // 6. Portal Web Sekolah (/portal-web)
        if ($isSuper || $this->hasRole('admin_portal_web')) {
            $portals[] = [
                'id'    => 'portal_web',
                'name'  => 'Portal Web',
                'url'   => url('/portal-web'),
                'desc'  => 'Command center untuk mengelola artikel, berita, pengumuman, prestasi, dan halaman depan sekolah.',
                'badge' => 'Admin Web',
                'badge_color' => 'bg-violet-500/20 text-violet-200 border-violet-400/30',
                'gradient' => 'from-violet-600 to-fuchsia-700',
                'icon_bg'  => 'bg-violet-500/20 text-violet-300 group-hover:bg-violet-500 group-hover:text-white',
                'border_hover' => 'hover:border-violet-400/50 hover:shadow-violet-500/20',
                'icon'  => 'globe-alt',
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
