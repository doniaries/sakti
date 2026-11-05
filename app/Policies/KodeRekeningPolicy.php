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

}