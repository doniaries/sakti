<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\MasterTujuanBbm;
use Illuminate\Auth\Access\HandlesAuthorization;

class MasterTujuanBbmPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:MasterTujuanBbm');
    }

    public function view(AuthUser $authUser, MasterTujuanBbm $masterTujuanBbm): bool
    {
        return $authUser->can('View:MasterTujuanBbm');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:MasterTujuanBbm');
    }

    public function update(AuthUser $authUser, MasterTujuanBbm $masterTujuanBbm): bool
    {
        return $authUser->can('Update:MasterTujuanBbm');
    }

    public function delete(AuthUser $authUser, MasterTujuanBbm $masterTujuanBbm): bool
    {
        return $authUser->can('Delete:MasterTujuanBbm');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:MasterTujuanBbm');
    }

}