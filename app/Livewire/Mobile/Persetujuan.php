<?php

namespace App\Livewire\Mobile;

use App\Models\LhkPekerjaan;
use App\Models\PengajuanIzin;
use App\Models\PengajuanSpj;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.mobile')]
#[Title('Persetujuan Cepat')]
class Persetujuan extends Component
{
    public string $tab = 'izin';

    public function setTab(string $tab): void
    {
        $this->tab = in_array($tab, ['izin', 'lhk', 'spj'], true) ? $tab : 'izin';
    }

    public function approveIzin(int $id): void
    {
        $izin = PengajuanIzin::find($id);
        if (! $izin || $izin->status !== 'menunggu') {
            return;
        }

        $izin->update([
            'status' => 'disetujui',
            'disetujui_oleh' => Auth::id(),
        ]);

        $this->dispatch('notify', [
            'type' => 'success',
            'title' => 'Izin Disetujui',
            'message' => 'Pengajuan izin disetujui dan otomatis tersinkron ke absensi.',
        ]);
    }

    public function rejectIzin(int $id): void
    {
        $izin = PengajuanIzin::find($id);
        if (! $izin || $izin->status !== 'menunggu') {
            return;
        }

        $izin->update([
            'status' => 'ditolak',
            'disetujui_oleh' => Auth::id(),
            'catatan_atasan' => 'Ditolak melalui persetujuan cepat mobile.',
        ]);

        $this->dispatch('notify', [
            'type' => 'warning',
            'title' => 'Izin Ditolak',
            'message' => 'Status pengajuan izin diperbarui menjadi ditolak.',
        ]);
    }

    public function approveLhk(int $id): void
    {
        $pekerjaan = LhkPekerjaan::find($id);
        if (! $pekerjaan) {
            return;
        }

        $pekerjaan->update(['status_verifikasi' => 'disetujui']);

        $this->dispatch('notify', [
            'type' => 'success',
            'title' => 'LHK Disetujui',
            'message' => 'Kegiatan LHK pegawai telah diverifikasi.',
        ]);
    }

    public function rejectLhk(int $id): void
    {
        $pekerjaan = LhkPekerjaan::find($id);
        if (! $pekerjaan) {
            return;
        }

        $pekerjaan->update(['status_verifikasi' => 'ditolak']);

        $this->dispatch('notify', [
            'type' => 'warning',
            'title' => 'LHK Ditolak',
            'message' => 'Kegiatan LHK ditandai ditolak.',
        ]);
    }

    public function approveSpj(int $id): void
    {
        $spj = PengajuanSpj::find($id);
        if (! $spj) {
            return;
        }

        $nextStatus = match ($spj->status) {
            'diajukan' => 'menunggu_realisasi',
            'menunggu_verifikasi_pptk' => 'menunggu_verifikasi_ppk',
            'menunggu_verifikasi_ppk' => 'menunggu_persetujuan_pa',
            'menunggu_persetujuan_pa' => 'menunggu_verifikasi_bendahara',
            'menunggu_verifikasi_bendahara' => 'disetujui',
            default => null,
        };

        if (! $nextStatus) {
            return;
        }

        $spj->update(['status' => $nextStatus]);

        $this->dispatch('notify', [
            'type' => 'success',
            'title' => 'SPJ Disetujui',
            'message' => "SPJ {$spj->nomor_pengajuan} dilanjutkan ke tahap berikutnya.",
        ]);
    }

    public function revisiSpj(int $id): void
    {
        $spj = PengajuanSpj::find($id);
        if (! $spj) {
            return;
        }

        $revisiStatus = match ($spj->status) {
            'diajukan', 'menunggu_verifikasi_pptk' => 'revisi_pptk',
            'menunggu_verifikasi_ppk' => 'revisi_ppk',
            'menunggu_persetujuan_pa' => 'revisi_pa',
            'menunggu_verifikasi_bendahara' => 'revisi_bendahara',
            default => null,
        };

        if (! $revisiStatus) {
            return;
        }

        $spj->update([
            'status' => $revisiStatus,
            'catatan_revisi' => 'Perlu revisi (dikirim via persetujuan cepat mobile).',
        ]);

        $this->dispatch('notify', [
            'type' => 'warning',
            'title' => 'SPJ Dikembalikan',
            'message' => "SPJ {$spj->nomor_pengajuan} dikembalikan untuk revisi.",
        ]);
    }

    public function render()
    {
        /** @var User|null $user */
        $user = Auth::user();
        $isGlobalApprover = $user?->hasAnyRole(['super_admin', 'admin', 'admin_kepegawaian', 'pa', 'pimpinan']) ?? false;

        $izinList = PengajuanIzin::with(['user', 'jenisIzin'])
            ->where('status', 'menunggu')
            ->when(! $isGlobalApprover && $user, fn ($q) => $q->whereHas('user', fn ($u) => $u->where('atasan_id', $user->id)))
            ->latest()
            ->limit(25)
            ->get();

        $lhkList = LhkPekerjaan::with(['lhk.user', 'aktivitas'])
            ->where('status_verifikasi', 'menunggu')
            ->when(! $isGlobalApprover && $user, fn ($q) => $q->whereHas('lhk.user', fn ($u) => $u->where('atasan_id', $user->id)))
            ->latest()
            ->limit(25)
            ->get();

        $spjStatuses = [];
        if ($user?->hasRole('super_admin')) {
            $spjStatuses = ['diajukan', 'menunggu_verifikasi_pptk', 'menunggu_verifikasi_ppk', 'menunggu_persetujuan_pa', 'menunggu_verifikasi_bendahara'];
        } else {
            if ($user?->hasRole('pptk')) {
                $spjStatuses = array_merge($spjStatuses, ['diajukan', 'menunggu_verifikasi_pptk']);
            }
            if ($user?->hasRole('ppk')) {
                $spjStatuses[] = 'menunggu_verifikasi_ppk';
            }
            if ($user?->hasRole('pa')) {
                $spjStatuses[] = 'menunggu_persetujuan_pa';
            }
            if ($user?->hasRole('bendahara')) {
                $spjStatuses[] = 'menunggu_verifikasi_bendahara';
            }
        }

        $spjList = empty($spjStatuses)
            ? collect()
            : PengajuanSpj::with(['user', 'bidang'])
                ->whereIn('status', array_unique($spjStatuses))
                ->latest()
                ->limit(25)
                ->get();

        return view('livewire.mobile.persetujuan', [
            'izinList' => $izinList,
            'lhkList' => $lhkList,
            'spjList' => $spjList,
        ]);
    }
}

