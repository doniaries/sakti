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
        if (! $authUser->can('Update:MasterKegiatan')) {
            return false;
        }

        /** @var \App\Models\User $authUser */
        if ($authUser->hasRole('jf') && ! $authUser->hasAnyRole(['super_admin', 'admin', 'pa', 'ppk', 'bendahara'])) {
            return (int) $masterKegiatan->bidang_id === (int) $authUser->bidang_id;
        }

        return true;
    }

    public function delete(AuthUser $authUser, MasterKegiatan $masterKegiatan): bool
    {
        if (! $authUser->can('Delete:MasterKegiatan')) {
            return false;
        }

        /** @var \App\Models\User $authUser */
        if ($authUser->hasRole('jf') && ! $authUser->hasAnyRole(['super_admin', 'admin', 'pa', 'ppk', 'bendahara'])) {
            return (int) $masterKegiatan->bidang_id === (int) $authUser->bidang_id;
        }

        return true;
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:MasterKegiatan');
    }

    public function restore(AuthUser $authUser, MasterKegiatan $masterKegiatan): bool
    {
        return $authUser->can('Restore:MasterKegiatan');
    }

    public function forceDelete(AuthUser $authUser, MasterKegiatan $masterKegiatan): bool
    {
        return $authUser->can('ForceDelete:MasterKegiatan');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:MasterKegiatan');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:MasterKegiatan');
    }

    public function replicate(AuthUser $authUser, MasterKegiatan $masterKegiatan): bool
    {
        return $authUser->can('Replicate:MasterKegiatan');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:MasterKegiatan');
    }

}