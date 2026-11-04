<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\RincianAnggaranBelanja;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class RincianAnggaranBelanjaPolicy
{
    use HandlesAuthorization;

    public function before(User $user, string $ability): ?bool
    {
        if ($user->hasRole(['super_admin', 'admin'])) {
            return true;
        }

        return null;
    }

    public function viewAny(User $user): bool
    {
        return $user->can('view_any_rincian::anggaran::belanja') || $user->hasRole(['super_admin', 'admin', 'pegawai']);
    }

    public function view(User $user, RincianAnggaranBelanja $record): bool
    {
        return $user->can('view_rincian::anggaran::belanja') || $user->hasRole(['super_admin', 'admin', 'pegawai']);
    }

    public function create(User $user): bool
    {
        return $user->can('create_rincian::anggaran::belanja') || $user->hasRole(['super_admin', 'admin']);
    }

    public function update(User $user, RincianAnggaranBelanja $record): bool
    {
        return $user->can('update_rincian::anggaran::belanja') || $user->hasRole(['super_admin', 'admin']);
    }

    public function delete(User $user, RincianAnggaranBelanja $record): bool
    {
        return $user->can('delete_rincian::anggaran::belanja') || $user->hasRole(['super_admin', 'admin']);
    }

    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_rincian::anggaran::belanja') || $user->hasRole(['super_admin', 'admin']);
    }
}
