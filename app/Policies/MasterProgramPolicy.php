<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\MasterProgram;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class MasterProgramPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:MasterProgram') || $authUser->hasAnyRole(['super_admin', 'admin']);
    }

    public function view(AuthUser $authUser, MasterProgram $masterProgram): bool
    {
        return $authUser->can('View:MasterProgram') || $authUser->hasAnyRole(['super_admin', 'admin']);
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:MasterProgram') || $authUser->hasAnyRole(['super_admin', 'admin']);
    }

    public function update(AuthUser $authUser, MasterProgram $masterProgram): bool
    {
        return $authUser->can('Update:MasterProgram') || $authUser->hasAnyRole(['super_admin', 'admin']);
    }

    public function delete(AuthUser $authUser, MasterProgram $masterProgram): bool
    {
        return $authUser->can('Delete:MasterProgram') || $authUser->hasAnyRole(['super_admin', 'admin']);
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:MasterProgram') || $authUser->hasAnyRole(['super_admin', 'admin']);
    }

    public function restore(AuthUser $authUser, MasterProgram $masterProgram): bool
    {
        return $authUser->can('Restore:MasterProgram') || $authUser->hasAnyRole(['super_admin', 'admin']);
    }

    public function forceDelete(AuthUser $authUser, MasterProgram $masterProgram): bool
    {
        return $authUser->can('ForceDelete:MasterProgram') || $authUser->hasAnyRole(['super_admin', 'admin']);
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:MasterProgram') || $authUser->hasAnyRole(['super_admin', 'admin']);
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:MasterProgram') || $authUser->hasAnyRole(['super_admin', 'admin']);
    }

    public function replicate(AuthUser $authUser, MasterProgram $masterProgram): bool
    {
        return $authUser->can('Replicate:MasterProgram') || $authUser->hasAnyRole(['super_admin', 'admin']);
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:MasterProgram') || $authUser->hasAnyRole(['super_admin', 'admin']);
    }
}

