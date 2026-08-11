<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\MesinAbsensi;
use Illuminate\Auth\Access\HandlesAuthorization;

class MesinAbsensiPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:MesinAbsensi');
    }

    public function view(AuthUser $authUser, MesinAbsensi $mesinAbsensi): bool
    {
        return $authUser->can('View:MesinAbsensi');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:MesinAbsensi');
    }

    public function update(AuthUser $authUser, MesinAbsensi $mesinAbsensi): bool
    {
        return $authUser->can('Update:MesinAbsensi');
    }

    public function delete(AuthUser $authUser, MesinAbsensi $mesinAbsensi): bool
    {
        return $authUser->can('Delete:MesinAbsensi');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:MesinAbsensi');
    }

    public function restore(AuthUser $authUser, MesinAbsensi $mesinAbsensi): bool
    {
        return $authUser->can('Restore:MesinAbsensi');
    }

    public function forceDelete(AuthUser $authUser, MesinAbsensi $mesinAbsensi): bool
    {
        return $authUser->can('ForceDelete:MesinAbsensi');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:MesinAbsensi');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:MesinAbsensi');
    }

    public function replicate(AuthUser $authUser, MesinAbsensi $mesinAbsensi): bool
    {
        return $authUser->can('Replicate:MesinAbsensi');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:MesinAbsensi');
    }

}