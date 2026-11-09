<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\MasterSubKegiatan;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class MasterSubKegiatanPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:MasterSubKegiatan');
    }

    public function view(AuthUser $authUser, MasterSubKegiatan $masterSubKegiatan): bool
    {
        if (! $authUser->can('View:MasterSubKegiatan')) {
            return false;
        }

        /** @var User $authUser */
        if ($authUser->canViewAllBidangKeuangan() || $authUser->hasRole('admin')) {
            return true;
        }

        // JF: melihat sub kegiatan yang ditugaskan kepada dirinya sendiri atau di bidang tugasnya
        if ($authUser->hasRole('jf') && ! $authUser->hasRole('pptk')) {
            $userBidangId = (int) ($authUser->bidang_id ?: (\App\Models\Bidang::where('kepala_id', $authUser->id)->value('id') ?? 0));
            return (int) $masterSubKegiatan->jf_id === (int) $authUser->id
                || ($userBidangId > 0 && (int) $masterSubKegiatan->kegiatan?->bidang_id === $userBidangId && (is_null($masterSubKegiatan->jf_id) || (int) $masterSubKegiatan->jf_id === (int) $authUser->id));
        }

        // PPTK (Kabid): Melihat seluruh sub kegiatan di bidangnya
        $userBidangId = (int) ($authUser->bidang_id ?: (\App\Models\Bidang::where('kepala_id', $authUser->id)->value('id') ?? 0));
        if ($userBidangId > 0) {
            return (int) $masterSubKegiatan->kegiatan?->bidang_id === $userBidangId;
        }

        return (int) $masterSubKegiatan->jf_id === (int) $authUser->id;
    }

    public function create(AuthUser $authUser): bool
    {
        // JF tidak membuat sub kegiatan baru (Kabid/PPTK yang membagi tugas)
        /** @var User $authUser */
        if ($authUser->hasRole('jf') && ! $authUser->hasRole('pptk') && ! $authUser->canViewAllBidangKeuangan() && ! $authUser->hasRole('admin')) {
            return false;
        }

        return $authUser->can('Create:MasterSubKegiatan');
    }

    public function update(AuthUser $authUser, MasterSubKegiatan $masterSubKegiatan): bool
    {
        if (! $authUser->can('Update:MasterSubKegiatan')) {
            return false;
        }

        /** @var User $authUser */
        if ($authUser->canViewAllBidangKeuangan() || $authUser->hasRole('admin')) {
            return true;
        }

        // JF: mengelola sub kegiatan yang ditugaskan kepada dirinya atau di bidang tugasnya
        if ($authUser->hasRole('jf') && ! $authUser->hasRole('pptk')) {
            $userBidangId = (int) ($authUser->bidang_id ?: (\App\Models\Bidang::where('kepala_id', $authUser->id)->value('id') ?? 0));
            return (int) $masterSubKegiatan->jf_id === (int) $authUser->id
                || ($userBidangId > 0 && (int) $masterSubKegiatan->kegiatan?->bidang_id === $userBidangId && (is_null($masterSubKegiatan->jf_id) || (int) $masterSubKegiatan->jf_id === (int) $authUser->id));
        }

        // PPTK (Kabid): di bidangnya
        $userBidangId = (int) ($authUser->bidang_id ?: (\App\Models\Bidang::where('kepala_id', $authUser->id)->value('id') ?? 0));
        if ($userBidangId > 0) {
            return (int) $masterSubKegiatan->kegiatan?->bidang_id === $userBidangId;
        }

        return false;
    }

    public function delete(AuthUser $authUser, MasterSubKegiatan $masterSubKegiatan): bool
    {
        // JF dilarang menghapus sub kegiatan
        /** @var User $authUser */
        if ($authUser->hasRole('jf') && ! $authUser->hasRole('pptk') && ! $authUser->canViewAllBidangKeuangan() && ! $authUser->hasRole('admin')) {
            return false;
        }

        if (! $authUser->can('Delete:MasterSubKegiatan')) {
            return false;
        }

        if ($authUser->canViewAllBidangKeuangan() || $authUser->hasRole('admin')) {
            return true;
        }

        $userBidangId = (int) ($authUser->bidang_id ?: (\App\Models\Bidang::where('kepala_id', $authUser->id)->value('id') ?? 0));
        return (int) $masterSubKegiatan->kegiatan?->bidang_id === $userBidangId;
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        /** @var User $authUser */
        if ($authUser->hasRole('jf') && ! $authUser->hasRole('pptk') && ! $authUser->canViewAllBidangKeuangan() && ! $authUser->hasRole('admin')) {
            return false;
        }

        return $authUser->can('DeleteAny:MasterSubKegiatan');
    }
}