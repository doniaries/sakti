<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\JadwalKerja;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class JadwalKerjaPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:JadwalKerja');
    }

    public function view(AuthUser $authUser, JadwalKerja $jadwalKerja): bool
    {
        return $authUser->can('View:JadwalKerja');
    }

    public function create(AuthUser $authUser): bool
    {
        /** @var User $authUser */
        if ($authUser->hasAnyRole(['jf', 'pegawai'])) {
            return false;
        }

        return $authUser->can('Create:JadwalKerja');
    }

    public function update(AuthUser $authUser, JadwalKerja $jadwalKerja): bool
    {
        /** @var User $authUser */
        if ($authUser->hasAnyRole(['jf', 'pegawai'])) {
            return false;
        }

        return $authUser->can('Update:JadwalKerja');
    }

    public function delete(AuthUser $authUser, JadwalKerja $jadwalKerja): bool
    {
        /** @var User $authUser */
        if ($authUser->hasAnyRole(['jf', 'pegawai'])) {
            return false;
        }

        return $authUser->can('Delete:JadwalKerja');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        /** @var User $authUser */
        if ($authUser->hasAnyRole(['jf', 'pegawai'])) {
            return false;
        }

        return $authUser->can('DeleteAny:JadwalKerja');
    }
}
