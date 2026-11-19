<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\BukuInventaris;
use Illuminate\Auth\Access\HandlesAuthorization;

class BukuInventarisPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:BukuInventaris')
            || $authUser->hasRole('pegawai')
            || (bool) $authUser->is_pengelola_aset;
    }

    public function view(AuthUser $authUser, BukuInventaris $bukuInventaris): bool
    {
        return $authUser->can('View:BukuInventaris')
            || $authUser->hasRole('pegawai')
            || (bool) $authUser->is_pengelola_aset;
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->hasRole('super_admin') || (bool) $authUser->is_pengelola_aset;
    }

    public function update(AuthUser $authUser, BukuInventaris $bukuInventaris): bool
    {
        return $authUser->hasRole('super_admin') || (bool) $authUser->is_pengelola_aset;
    }

    public function delete(AuthUser $authUser, BukuInventaris $bukuInventaris): bool
    {
        return $authUser->hasRole('super_admin') || (bool) $authUser->is_pengelola_aset;
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->hasRole('super_admin') || (bool) $authUser->is_pengelola_aset;
    }

    public function restore(AuthUser $authUser, BukuInventaris $bukuInventaris): bool
    {
        return $authUser->hasRole('super_admin') || (bool) $authUser->is_pengelola_aset;
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->hasRole('super_admin') || (bool) $authUser->is_pengelola_aset;
    }

    public function forceDelete(AuthUser $authUser, BukuInventaris $bukuInventaris): bool
    {
        return $authUser->hasRole('super_admin') || (bool) $authUser->is_pengelola_aset;
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->hasRole('super_admin') || (bool) $authUser->is_pengelola_aset;
    }
}