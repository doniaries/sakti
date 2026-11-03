<?php

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;
    
    protected function canAccessTargetUser(AuthUser $authUser, ?AuthUser $record = null): bool
    {
        if (! $record) {
            return true;
        }

        if (method_exists($record, 'hasRole') && $record->hasRole('super_admin')) {
            return method_exists($authUser, 'hasRole') && $authUser->hasRole('super_admin');
        }

        return true;
    }

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:User');
    }

    public function view(AuthUser $authUser, ?AuthUser $record = null): bool
    {
        return $authUser->can('View:User') && $this->canAccessTargetUser($authUser, $record);
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:User');
    }

    public function update(AuthUser $authUser, ?AuthUser $record = null): bool
    {
        return $authUser->can('Update:User') && $this->canAccessTargetUser($authUser, $record);
    }

    public function delete(AuthUser $authUser, ?AuthUser $record = null): bool
    {
        return $authUser->can('Delete:User') && $this->canAccessTargetUser($authUser, $record);
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:User');
    }

    public function restore(AuthUser $authUser): bool
    {
        return $authUser->can('Restore:User');
    }

    public function forceDelete(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDelete:User');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:User');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:User');
    }

    public function replicate(AuthUser $authUser): bool
    {
        return $authUser->can('Replicate:User');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:User');
    }

}