<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Aktivitas;
use Illuminate\Auth\Access\HandlesAuthorization;

class AktivitasPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Aktivitas');
    }

    public function view(AuthUser $authUser, Aktivitas $aktivitas): bool
    {
        return $authUser->can('View:Aktivitas');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Aktivitas');
    }

    public function update(AuthUser $authUser, Aktivitas $aktivitas): bool
    {
        return $authUser->can('Update:Aktivitas');
    }

    public function delete(AuthUser $authUser, Aktivitas $aktivitas): bool
    {
        return $authUser->can('Delete:Aktivitas');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:Aktivitas');
    }

    public function restore(AuthUser $authUser, Aktivitas $aktivitas): bool
    {
        return $authUser->can('Restore:Aktivitas');
    }

    public function forceDelete(AuthUser $authUser, Aktivitas $aktivitas): bool
    {
        return $authUser->can('ForceDelete:Aktivitas');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Aktivitas');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Aktivitas');
    }

    public function replicate(AuthUser $authUser, Aktivitas $aktivitas): bool
    {
        return $authUser->can('Replicate:Aktivitas');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Aktivitas');
    }

}