<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\PengajuanIzin;
use Illuminate\Auth\Access\HandlesAuthorization;

class PengajuanIzinPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:PengajuanIzin');
    }

    public function view(AuthUser $authUser, PengajuanIzin $pengajuanIzin): bool
    {
        return $authUser->can('View:PengajuanIzin');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:PengajuanIzin');
    }

    public function update(AuthUser $authUser, PengajuanIzin $pengajuanIzin): bool
    {
        return $authUser->can('Update:PengajuanIzin');
    }

    public function delete(AuthUser $authUser, PengajuanIzin $pengajuanIzin): bool
    {
        return $authUser->can('Delete:PengajuanIzin');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:PengajuanIzin');
    }

}