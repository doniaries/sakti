<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\AbsensiMobile;
use Illuminate\Auth\Access\HandlesAuthorization;

class AbsensiMobilePolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:AbsensiMobile');
    }

    public function view(AuthUser $authUser, AbsensiMobile $absensiMobile): bool
    {
        return $authUser->can('View:AbsensiMobile');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:AbsensiMobile');
    }

    public function update(AuthUser $authUser, AbsensiMobile $absensiMobile): bool
    {
        return $authUser->can('Update:AbsensiMobile');
    }

    public function delete(AuthUser $authUser, AbsensiMobile $absensiMobile): bool
    {
        return $authUser->can('Delete:AbsensiMobile');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:AbsensiMobile');
    }

    public function restore(AuthUser $authUser, AbsensiMobile $absensiMobile): bool
    {
        return $authUser->can('Restore:AbsensiMobile');
    }

    public function forceDelete(AuthUser $authUser, AbsensiMobile $absensiMobile): bool
    {
        return $authUser->can('ForceDelete:AbsensiMobile');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:AbsensiMobile');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:AbsensiMobile');
    }

    public function replicate(AuthUser $authUser, AbsensiMobile $absensiMobile): bool
    {
        return $authUser->can('Replicate:AbsensiMobile');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:AbsensiMobile');
    }

}