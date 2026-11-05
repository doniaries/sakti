<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Aktivitas;
use Illuminate\Auth\Access\HandlesAuthorization;

class AktivitasPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Aktivitas');
    }

    public function view(AuthUser $authUser, Aktivitas $aktivitas): bool
    {
        return $authUser->can('View:Aktivitas');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Aktivitas');
    }

    public function update(AuthUser $authUser, Aktivitas $aktivitas): bool
    {
        return $authUser->can('Update:Aktivitas');
    }

    public function delete(AuthUser $authUser, Aktivitas $aktivitas): bool
    {
        return $authUser->can('Delete:Aktivitas');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:Aktivitas');
    }

}