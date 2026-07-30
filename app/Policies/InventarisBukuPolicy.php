<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\InventarisBuku;
use Illuminate\Auth\Access\HandlesAuthorization;

class InventarisBukuPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:InventarisBuku');
    }

    public function view(AuthUser $authUser, InventarisBuku $inventarisBuku): bool
    {
        return $authUser->can('View:InventarisBuku');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:InventarisBuku');
    }

    public function update(AuthUser $authUser, InventarisBuku $inventarisBuku): bool
    {
        return $authUser->can('Update:InventarisBuku');
    }

    public function delete(AuthUser $authUser, InventarisBuku $inventarisBuku): bool
    {
        return $authUser->can('Delete:InventarisBuku');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:InventarisBuku');
    }

    public function restore(AuthUser $authUser, InventarisBuku $inventarisBuku): bool
    {
        return $authUser->can('Restore:InventarisBuku');
    }

    public function forceDelete(AuthUser $authUser, InventarisBuku $inventarisBuku): bool
    {
        return $authUser->can('ForceDelete:InventarisBuku');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:InventarisBuku');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:InventarisBuku');
    }

    public function replicate(AuthUser $authUser, InventarisBuku $inventarisBuku): bool
    {
        return $authUser->can('Replicate:InventarisBuku');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:InventarisBuku');
    }

}