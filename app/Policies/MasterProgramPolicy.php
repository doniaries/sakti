<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\MasterProgram;
use Illuminate\Auth\Access\HandlesAuthorization;

class MasterProgramPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:MasterProgram');
    }

    public function view(AuthUser $authUser, MasterProgram $masterProgram): bool
    {
        return $authUser->can('View:MasterProgram');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:MasterProgram');
    }

    public function update(AuthUser $authUser, MasterProgram $masterProgram): bool
    {
        return $authUser->can('Update:MasterProgram');
    }

    public function delete(AuthUser $authUser, MasterProgram $masterProgram): bool
    {
        return $authUser->can('Delete:MasterProgram');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:MasterProgram');
    }

}