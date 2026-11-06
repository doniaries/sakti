<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\MasterSyaratBelanja;
use Illuminate\Auth\Access\HandlesAuthorization;

class MasterSyaratBelanjaPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:MasterSyaratBelanja');
    }

    public function view(AuthUser $authUser, MasterSyaratBelanja $masterSyaratBelanja): bool
    {
        return $authUser->can('View:MasterSyaratBelanja');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:MasterSyaratBelanja');
    }

    public function update(AuthUser $authUser, MasterSyaratBelanja $masterSyaratBelanja): bool
    {
        return $authUser->can('Update:MasterSyaratBelanja');
    }

    public function delete(AuthUser $authUser, MasterSyaratBelanja $masterSyaratBelanja): bool
    {
        return $authUser->can('Delete:MasterSyaratBelanja');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:MasterSyaratBelanja');
    }

}