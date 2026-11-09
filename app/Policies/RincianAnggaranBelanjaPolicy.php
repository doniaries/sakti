<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\RincianAnggaranBelanja;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class RincianAnggaranBelanjaPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:RincianAnggaranBelanja');
    }

    public function view(AuthUser $authUser, RincianAnggaranBelanja $rincianAnggaranBelanja): bool
    {
        if (! $authUser->can('View:RincianAnggaranBelanja')) {
            return false;
        }

        /** @var User $authUser */
        if ($authUser->canViewAllBidangKeuangan() || $authUser->hasRole('admin')) {
            return true;
        }

        // JF: melihat item di sub kegiatannya sendiri atau di bidang tugasnya
        if ($authUser->hasRole('jf') && ! $authUser->hasRole('pptk')) {
            $userBidangId = (int) ($authUser->bidang_id ?: (\App\Models\Bidang::where('kepala_id', $authUser->id)->value('id') ?? 0));
            $subKeg = $rincianAnggaranBelanja->subKegiatan;
            return (int) $subKeg?->jf_id === (int) $authUser->id
                || ($userBidangId > 0 && (int) $rincianAnggaranBelanja->bidang_id === $userBidangId && (is_null($subKeg?->jf_id) || (int) $subKeg?->jf_id === (int) $authUser->id));
        }

        // PPTK (Kabid): di bidangnya
        $userBidangId = (int) ($authUser->bidang_id ?: (\App\Models\Bidang::where('kepala_id', $authUser->id)->value('id') ?? 0));
        if ($userBidangId > 0) {
            return (int) $rincianAnggaranBelanja->bidang_id === $userBidangId;
        }

        return false;
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:RincianAnggaranBelanja');
    }

    public function update(AuthUser $authUser, RincianAnggaranBelanja $rincianAnggaranBelanja): bool
    {
        if (! $authUser->can('Update:RincianAnggaranBelanja')) {
            return false;
        }

        /** @var User $authUser */
        if ($authUser->canViewAllBidangKeuangan() || $authUser->hasRole('admin')) {
            return true;
        }

        // JF: mengelola item di sub kegiatannya atau di bidang tugasnya
        if ($authUser->hasRole('jf') && ! $authUser->hasRole('pptk')) {
            $userBidangId = (int) ($authUser->bidang_id ?: (\App\Models\Bidang::where('kepala_id', $authUser->id)->value('id') ?? 0));
            $subKeg = $rincianAnggaranBelanja->subKegiatan;
            return (int) $subKeg?->jf_id === (int) $authUser->id
                || ($userBidangId > 0 && (int) $rincianAnggaranBelanja->bidang_id === $userBidangId && (is_null($subKeg?->jf_id) || (int) $subKeg?->jf_id === (int) $authUser->id));
        }

        // PPTK: di bidangnya
        $userBidangId = (int) ($authUser->bidang_id ?: (\App\Models\Bidang::where('kepala_id', $authUser->id)->value('id') ?? 0));
        if ($userBidangId > 0) {
            return (int) $rincianAnggaranBelanja->bidang_id === $userBidangId;
        }

        return false;
    }

    public function delete(AuthUser $authUser, RincianAnggaranBelanja $rincianAnggaranBelanja): bool
    {
        if (! $authUser->can('Delete:RincianAnggaranBelanja')) {
            return false;
        }

        // Tidak boleh menghapus item yang sudah ada realisasi SPJ
        if ((float) $rincianAnggaranBelanja->nilai_realisasi > 0) {
            return false;
        }

        /** @var User $authUser */
        if ($authUser->canViewAllBidangKeuangan() || $authUser->hasRole('admin')) {
            return true;
        }

        // JF: hanya di sub kegiatan tugasnya
        if ($authUser->hasRole('jf') && ! $authUser->hasRole('pptk')) {
            return (int) $rincianAnggaranBelanja->subKegiatan?->jf_id === (int) $authUser->id;
        }

        // PPTK: di bidangnya
        $userBidangId = (int) ($authUser->bidang_id ?: (\App\Models\Bidang::where('kepala_id', $authUser->id)->value('id') ?? 0));
        if ($userBidangId > 0) {
            return (int) $rincianAnggaranBelanja->bidang_id === $userBidangId;
        }

        return false;
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:RincianAnggaranBelanja');
    }
}