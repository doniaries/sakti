<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Instansi;
use Illuminate\Auth\Access\HandlesAuthorization;

class InstansiPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Instansi');
    }

    public function view(AuthUser $authUser, Instansi $instansi): bool
    {
        return $authUser->can('View:Instansi');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Instansi');
    }

    public function update(AuthUser $authUser, Instansi $instansi): bool
    {
        return $authUser->can('Update:Instansi');
    }

    public function delete(AuthUser $authUser, Instansi $instansi): bool
    {
        return $authUser->can('Delete:Instansi');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:Instansi');
    }

}