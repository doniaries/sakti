<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\BukuInventaris;
use Illuminate\Auth\Access\HandlesAuthorization;

class BukuInventarisPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:BukuInventaris');
    }

    public function view(AuthUser $authUser, BukuInventaris $bukuInventaris): bool
    {
        return $authUser->can('View:BukuInventaris');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:BukuInventaris');
    }

    public function update(AuthUser $authUser, BukuInventaris $bukuInventaris): bool
    {
        return $authUser->can('Update:BukuInventaris');
    }

    public function delete(AuthUser $authUser, BukuInventaris $bukuInventaris): bool
    {
        return $authUser->can('Delete:BukuInventaris');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:BukuInventaris');
    }

}