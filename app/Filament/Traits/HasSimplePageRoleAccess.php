<?php

namespace App\Filament\Traits;

trait HasSimplePageRoleAccess
{
    /**
     * Tentukan apakah halaman ini hanya bisa diakses oleh editor.
     * Default false (viewer bisa akses). Override jika perlu.
     */
    protected static function requiresEditorRole(): bool
    {
        return false;
    }

    abstract protected static function getModuleRolePrefix(): string;

    public static function canAccess(): bool
    {
        $user = auth()->user();
        if (!$user) return false;
        
        if ($user->isSuperAdmin()) return true;

        $prefix = static::getModuleRolePrefix();
        $roles = static::requiresEditorRole() 
            ? ["admin_{$prefix}_editor"] 
            : ["admin_{$prefix}_viewer", "admin_{$prefix}_editor"];
            
        return $user->hasAnyRole($roles);
    }
}
