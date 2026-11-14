<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\MasterSubKegiatan;
use Illuminate\Auth\Access\HandlesAuthorization;

class MasterSubKegiatanPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:MasterSubKegiatan');
    }

    public function view(AuthUser $authUser, MasterSubKegiatan $masterSubKegiatan): bool
    {
        return $authUser->can('View:MasterSubKegiatan');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:MasterSubKegiatan');
    }

    public function update(AuthUser $authUser, MasterSubKegiatan $masterSubKegiatan): bool
    {
        return $authUser->can('Update:MasterSubKegiatan');
    }

    public function delete(AuthUser $authUser, MasterSubKegiatan $masterSubKegiatan): bool
    {
        return $authUser->can('Delete:MasterSubKegiatan');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:MasterSubKegiatan');
    }

}