<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\MasterJenisBelanja;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class MasterJenisBelanjaPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        /** @var User $authUser */
        return $authUser->hasAnyRole(['super_admin', 'admin']);
    }

    public function view(AuthUser $authUser, MasterJenisBelanja $masterJenisBelanja): bool
    {
        /** @var User $authUser */
        return $authUser->hasAnyRole(['super_admin', 'admin']);
    }

    public function create(AuthUser $authUser): bool
    {
        /** @var User $authUser */
        return $authUser->hasAnyRole(['super_admin', 'admin']);
    }

    public function update(AuthUser $authUser, MasterJenisBelanja $masterJenisBelanja): bool
    {
        /** @var User $authUser */
        return $authUser->hasAnyRole(['super_admin', 'admin']);
    }

    public function delete(AuthUser $authUser, MasterJenisBelanja $masterJenisBelanja): bool
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

