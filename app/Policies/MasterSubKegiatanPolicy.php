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
        if (! $authUser->can('Update:MasterSubKegiatan')) {
            return false;
        }

        /** @var \App\Models\User $authUser */
        if ($authUser->hasRole('jf') && ! $authUser->hasAnyRole(['super_admin', 'admin', 'pa', 'ppk', 'bendahara'])) {
            $isSameBidang = (int) $masterSubKegiatan->kegiatan?->bidang_id === (int) $authUser->bidang_id;
            $isPengampu = (int) $masterSubKegiatan->jf_id === (int) $authUser->id;
            return $isSameBidang || $isPengampu;
        }

        return true;
    }

    public function delete(AuthUser $authUser, MasterSubKegiatan $masterSubKegiatan): bool
    {
        if (! $authUser->can('Delete:MasterSubKegiatan')) {
            return false;
        }

        /** @var \App\Models\User $authUser */
        if ($authUser->hasRole('jf') && ! $authUser->hasAnyRole(['super_admin', 'admin', 'pa', 'ppk', 'bendahara'])) {
            $isSameBidang = (int) $masterSubKegiatan->kegiatan?->bidang_id === (int) $authUser->bidang_id;
            $isPengampu = (int) $masterSubKegiatan->jf_id === (int) $authUser->id;
            return $isSameBidang || $isPengampu;
        }

        return true;
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