<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\MasterBarang;
use Illuminate\Auth\Access\HandlesAuthorization;

class MasterBarangPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:MasterBarang');
    }

    public function view(AuthUser $authUser, MasterBarang $masterBarang): bool
    {
        return $authUser->can('View:MasterBarang');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:MasterBarang');
    }

    public function update(AuthUser $authUser, MasterBarang $masterBarang): bool
    {
        return $authUser->can('Update:MasterBarang');
    }

    public function delete(AuthUser $authUser, MasterBarang $masterBarang): bool
    {
        return $authUser->can('Delete:MasterBarang');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:MasterBarang');
    }

    public function restore(AuthUser $authUser, MasterBarang $masterBarang): bool
    {
        return $authUser->can('Restore:MasterBarang');
    }

    public function forceDelete(AuthUser $authUser, MasterBarang $masterBarang): bool
    {
        return $authUser->can('ForceDelete:MasterBarang');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:MasterBarang');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:MasterBarang');
    }

    public function replicate(AuthUser $authUser, MasterBarang $masterBarang): bool
    {
        return $authUser->can('Replicate:MasterBarang');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:MasterBarang');
    }

}