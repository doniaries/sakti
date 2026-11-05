<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\MasterToko;
use Illuminate\Auth\Access\HandlesAuthorization;

class MasterTokoPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:MasterToko');
    }

    public function view(AuthUser $authUser, MasterToko $masterToko): bool
    {
        return $authUser->can('View:MasterToko');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:MasterToko');
    }

    public function update(AuthUser $authUser, MasterToko $masterToko): bool
    {
        return $authUser->can('Update:MasterToko');
    }

    public function delete(AuthUser $authUser, MasterToko $masterToko): bool
    {
        return $authUser->can('Delete:MasterToko');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:MasterToko');
    }

}