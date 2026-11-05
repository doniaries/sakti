<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\MasterKegiatan;
use Illuminate\Auth\Access\HandlesAuthorization;

class MasterKegiatanPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:MasterKegiatan');
    }

    public function view(AuthUser $authUser, MasterKegiatan $masterKegiatan): bool
    {
        return $authUser->can('View:MasterKegiatan');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:MasterKegiatan');
    }

    public function update(AuthUser $authUser, MasterKegiatan $masterKegiatan): bool
    {
        return $authUser->can('Update:MasterKegiatan');
    }

    public function delete(AuthUser $authUser, MasterKegiatan $masterKegiatan): bool
    {
        return $authUser->can('Delete:MasterKegiatan');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:MasterKegiatan');
    }

}