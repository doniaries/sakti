<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\StokBarang;
use Illuminate\Auth\Access\HandlesAuthorization;

class StokBarangPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:StokBarang');
    }

    public function view(AuthUser $authUser, StokBarang $stokBarang): bool
    {
        return $authUser->can('View:StokBarang');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:StokBarang');
    }

    public function update(AuthUser $authUser, StokBarang $stokBarang): bool
    {
        return $authUser->can('Update:StokBarang');
    }

    public function delete(AuthUser $authUser, StokBarang $stokBarang): bool
    {
        return $authUser->can('Delete:StokBarang');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:StokBarang');
    }

}