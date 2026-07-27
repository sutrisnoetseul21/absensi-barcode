<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\KategoriBuku;
use Illuminate\Auth\Access\HandlesAuthorization;

class KategoriBukuPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:KategoriBuku');
    }

    public function view(AuthUser $authUser, KategoriBuku $kategoriBuku): bool
    {
        return $authUser->can('View:KategoriBuku');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:KategoriBuku');
    }

    public function update(AuthUser $authUser, KategoriBuku $kategoriBuku): bool
    {
        return $authUser->can('Update:KategoriBuku');
    }

    public function delete(AuthUser $authUser, KategoriBuku $kategoriBuku): bool
    {
        return $authUser->can('Delete:KategoriBuku');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:KategoriBuku');
    }

    public function restore(AuthUser $authUser, KategoriBuku $kategoriBuku): bool
    {
        return $authUser->can('Restore:KategoriBuku');
    }

    public function forceDelete(AuthUser $authUser, KategoriBuku $kategoriBuku): bool
    {
        return $authUser->can('ForceDelete:KategoriBuku');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:KategoriBuku');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:KategoriBuku');
    }

    public function replicate(AuthUser $authUser, KategoriBuku $kategoriBuku): bool
    {
        return $authUser->can('Replicate:KategoriBuku');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:KategoriBuku');
    }

}