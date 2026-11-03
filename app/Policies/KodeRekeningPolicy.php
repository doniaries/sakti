<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\KodeRekening;
use Illuminate\Auth\Access\HandlesAuthorization;

class KodeRekeningPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:KodeRekening');
    }

    public function view(AuthUser $authUser, KodeRekening $kodeRekening): bool
    {
        return $authUser->can('View:KodeRekening');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:KodeRekening');
    }

    public function update(AuthUser $authUser, KodeRekening $kodeRekening): bool
    {
        return $authUser->can('Update:KodeRekening');
    }

    public function delete(AuthUser $authUser, KodeRekening $kodeRekening): bool
    {
        return $authUser->can('Delete:KodeRekening');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:KodeRekening');
    }

    public function restore(AuthUser $authUser, KodeRekening $kodeRekening): bool
    {
        return $authUser->can('Restore:KodeRekening');
    }

    public function forceDelete(AuthUser $authUser, KodeRekening $kodeRekening): bool
    {
        return $authUser->can('ForceDelete:KodeRekening');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:KodeRekening');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:KodeRekening');
    }

    public function replicate(AuthUser $authUser, KodeRekening $kodeRekening): bool
    {
        return $authUser->can('Replicate:KodeRekening');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:KodeRekening');
    }

}