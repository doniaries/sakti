<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\AbsensiHarian;
use Illuminate\Auth\Access\HandlesAuthorization;

class AbsensiHarianPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:AbsensiHarian');
    }

    public function view(AuthUser $authUser, AbsensiHarian $absensiHarian): bool
    {
        return $authUser->can('View:AbsensiHarian');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:AbsensiHarian');
    }

    public function update(AuthUser $authUser, AbsensiHarian $absensiHarian): bool
    {
        return $authUser->can('Update:AbsensiHarian');
    }

    public function delete(AuthUser $authUser, AbsensiHarian $absensiHarian): bool
    {
        return $authUser->can('Delete:AbsensiHarian');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:AbsensiHarian');
    }

    public function restore(AuthUser $authUser, AbsensiHarian $absensiHarian): bool
    {
        return $authUser->can('Restore:AbsensiHarian');
    }

    public function forceDelete(AuthUser $authUser, AbsensiHarian $absensiHarian): bool
    {
        return $authUser->can('ForceDelete:AbsensiHarian');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:AbsensiHarian');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:AbsensiHarian');
    }

    public function replicate(AuthUser $authUser, AbsensiHarian $absensiHarian): bool
    {
        return $authUser->can('Replicate:AbsensiHarian');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:AbsensiHarian');
    }

}