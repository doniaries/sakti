<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\MasterToko;
use Illuminate\Auth\Access\HandlesAuthorization;

class MasterTokoPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:MasterToko');
    }

    public function view(AuthUser $authUser, MasterToko $masterToko): bool
    {
        return $authUser->can('View:MasterToko');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:MasterToko');
    }

    public function update(AuthUser $authUser, MasterToko $masterToko): bool
    {
        return $authUser->can('Update:MasterToko');
    }

    public function delete(AuthUser $authUser, MasterToko $masterToko): bool
    {
        return $authUser->can('Delete:MasterToko');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:MasterToko');
    }

    public function restore(AuthUser $authUser, MasterToko $masterToko): bool
    {
        return $authUser->can('Restore:MasterToko');
    }

    public function forceDelete(AuthUser $authUser, MasterToko $masterToko): bool
    {
        return $authUser->can('ForceDelete:MasterToko');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:MasterToko');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:MasterToko');
    }

    public function replicate(AuthUser $authUser, MasterToko $masterToko): bool
    {
        return $authUser->can('Replicate:MasterToko');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:MasterToko');
    }

}