<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\MasterSyaratBelanja;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class MasterSyaratBelanjaPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        /** @var User $authUser */
        return $authUser->hasAnyRole(['super_admin', 'admin']);
    }

    public function view(AuthUser $authUser, MasterSyaratBelanja $masterSyaratBelanja): bool
    {
        /** @var User $authUser */
        return $authUser->hasAnyRole(['super_admin', 'admin']);
    }

    public function create(AuthUser $authUser): bool
    {
        /** @var User $authUser */
        return $authUser->hasAnyRole(['super_admin', 'admin']);
    }

    public function update(AuthUser $authUser, MasterSyaratBelanja $masterSyaratBelanja): bool
    {
        /** @var User $authUser */
        return $authUser->hasAnyRole(['super_admin', 'admin']);
    }

    public function delete(AuthUser $authUser, MasterSyaratBelanja $masterSyaratBelanja): bool
    {
        /** @var User $authUser */
        return $authUser->hasAnyRole(['super_admin', 'admin']);
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        /** @var User $authUser */
        return $authUser->hasAnyRole(['super_admin', 'admin']);
    }
}