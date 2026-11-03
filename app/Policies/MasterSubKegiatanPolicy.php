<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\MasterSubKegiatan;
use Illuminate\Auth\Access\HandlesAuthorization;

class MasterSubKegiatanPolicy
{
    use HandlesAuthorization;
    
    protected function canAccessBidang(AuthUser $authUser, MasterSubKegiatan $masterSubKegiatan): bool
    {
        if ($authUser->can('ViewAny:Bidang')) {
            return true;
        }

        $kegiatan = $masterSubKegiatan->kegiatan;
        if ($kegiatan && $kegiatan->bidang && $authUser->can('view', $kegiatan->bidang)) {
            return true;
        }

        return (int) ($authUser->bidang_id ?? 0) === (int) ($kegiatan?->bidang_id ?? -1);
    }

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:MasterSubKegiatan');
    }

    public function view(AuthUser $authUser, MasterSubKegiatan $masterSubKegiatan): bool
    {
        return $authUser->can('View:MasterSubKegiatan') && $this->canAccessBidang($authUser, $masterSubKegiatan);
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:MasterSubKegiatan');
    }

    public function update(AuthUser $authUser, MasterSubKegiatan $masterSubKegiatan): bool
    {
        return $authUser->can('Update:MasterSubKegiatan') && $this->canAccessBidang($authUser, $masterSubKegiatan);
    }

    public function delete(AuthUser $authUser, MasterSubKegiatan $masterSubKegiatan): bool
    {
        return $authUser->can('Delete:MasterSubKegiatan') && $this->canAccessBidang($authUser, $masterSubKegiatan);
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:MasterSubKegiatan');
    }

    public function restore(AuthUser $authUser, MasterSubKegiatan $masterSubKegiatan): bool
    {
        return $authUser->can('Restore:MasterSubKegiatan');
    }

    public function forceDelete(AuthUser $authUser, MasterSubKegiatan $masterSubKegiatan): bool
    {
        return $authUser->can('ForceDelete:MasterSubKegiatan');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:MasterSubKegiatan');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:MasterSubKegiatan');
    }

    public function replicate(AuthUser $authUser, MasterSubKegiatan $masterSubKegiatan): bool
    {
        return $authUser->can('Replicate:MasterSubKegiatan');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:MasterSubKegiatan');
    }

}