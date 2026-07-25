<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\RiwayatPindahKelas;
use Illuminate\Auth\Access\HandlesAuthorization;

class RiwayatPindahKelasPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:RiwayatPindahKelas');
    }

    public function view(AuthUser $authUser, RiwayatPindahKelas $riwayatPindahKelas): bool
    {
        return $authUser->can('View:RiwayatPindahKelas');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:RiwayatPindahKelas');
    }

    public function update(AuthUser $authUser, RiwayatPindahKelas $riwayatPindahKelas): bool
    {
        return $authUser->can('Update:RiwayatPindahKelas');
    }

    public function delete(AuthUser $authUser, RiwayatPindahKelas $riwayatPindahKelas): bool
    {
        return $authUser->can('Delete:RiwayatPindahKelas');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:RiwayatPindahKelas');
    }

    public function restore(AuthUser $authUser, RiwayatPindahKelas $riwayatPindahKelas): bool
    {
        return $authUser->can('Restore:RiwayatPindahKelas');
    }

    public function forceDelete(AuthUser $authUser, RiwayatPindahKelas $riwayatPindahKelas): bool
    {
        return $authUser->can('ForceDelete:RiwayatPindahKelas');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:RiwayatPindahKelas');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:RiwayatPindahKelas');
    }

    public function replicate(AuthUser $authUser, RiwayatPindahKelas $riwayatPindahKelas): bool
    {
        return $authUser->can('Replicate:RiwayatPindahKelas');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:RiwayatPindahKelas');
    }

}