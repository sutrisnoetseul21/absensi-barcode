<?php

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use Illuminate\Auth\Access\HandlesAuthorization;

class GuruPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Guru');
    }

    public function view(AuthUser $authUser): bool
    {
        return $authUser->can('View:Guru');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Guru');
    }

    public function update(AuthUser $authUser): bool
    {
        return $authUser->can('Update:Guru');
    }

    public function delete(AuthUser $authUser): bool
    {
        return $authUser->can('Delete:Guru');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:Guru');
    }

    public function restore(AuthUser $authUser): bool
    {
        return $authUser->can('Restore:Guru');
    }

    public function forceDelete(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDelete:Guru');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Guru');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Guru');
    }

    public function replicate(AuthUser $authUser): bool
    {
        return $authUser->can('Replicate:Guru');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Guru');
    }

}