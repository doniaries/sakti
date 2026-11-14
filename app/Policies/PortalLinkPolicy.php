<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\PortalLink;
use Illuminate\Auth\Access\HandlesAuthorization;

class PortalLinkPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:PortalLink');
    }

    public function view(AuthUser $authUser, PortalLink $portalLink): bool
    {
        return $authUser->can('View:PortalLink');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:PortalLink');
    }

    public function update(AuthUser $authUser, PortalLink $portalLink): bool
    {
        return $authUser->can('Update:PortalLink');
    }

    public function delete(AuthUser $authUser, PortalLink $portalLink): bool
    {
        return $authUser->can('Delete:PortalLink');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:PortalLink');
    }

}