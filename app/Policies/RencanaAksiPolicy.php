<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\RencanaAksi;
use Illuminate\Auth\Access\HandlesAuthorization;

class RencanaAksiPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:RencanaAksi');
    }

    public function view(AuthUser $authUser, RencanaAksi $rencanaAksi): bool
    {
        return $authUser->can('View:RencanaAksi');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:RencanaAksi');
    }

    public function update(AuthUser $authUser, RencanaAksi $rencanaAksi): bool
    {
        return $authUser->can('Update:RencanaAksi');
    }

    public function delete(AuthUser $authUser, RencanaAksi $rencanaAksi): bool
    {
        return $authUser->can('Delete:RencanaAksi');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:RencanaAksi');
    }

}