<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Reputasi;
use Illuminate\Auth\Access\HandlesAuthorization;

class ReputasiPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Reputasi');
    }

    public function view(AuthUser $authUser, Reputasi $reputasi): bool
    {
        return $authUser->can('View:Reputasi');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Reputasi');
    }

    public function update(AuthUser $authUser, Reputasi $reputasi): bool
    {
        return $authUser->can('Update:Reputasi');
    }

    public function delete(AuthUser $authUser, Reputasi $reputasi): bool
    {
        return $authUser->can('Delete:Reputasi');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:Reputasi');
    }

    public function restore(AuthUser $authUser, Reputasi $reputasi): bool
    {
        return $authUser->can('Restore:Reputasi');
    }

    public function forceDelete(AuthUser $authUser, Reputasi $reputasi): bool
    {
        return $authUser->can('ForceDelete:Reputasi');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Reputasi');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Reputasi');
    }

    public function replicate(AuthUser $authUser, Reputasi $reputasi): bool
    {
        return $authUser->can('Replicate:Reputasi');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Reputasi');
    }

}