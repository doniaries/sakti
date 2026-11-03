<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\PengajuanSpj;
use Illuminate\Auth\Access\HandlesAuthorization;

class PengajuanSpjPolicy
{
    use HandlesAuthorization;
    
    protected function canAccessBidang(AuthUser $authUser, PengajuanSpj $pengajuanSpj): bool
    {
        if ($authUser->can('ViewAny:Bidang')) {
            return true;
        }

        $anggaranRole = $authUser->anggaran_role instanceof \App\Enums\AnggaranRole
            ? $authUser->anggaran_role->value
            : $authUser->anggaran_role;

        if (in_array($anggaranRole, ['PA', 'PPK', 'BENDAHARA'], true)) {
            return true;
        }

        if ($pengajuanSpj->bidang && $authUser->can('view', $pengajuanSpj->bidang)) {
            return true;
        }

        return (int) ($authUser->bidang_id ?? 0) === (int) $pengajuanSpj->bidang_id;
    }

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:PengajuanSpj');
    }

    public function view(AuthUser $authUser, PengajuanSpj $pengajuanSpj): bool
    {
        return $authUser->can('View:PengajuanSpj') && $this->canAccessBidang($authUser, $pengajuanSpj);
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:PengajuanSpj');
    }

    public function update(AuthUser $authUser, PengajuanSpj $pengajuanSpj): bool
    {
        return $authUser->can('Update:PengajuanSpj') && $this->canAccessBidang($authUser, $pengajuanSpj);
    }

    public function delete(AuthUser $authUser, PengajuanSpj $pengajuanSpj): bool
    {
        return $authUser->can('Delete:PengajuanSpj') && $this->canAccessBidang($authUser, $pengajuanSpj);
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:PengajuanSpj');
    }

    public function restore(AuthUser $authUser, PengajuanSpj $pengajuanSpj): bool
    {
        return $authUser->can('Restore:PengajuanSpj');
    }

    public function forceDelete(AuthUser $authUser, PengajuanSpj $pengajuanSpj): bool
    {
        return $authUser->can('ForceDelete:PengajuanSpj');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:PengajuanSpj');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:PengajuanSpj');
    }

    public function replicate(AuthUser $authUser, PengajuanSpj $pengajuanSpj): bool
    {
        return $authUser->can('Replicate:PengajuanSpj');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:PengajuanSpj');
    }

}