<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\MasterJenisBelanja;
use Illuminate\Auth\Access\HandlesAuthorization;

class MasterJenisBelanjaPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:MasterJenisBelanja');
    }

    public function view(AuthUser $authUser, MasterJenisBelanja $masterJenisBelanja): bool
    {
        return $authUser->can('View:MasterJenisBelanja');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:MasterJenisBelanja');
    }

    public function update(AuthUser $authUser, MasterJenisBelanja $masterJenisBelanja): bool
    {
        return $authUser->can('Update:MasterJenisBelanja');
    }

    public function delete(AuthUser $authUser, MasterJenisBelanja $masterJenisBelanja): bool
    {
        return $authUser->can('Delete:MasterJenisBelanja');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:MasterJenisBelanja');
    }

}