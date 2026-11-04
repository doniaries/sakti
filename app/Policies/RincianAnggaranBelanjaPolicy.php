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
        return $authUser->can('Update:RincianAnggaranBelanja');
    }

    public function delete(AuthUser $authUser, RincianAnggaranBelanja $rincianAnggaranBelanja): bool
    {
        return $authUser->can('Delete:RincianAnggaranBelanja');
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