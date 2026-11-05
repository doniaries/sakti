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

}