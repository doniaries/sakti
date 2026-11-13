<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Jabatan;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class JabatanPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Jabatan');
    }

    public function view(AuthUser $authUser, Jabatan $jabatan): bool
    {
        return $authUser->can('View:Jabatan');
    }

    public function create(AuthUser $authUser): bool
    {
        /** @var User $authUser */
        if ($authUser->hasAnyRole(['jf', 'pegawai'])) {
            return false;
        }

        return $authUser->can('Create:Jabatan');
    }

    public function update(AuthUser $authUser, Jabatan $jabatan): bool
    {
        /** @var User $authUser */
        if ($authUser->hasAnyRole(['jf', 'pegawai'])) {
            return false;
        }

        return $authUser->can('Update:Jabatan');
    }

    public function delete(AuthUser $authUser, Jabatan $jabatan): bool
    {
        /** @var User $authUser */
        if ($authUser->hasAnyRole(['jf', 'pegawai'])) {
            return false;
        }

        return $authUser->can('Delete:Jabatan');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        /** @var User $authUser */
        if ($authUser->hasAnyRole(['jf', 'pegawai'])) {
            return false;
        }

        return $authUser->can('DeleteAny:Jabatan');
    }
}
