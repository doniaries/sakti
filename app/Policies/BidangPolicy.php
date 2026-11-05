<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Bidang;
use Illuminate\Auth\Access\HandlesAuthorization;

class BidangPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Bidang');
    }

    public function view(AuthUser $authUser, Bidang $bidang): bool
    {
        return $authUser->can('View:Bidang');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Bidang');
    }

    public function update(AuthUser $authUser, Bidang $bidang): bool
    {
        return $authUser->can('Update:Bidang');
    }

    public function delete(AuthUser $authUser, Bidang $bidang): bool
    {
        return $authUser->can('Delete:Bidang');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:Bidang');
    }

}