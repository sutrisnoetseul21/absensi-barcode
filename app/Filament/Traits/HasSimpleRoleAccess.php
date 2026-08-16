<?php

namespace App\Filament\Traits;

use Illuminate\Database\Eloquent\Model;

trait HasSimpleRoleAccess
{
    /**
     * Tentukan prefix role untuk resource ini.
     * Contoh: 'akademik', 'presensi', 'perpustakaan', 'master'
     */
    abstract protected static function getModuleRolePrefix(): string;

    public static function canViewAny(): bool
    {
        $user = auth()->user();
        if (!$user) return false;
        
        if ($user->isSuperAdmin()) return true;

        $prefix = static::getModuleRolePrefix();
        return $user->hasAnyRole(["admin_{$prefix}_viewer", "admin_{$prefix}_editor"]);
    }

    public static function canCreate(): bool
    {
        $user = auth()->user();
        if (!$user) return false;
        
        if ($user->isSuperAdmin()) return true;

        $prefix = static::getModuleRolePrefix();
        return $user->hasRole("admin_{$prefix}_editor");
    }

    public static function canEdit(Model $record): bool
    {
        $user = auth()->user();
        if (!$user) return false;
        
        if ($user->isSuperAdmin()) return true;

        $prefix = static::getModuleRolePrefix();
        return $user->hasRole("admin_{$prefix}_editor");
    }

    public static function canDelete(Model $record): bool
    {
        $user = auth()->user();
        if (!$user) return false;
        
        if ($user->isSuperAdmin()) return true;

        $prefix = static::getModuleRolePrefix();
        return $user->hasRole("admin_{$prefix}_editor");
    }
}
