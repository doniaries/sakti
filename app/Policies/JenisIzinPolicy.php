<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\JenisIzin;
use Illuminate\Auth\Access\HandlesAuthorization;

class JenisIzinPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:JenisIzin');
    }

    public function view(AuthUser $authUser, JenisIzin $jenisIzin): bool
    {
        return $authUser->can('View:JenisIzin');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:JenisIzin');
    }

    public function update(AuthUser $authUser, JenisIzin $jenisIzin): bool
    {
        return $authUser->can('Update:JenisIzin');
    }

    public function delete(AuthUser $authUser, JenisIzin $jenisIzin): bool
    {
        return $authUser->can('Delete:JenisIzin');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:JenisIzin');
    }

    public function restore(AuthUser $authUser, JenisIzin $jenisIzin): bool
    {
        return $authUser->can('Restore:JenisIzin');
    }

    public function forceDelete(AuthUser $authUser, JenisIzin $jenisIzin): bool
    {
        return $authUser->can('ForceDelete:JenisIzin');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:JenisIzin');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:JenisIzin');
    }

    public function replicate(AuthUser $authUser, JenisIzin $jenisIzin): bool
    {
        return $authUser->can('Replicate:JenisIzin');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:JenisIzin');
    }

}