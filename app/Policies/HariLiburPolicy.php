<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\HariLibur;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class HariLiburPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:HariLibur');
    }

    public function view(AuthUser $authUser, HariLibur $hariLibur): bool
    {
        return $authUser->can('View:HariLibur');
    }

    public function create(AuthUser $authUser): bool
    {
        /** @var User $authUser */
        if ($authUser->hasAnyRole(['jf', 'pegawai'])) {
            return false;
        }

        return $authUser->can('Create:HariLibur');
    }

    public function update(AuthUser $authUser, HariLibur $hariLibur): bool
    {
        /** @var User $authUser */
        if ($authUser->hasAnyRole(['jf', 'pegawai'])) {
            return false;
        }

        return $authUser->can('Update:HariLibur');
    }

    public function delete(AuthUser $authUser, HariLibur $hariLibur): bool
    {
        /** @var User $authUser */
        if ($authUser->hasAnyRole(['jf', 'pegawai'])) {
            return false;
        }

        return $authUser->can('Delete:HariLibur');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        /** @var User $authUser */
        if ($authUser->hasAnyRole(['jf', 'pegawai'])) {
            return false;
        }

        return $authUser->can('DeleteAny:HariLibur');
    }
}
