<?php

namespace App\Livewire\Mobile;

use App\Models\LhkPekerjaan;
use App\Models\PengajuanIzin;
use App\Models\PengajuanSpj;
use App\Models\PengaturanAplikasi;
use App\Models\SuratTugas;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.mobile')]
#[Title('Persetujuan Cepat')]
class Persetujuan extends Component
{
    public string $tab = 'izin';
    public bool $hasExplicitTab = false;

    public function mount(): void
    {
        $requestedTab = (string) request()->query('tab', '');
        if (in_array($requestedTab, ['izin', 'lhk', 'spj', 'surat_tugas'], true)) {
            $this->tab = $requestedTab;
            $this->hasExplicitTab = true;
        }
    }

    public function setTab(string $tab): void
    {
        $this->hasExplicitTab = true;
        $this->tab = in_array($tab, ['izin', 'lhk', 'spj', 'surat_tugas'], true) ? $tab : 'izin';
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

        $izin->user?->sendWebPush('Izin Disetujui', 'Pengajuan izin Anda telah disetujui dan tersinkron ke absensi.', route('mobile.dashboard'));

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

        $izin->user?->sendWebPush('Izin Ditolak', 'Pengajuan izin Anda ditolak melalui persetujuan cepat.', route('mobile.dashboard'));

        $this->dispatch('notify', [
            'type' => 'warning',
            'title' => 'Izin Ditolak',
            'message' => 'Status pengajuan izin diperbarui menjadi ditolak.',
        ]);
    }

    public function approveLhk(int $id): void
    {
        if (! Schema::hasColumn('lhk_pekerjaans', 'status_verifikasi')) {
            $this->dispatch('notify', [
                'type' => 'warning',
                'title' => 'Migrasi Diperlukan',
                'message' => 'Kolom status_verifikasi belum tersedia. Jalankan migrasi database terlebih dahulu.',
            ]);
            return;
        }

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
        if (! Schema::hasColumn('lhk_pekerjaans', 'status_verifikasi')) {
            $this->dispatch('notify', [
                'type' => 'warning',
                'title' => 'Migrasi Diperlukan',
                'message' => 'Kolom status_verifikasi belum tersedia. Jalankan migrasi database terlebih dahulu.',
            ]);
            return;
        }

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

        $spj->user?->sendWebPush('SPJ Dikembalikan', "Pengajuan SPJ {$spj->nomor_pengajuan} dikembalikan untuk revisi.", route('mobile.dashboard'));
    }

    public function approveSuratTugas(int $id): void
    {
        /** @var User|null $user */
        $user = Auth::user();
        $suratTugas = SuratTugas::find($id);

        if (! $suratTugas || $suratTugas->status !== 'draft') {
            return;
        }

        $canApprove = $user !== null && (
            $suratTugas->penandatangan_id === $user->id
            || $user->canSignSuratTugas()
            || $user->isKepalaBidang()
        );

        if (! $canApprove) {
            $this->dispatch('notify', [
                'type' => 'error',
                'title' => 'Akses Ditolak',
                'message' => 'Anda tidak memiliki wewenang menyetujui Surat Tugas ini.',
            ]);
            return;
        }

        $suratTugas->tandatanganiDiSakti($suratTugas->penandatangan_id ?: $user->id);

        $this->dispatch('notify', [
            'type' => 'success',
            'title' => 'Surat Tugas Diterbitkan',
            'message' => "Surat Tugas {$suratTugas->nomor_surat} berhasil disetujui & diterbitkan.",
        ]);
    }

    public function render()
    {
        /** @var User|null $user */
        $user = Auth::user();
        $isGlobalApprover = $user?->hasAnyRole(['super_admin', 'admin', 'admin_kepegawaian', 'pa', 'pimpinan']) ?? false;

        $izinEnabled = PengaturanAplikasi::isModuleEnabled('izin');
        $lhkEnabled = PengaturanAplikasi::isModuleEnabled('lhk');
        $spjEnabled = PengaturanAplikasi::isModuleEnabled('spj');
        $stEnabled = PengaturanAplikasi::isModuleEnabled('surat_tugas');

        $izinList = $izinEnabled
            ? PengajuanIzin::with(['user', 'jenisIzin'])
                ->where('status', 'menunggu')
                ->when(! $isGlobalApprover && $user, fn ($q) => $q->whereHas('user', fn ($u) => $u->where('atasan_id', $user->id)))
                ->latest()
                ->limit(25)
                ->get()
            : collect();

        $hasStatusVerifikasi = $lhkEnabled && Schema::hasColumn('lhk_pekerjaans', 'status_verifikasi');
        $lhkList = $hasStatusVerifikasi
            ? LhkPekerjaan::with(['lhk.user', 'aktivitas'])
                ->where('status_verifikasi', 'menunggu')
                ->when(! $isGlobalApprover && $user, fn ($q) => $q->whereHas('lhk.user', fn ($u) => $u->where('atasan_id', $user->id)))
                ->latest()
                ->limit(25)
                ->get()
            : collect();

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

        $spjList = (! $spjEnabled || empty($spjStatuses))
            ? collect()
            : PengajuanSpj::with(['user', 'bidang'])
                ->whereIn('status', array_unique($spjStatuses))
                ->latest()
                ->limit(25)
                ->get();

        $canSeeSuratTugas = $stEnabled && $user !== null && ($user->canSignSuratTugas() || $user->isKepalaBidang());
        $suratTugasList = ($stEnabled && ($canSeeSuratTugas || $user))
            ? SuratTugas::with(['creator', 'penandatangan', 'pegawais'])
                ->where('status', 'draft')
                ->when(
                    ! ($user?->hasAnyRole(['super_admin', 'admin', 'pa']) ?? false) && $user,
                    fn ($q) => $q->where(function ($sub) use ($user): void {
                        $sub->where('penandatangan_id', $user->id);
                        if ($user->canSignSuratTugas() || $user->isKepalaBidang()) {
                            $sub->orWhereNull('penandatangan_id');
                        }
                    })
                )
                ->latest()
                ->limit(25)
                ->get()
            : collect();

        if (! $this->hasExplicitTab && $this->tab === 'izin' && $izinList->isEmpty()) {
            if ($lhkList->isNotEmpty()) {
                $this->tab = 'lhk';
            } elseif ($spjList->isNotEmpty()) {
                $this->tab = 'spj';
            } elseif ($suratTugasList->isNotEmpty()) {
                $this->tab = 'surat_tugas';
            }
        }

        if (! request()->query('tab')) {
            if ($this->tab === 'izin' && $izinList->isEmpty()) {
                if ($suratTugasList->isNotEmpty()) {
                    $this->tab = 'surat_tugas';
                } elseif ($lhkList->isNotEmpty()) {
                    $this->tab = 'lhk';
                } elseif ($spjList->isNotEmpty()) {
                    $this->tab = 'spj';
                }
            }
        }

        return view('livewire.mobile.persetujuan', [
            'izinList' => $izinList,
            'lhkList' => $lhkList,
            'spjList' => $spjList,
            'suratTugasList' => $suratTugasList,
            'izinEnabled' => $izinEnabled,
            'lhkEnabled' => $lhkEnabled,
            'spjEnabled' => $spjEnabled,
            'stEnabled' => $stEnabled,
        ]);
    }
}

