<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\SuratTugas;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class SuratTugasPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $authUser): bool
    {
        return $authUser->canManageSuratTugas() || filled($authUser->jabatan_tim_kreatif);
    }

    public function view(User $authUser, SuratTugas $suratTugas): bool
    {
        if ($authUser->canManageSuratTugas()) {
            return true;
        }

        return $suratTugas->penandatangan_id === $authUser->id
            || $suratTugas->penanggung_jawab_id === $authUser->id
            || $suratTugas->pegawais()->where('users.id', $authUser->id)->exists();
    }

    public function create(User $authUser): bool
    {
        return $authUser->canManageSuratTugas();
    }

    public function update(User $authUser, SuratTugas $suratTugas): bool
    {
        return $authUser->canManageSuratTugas();
    }

    public function delete(User $authUser, SuratTugas $suratTugas): bool
    {
        return $authUser->hasRole('super_admin');
    }

    public function deleteAny(User $authUser): bool
    {
        return $authUser->hasRole('super_admin');
    }

    public function restore(User $authUser, SuratTugas $suratTugas): bool
    {
        return $authUser->hasRole('super_admin');
    }

    public function restoreAny(User $authUser): bool
    {
        return $authUser->hasRole('super_admin');
    }

    public function forceDelete(User $authUser, SuratTugas $suratTugas): bool
    {
        return $authUser->hasRole('super_admin');
    }

    public function forceDeleteAny(User $authUser): bool
    {
        return $authUser->hasRole('super_admin');
    }
}
