<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\PengajuanIzin;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

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
        // Aturan Keras: Data yang sudah diproses atau disetujui TIDAK DAPAT diubah kembali
        if ($pengajuanIzin->status !== 'menunggu') {
            return false;
        }

        /** @var User $authUser */
        if ($authUser->hasRole('pegawai') && $pengajuanIzin->user_id !== $authUser->id) {
            return false;
        }

        return $authUser->can('Update:PengajuanIzin');
    }

    public function delete(AuthUser $authUser, PengajuanIzin $pengajuanIzin): bool
    {
        // Aturan Keras: Data yang sudah diproses atau disetujui TIDAK DAPAT dihapus kembali
        if ($pengajuanIzin->status !== 'menunggu') {
            return false;
        }

        /** @var User $authUser */
        if ($authUser->hasRole('pegawai') && $pengajuanIzin->user_id !== $authUser->id) {
            return false;
        }

        return $authUser->can('Delete:PengajuanIzin');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        /** @var User $authUser */
        if ($authUser->hasRole('pegawai')) {
            return false;
        }

        return $authUser->can('DeleteAny:PengajuanIzin');
    }
}
