<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\PengaturanAplikasi;
use Illuminate\Auth\Access\HandlesAuthorization;

class PengaturanAplikasiPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:PengaturanAplikasi');
    }

    public function view(AuthUser $authUser, PengaturanAplikasi $pengaturanAplikasi): bool
    {
        return $authUser->can('View:PengaturanAplikasi');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:PengaturanAplikasi');
    }

    public function update(AuthUser $authUser, PengaturanAplikasi $pengaturanAplikasi): bool
    {
        return $authUser->can('Update:PengaturanAplikasi');
    }

    public function delete(AuthUser $authUser, PengaturanAplikasi $pengaturanAplikasi): bool
    {
        return $authUser->can('Delete:PengaturanAplikasi');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:PengaturanAplikasi');
    }

    public function restore(AuthUser $authUser, PengaturanAplikasi $pengaturanAplikasi): bool
    {
        return $authUser->can('Restore:PengaturanAplikasi');
    }

    public function forceDelete(AuthUser $authUser, PengaturanAplikasi $pengaturanAplikasi): bool
    {
        return $authUser->can('ForceDelete:PengaturanAplikasi');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:PengaturanAplikasi');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:PengaturanAplikasi');
    }

    public function replicate(AuthUser $authUser, PengaturanAplikasi $pengaturanAplikasi): bool
    {
        return $authUser->can('Replicate:PengaturanAplikasi');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:PengaturanAplikasi');
    }

}