<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\PortalLink;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PortalLinkPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['super_admin', 'admin', 'admin_kepegawaian', 'admin_keuangan']);
    }

    public function view(User $user, PortalLink $portalLink): bool
    {
        return $user->hasAnyRole(['super_admin', 'admin', 'admin_kepegawaian', 'admin_keuangan']);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['super_admin', 'admin']);
    }

    public function update(User $user, PortalLink $portalLink): bool
    {
        return $user->hasAnyRole(['super_admin', 'admin']);
    }

    public function delete(User $user, PortalLink $portalLink): bool
    {
        return $user->hasAnyRole(['super_admin', 'admin']);
    }

    public function deleteAny(User $user): bool
    {
        return $user->hasAnyRole(['super_admin', 'admin']);
    }
}

