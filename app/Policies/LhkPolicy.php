<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Lhk;
use Illuminate\Auth\Access\HandlesAuthorization;

class LhkPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Lhk');
    }

    public function view(AuthUser $authUser, Lhk $lhk): bool
    {
        return $authUser->can('View:Lhk');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Lhk');
    }

    public function update(AuthUser $authUser, Lhk $lhk): bool
    {
        return $authUser->can('Update:Lhk');
    }

    public function delete(AuthUser $authUser, Lhk $lhk): bool
    {
        return $authUser->can('Delete:Lhk');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:Lhk');
    }

    public function restore(AuthUser $authUser, Lhk $lhk): bool
    {
        return $authUser->can('Restore:Lhk');
    }

    public function forceDelete(AuthUser $authUser, Lhk $lhk): bool
    {
        return $authUser->can('ForceDelete:Lhk');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Lhk');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Lhk');
    }

    public function replicate(AuthUser $authUser, Lhk $lhk): bool
    {
        return $authUser->can('Replicate:Lhk');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Lhk');
    }

}