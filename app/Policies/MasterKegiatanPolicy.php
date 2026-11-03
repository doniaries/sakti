<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\MasterKegiatan;
use Illuminate\Auth\Access\HandlesAuthorization;

class MasterKegiatanPolicy
{
    use HandlesAuthorization;
    
    protected function canAccessBidang(AuthUser $authUser, MasterKegiatan $masterKegiatan): bool
    {
        if ($authUser->can('ViewAny:Bidang')) {
            return true;
        }

        if ($masterKegiatan->bidang && $authUser->can('view', $masterKegiatan->bidang)) {
            return true;
        }

        return (int) ($authUser->bidang_id ?? 0) === (int) $masterKegiatan->bidang_id;
    }

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:MasterKegiatan');
    }

    public function view(AuthUser $authUser, MasterKegiatan $masterKegiatan): bool
    {
        return $authUser->can('View:MasterKegiatan') && $this->canAccessBidang($authUser, $masterKegiatan);
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:MasterKegiatan');
    }

    public function update(AuthUser $authUser, MasterKegiatan $masterKegiatan): bool
    {
        return $authUser->can('Update:MasterKegiatan') && $this->canAccessBidang($authUser, $masterKegiatan);
    }

    public function delete(AuthUser $authUser, MasterKegiatan $masterKegiatan): bool
    {
        return $authUser->can('Delete:MasterKegiatan') && $this->canAccessBidang($authUser, $masterKegiatan);
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