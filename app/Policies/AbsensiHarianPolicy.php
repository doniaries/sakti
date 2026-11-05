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

}