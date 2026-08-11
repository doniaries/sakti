<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\RencanaAksi;
use Illuminate\Auth\Access\HandlesAuthorization;

class RencanaAksiPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:RencanaAksi');
    }

    public function view(AuthUser $authUser, RencanaAksi $rencanaAksi): bool
    {
        return $authUser->can('View:RencanaAksi');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:RencanaAksi');
    }

    public function update(AuthUser $authUser, RencanaAksi $rencanaAksi): bool
    {
        return $authUser->can('Update:RencanaAksi');
    }

    public function delete(AuthUser $authUser, RencanaAksi $rencanaAksi): bool
    {
        return $authUser->can('Delete:RencanaAksi');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:RencanaAksi');
    }

    public function restore(AuthUser $authUser, RencanaAksi $rencanaAksi): bool
    {
        return $authUser->can('Restore:RencanaAksi');
    }

    public function forceDelete(AuthUser $authUser, RencanaAksi $rencanaAksi): bool
    {
        return $authUser->can('ForceDelete:RencanaAksi');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:RencanaAksi');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:RencanaAksi');
    }

    public function replicate(AuthUser $authUser, RencanaAksi $rencanaAksi): bool
    {
        return $authUser->can('Replicate:RencanaAksi');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:RencanaAksi');
    }

}