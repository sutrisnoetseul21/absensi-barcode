<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\KlasifikasiDdc;
use Illuminate\Auth\Access\HandlesAuthorization;

class KlasifikasiDdcPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:KlasifikasiDdc');
    }

    public function view(AuthUser $authUser, KlasifikasiDdc $klasifikasiDdc): bool
    {
        return $authUser->can('View:KlasifikasiDdc');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:KlasifikasiDdc');
    }

    public function update(AuthUser $authUser, KlasifikasiDdc $klasifikasiDdc): bool
    {
        return $authUser->can('Update:KlasifikasiDdc');
    }

    public function delete(AuthUser $authUser, KlasifikasiDdc $klasifikasiDdc): bool
    {
        return $authUser->can('Delete:KlasifikasiDdc');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:KlasifikasiDdc');
    }

    public function restore(AuthUser $authUser, KlasifikasiDdc $klasifikasiDdc): bool
    {
        return $authUser->can('Restore:KlasifikasiDdc');
    }

    public function forceDelete(AuthUser $authUser, KlasifikasiDdc $klasifikasiDdc): bool
    {
        return $authUser->can('ForceDelete:KlasifikasiDdc');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:KlasifikasiDdc');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:KlasifikasiDdc');
    }

    public function replicate(AuthUser $authUser, KlasifikasiDdc $klasifikasiDdc): bool
    {
        return $authUser->can('Replicate:KlasifikasiDdc');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:KlasifikasiDdc');
    }

}