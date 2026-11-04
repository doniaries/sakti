<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\StokBarang;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class StokBarangPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:StokBarang');
    }

    public function view(AuthUser $authUser, StokBarang $stokBarang): bool
    {
        return $authUser->can('View:StokBarang');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:StokBarang');
    }

    public function update(AuthUser $authUser, StokBarang $stokBarang): bool
    {
        return $authUser->can('Update:StokBarang');
    }

    public function delete(AuthUser $authUser, StokBarang $stokBarang): bool
    {
        return $authUser->can('Delete:StokBarang');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:StokBarang');
    }

    public function restore(AuthUser $authUser, StokBarang $stokBarang): bool
    {
        return $authUser->can('Restore:StokBarang');
    }

    public function forceDelete(AuthUser $authUser, StokBarang $stokBarang): bool
    {
        return $authUser->can('ForceDelete:StokBarang');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:StokBarang');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:StokBarang');
    }

    public function replicate(AuthUser $authUser, StokBarang $stokBarang): bool
    {
        return $authUser->can('Replicate:StokBarang');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:StokBarang');
    }
}

