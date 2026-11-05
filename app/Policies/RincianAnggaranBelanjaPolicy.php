<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\RincianAnggaranBelanja;
use Illuminate\Auth\Access\HandlesAuthorization;

class RincianAnggaranBelanjaPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:RincianAnggaranBelanja');
    }

    public function view(AuthUser $authUser, RincianAnggaranBelanja $rincianAnggaranBelanja): bool
    {
        return $authUser->can('View:RincianAnggaranBelanja');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:RincianAnggaranBelanja');
    }

    public function update(AuthUser $authUser, RincianAnggaranBelanja $rincianAnggaranBelanja): bool
    {
        if (! $authUser->can('Update:RincianAnggaranBelanja')) {
            return false;
        }

        /** @var \App\Models\User $authUser */
        if ($authUser->hasRole('jf') && ! $authUser->hasAnyRole(['super_admin', 'admin', 'pa', 'ppk', 'bendahara'])) {
            $isSameBidang = (int) $rincianAnggaranBelanja->bidang_id === (int) $authUser->bidang_id
                || (int) $rincianAnggaranBelanja->subKegiatan?->kegiatan?->bidang_id === (int) $authUser->bidang_id;
            $isPengampu = (int) $rincianAnggaranBelanja->subKegiatan?->jf_id === (int) $authUser->id;
            return $isSameBidang || $isPengampu;
        }

        return true;
    }

    public function delete(AuthUser $authUser, RincianAnggaranBelanja $rincianAnggaranBelanja): bool
    {
        if (! $authUser->can('Delete:RincianAnggaranBelanja')) {
            return false;
        }

        /** @var \App\Models\User $authUser */
        if ($authUser->hasRole('jf') && ! $authUser->hasAnyRole(['super_admin', 'admin', 'pa', 'ppk', 'bendahara'])) {
            $isSameBidang = (int) $rincianAnggaranBelanja->bidang_id === (int) $authUser->bidang_id
                || (int) $rincianAnggaranBelanja->subKegiatan?->kegiatan?->bidang_id === (int) $authUser->bidang_id;
            $isPengampu = (int) $rincianAnggaranBelanja->subKegiatan?->jf_id === (int) $authUser->id;
            return $isSameBidang || $isPengampu;
        }

        return true;
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:RincianAnggaranBelanja');
    }

    public function restore(AuthUser $authUser, RincianAnggaranBelanja $rincianAnggaranBelanja): bool
    {
        return $authUser->can('Restore:RincianAnggaranBelanja');
    }

    public function forceDelete(AuthUser $authUser, RincianAnggaranBelanja $rincianAnggaranBelanja): bool
    {
        return $authUser->can('ForceDelete:RincianAnggaranBelanja');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:RincianAnggaranBelanja');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:RincianAnggaranBelanja');
    }

    public function replicate(AuthUser $authUser, RincianAnggaranBelanja $rincianAnggaranBelanja): bool
    {
        return $authUser->can('Replicate:RincianAnggaranBelanja');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:RincianAnggaranBelanja');
    }

}