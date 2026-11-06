<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class ActivityLogPolicy
{
    use HandlesAuthorization;

    /**
     * Tentukan apakah user dapat melihat daftar Log Aktivitas.
     * Hanya Superadmin, Kasubag Keuangan (Admin Dinas), PPK, dan PA (Kadis).
     */
    public function viewAny(AuthUser $authUser): bool
    {
        /** @var User $user */
        $user = $authUser;

        $anggaranRole = $user->anggaran_role instanceof \App\Enums\AnggaranRole
            ? $user->anggaran_role->value
            : $user->anggaran_role;

        return $user->hasAnyRole(['super_admin', 'admin', 'ppk', 'pa'])
            || in_array($anggaranRole, ['PPK', 'PA'], true);
    }

    public function view(AuthUser $authUser, ActivityLog $activityLog): bool
    {
        return $this->viewAny($authUser);
    }

    public function create(AuthUser $authUser): bool
    {
        return false;
    }

    public function update(AuthUser $authUser, ActivityLog $activityLog): bool
    {
        return false;
    }

    public function delete(AuthUser $authUser, ActivityLog $activityLog): bool
    {
        return false;
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return false;
    }
}
