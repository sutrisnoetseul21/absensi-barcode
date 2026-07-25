<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Pengumuman;
use Illuminate\Auth\Access\HandlesAuthorization;

class PengumumanPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Pengumuman');
    }

    public function view(AuthUser $authUser, Pengumuman $pengumuman): bool
    {
        return $authUser->can('View:Pengumuman');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Pengumuman');
    }

    public function update(AuthUser $authUser, Pengumuman $pengumuman): bool
    {
        return $authUser->can('Update:Pengumuman');
    }

    public function delete(AuthUser $authUser, Pengumuman $pengumuman): bool
    {
        return $authUser->can('Delete:Pengumuman');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:Pengumuman');
    }

    public function restore(AuthUser $authUser, Pengumuman $pengumuman): bool
    {
        return $authUser->can('Restore:Pengumuman');
    }

    public function forceDelete(AuthUser $authUser, Pengumuman $pengumuman): bool
    {
        return $authUser->can('ForceDelete:Pengumuman');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Pengumuman');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Pengumuman');
    }

    public function replicate(AuthUser $authUser, Pengumuman $pengumuman): bool
    {
        return $authUser->can('Replicate:Pengumuman');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Pengumuman');
    }

}