<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\LogAbsensi;
use Illuminate\Auth\Access\HandlesAuthorization;

class LogAbsensiPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:LogAbsensi');
    }

    public function view(AuthUser $authUser, LogAbsensi $logAbsensi): bool
    {
        return $authUser->can('View:LogAbsensi');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:LogAbsensi');
    }

    public function update(AuthUser $authUser, LogAbsensi $logAbsensi): bool
    {
        return $authUser->can('Update:LogAbsensi');
    }

    public function delete(AuthUser $authUser, LogAbsensi $logAbsensi): bool
    {
        return $authUser->can('Delete:LogAbsensi');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:LogAbsensi');
    }

}