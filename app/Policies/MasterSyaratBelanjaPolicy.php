<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\MasterSyaratBelanja;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class MasterSyaratBelanjaPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return true; // Data global persyaratan, dapat diakses oleh semua pengguna
    }

    public function view(AuthUser $authUser, MasterSyaratBelanja $masterSyaratBelanja): bool
    {
        return true; // Data global persyaratan, dapat diakses oleh semua pengguna
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->hasAnyRole(['super_admin', 'admin', 'ppk', 'bendahara', 'pa']);
    }

    public function update(AuthUser $authUser, MasterSyaratBelanja $masterSyaratBelanja): bool
    {
        return $authUser->hasAnyRole(['super_admin', 'admin', 'ppk', 'bendahara', 'pa']);
    }

    public function delete(AuthUser $authUser, MasterSyaratBelanja $masterSyaratBelanja): bool
    {
        return $authUser->hasAnyRole(['super_admin', 'admin', 'ppk']);
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->hasAnyRole(['super_admin', 'admin', 'ppk']);
    }
}
