<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\MasterTujuanBbm;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class MasterTujuanBbmPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return true; // Data global, dapat diakses oleh semua pengguna
    }

    public function view(AuthUser $authUser, MasterTujuanBbm $masterTujuanBbm): bool
    {
        return true; // Data global, dapat diakses oleh semua pengguna
    }

    public function create(AuthUser $authUser): bool
    {
        return true; // Dapat ditambahkan langsung di form pengajuan oleh siapa saja
    }

    public function update(AuthUser $authUser, MasterTujuanBbm $masterTujuanBbm): bool
    {
        return true;
    }

    public function delete(AuthUser $authUser, MasterTujuanBbm $masterTujuanBbm): bool
    {
        return $authUser->hasAnyRole(['super_admin', 'admin', 'ppk', 'pa']);
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->hasAnyRole(['super_admin', 'admin', 'ppk', 'pa']);
    }
}
