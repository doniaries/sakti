<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\BukuInventaris;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class BukuInventarisPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $authUser): bool
    {
        return $authUser->canViewAset();
    }

    public function view(User $authUser, BukuInventaris $bukuInventaris): bool
    {
        return $authUser->canViewAset();
    }

    public function create(User $authUser): bool
    {
        return $authUser->canManageAset();
    }

    public function update(User $authUser, BukuInventaris $bukuInventaris): bool
    {
        return $authUser->canManageAset();
    }

    public function delete(User $authUser, BukuInventaris $bukuInventaris): bool
    {
        return $authUser->canManageAset();
    }

    public function deleteAny(User $authUser): bool
    {
        return $authUser->canManageAset();
    }

    public function restore(User $authUser, BukuInventaris $bukuInventaris): bool
    {
        return $authUser->canManageAset();
    }

    public function restoreAny(User $authUser): bool
    {
        return $authUser->canManageAset();
    }

    public function forceDelete(User $authUser, BukuInventaris $bukuInventaris): bool
    {
        return $authUser->canManageAset();
    }

    public function forceDeleteAny(User $authUser): bool
    {
        return $authUser->canManageAset();
    }
}
