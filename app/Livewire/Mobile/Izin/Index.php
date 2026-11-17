<?php

namespace App\Livewire\Mobile\Izin;

use App\Models\PengajuanIzin;
use App\Models\User;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.mobile')]
#[Title('Pengajuan Izin & Cuti - Absensi LHK Mobile')]
class Index extends Component
{
    public string $activeTab = 'saya'; // 'saya' atau 'verifikasi'
    public string $statusFilter = 'semua'; // 'semua', 'menunggu', 'disetujui', 'ditolak'

    public ?PengajuanIzin $selectedPengajuan = null;
    public bool $showDetailModal = false;

    public string $catatanAksi = '';
    public ?int $targetAksiId = null;
    public string $tipeAksi = ''; // 'setujui' atau 'tolak'
    public bool $showKonfirmasiModal = false;

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
        $this->statusFilter = 'semua';
    }

    public function setFilter(string $status): void
    {
        $this->statusFilter = $status;
    }

    public function bukaDetail(int $id): void
    {
        $this->selectedPengajuan = PengajuanIzin::with(['jenisIzin', 'user.jabatan', 'approver'])->find($id);
        $this->showDetailModal = true;
    }

    public function tutupDetail(): void
    {
        $this->showDetailModal = false;
        $this->selectedPengajuan = null;
    }

    public function bukaModalAksi(int $id, string $tipe): void
    {
        $this->targetAksiId = $id;
        $this->tipeAksi = $tipe;
        $this->catatanAksi = '';
        $this->showKonfirmasiModal = true;
    }

    public function tutupModalAksi(): void
    {
        $this->showKonfirmasiModal = false;
        $this->targetAksiId = null;
        $this->tipeAksi = '';
        $this->catatanAksi = '';
    }

    public function eksekusiAksi(): void
    {
        if (! $this->targetAksiId || ! in_array($this->tipeAksi, ['setujui', 'tolak'])) {
            return;
        }

        /** @var User $currentUser */
        $currentUser = Auth::user();

        $pengajuan = PengajuanIzin::with(['jenisIzin', 'user'])->find($this->targetAksiId);
        if (! $pengajuan) {
            $this->tutupModalAksi();
        $this->tutupDetail();
            return;
        }

        // Validasi otoritas: hanya atasan langsung atau superadmin/admin/pimpinan
        $isApprover = $pengajuan->user?->atasan_id === $currentUser->id
            || $currentUser->hasAnyRole(['super_admin', 'admin', 'pa', 'ppk', 'pptk']);

        if (! $isApprover) {
            $this->dispatch('notify', [
                'type' => 'error',
                'title' => 'Akses Ditolak',
                'message' => 'Anda tidak memiliki hak untuk memverifikasi pengajuan ini.',
            ]);
            $this->tutupModalAksi();
        $this->tutupDetail();
            return;
        }

        $statusBaru = ($this->tipeAksi === 'setujui') ? 'disetujui' : 'ditolak';

        $pengajuan->update([
            'status' => $statusBaru,
            'disetujui_oleh' => $currentUser->id,
            'catatan_atasan' => ! empty($this->catatanAksi) ? $this->catatanAksi : null,
        ]);

        // Sinkronisasi otomatis ke AbsensiHarian
        if ($statusBaru === 'disetujui') {
            $pengajuan->sinkronkanKeAbsensiHarian();
        }

        // Kirim Notifikasi ke Pemohon
        try {
            if ($pengajuan->user) {
                Notification::make()
                    ->title("Pengajuan Izin {$statusBaru}!")
                    ->body("Pengajuan {$pengajuan->jenisIzin?->nama} Anda telah {$statusBaru} oleh {$currentUser->name}.")
                    ->color($statusBaru === 'disetujui' ? 'success' : 'danger')
                    ->sendToDatabase($pengajuan->user);
            }
        } catch (\Throwable $e) {
            // Abaikan error notifikasi
        }

        $this->tutupModalAksi();
        $this->tutupDetail();

        $this->dispatch('notify', [
            'type' => $statusBaru === 'disetujui' ? 'success' : 'info',
            'title' => 'Verifikasi Berhasil',
            'message' => "Pengajuan pegawai {$pengajuan->user?->name} telah berhasil {$statusBaru}.",
        ]);
    }

    public function batalkanPengajuan(int $id): void
    {
        $pengajuan = PengajuanIzin::where('user_id', Auth::id())->where('id', $id)->first();
        if (! $pengajuan) {
            return;
        }

        // Aturan Keras: Data yang sudah diproses atau disetujui TIDAK BISA DIHAPUS
        if ($pengajuan->status !== 'menunggu') {
            $this->dispatch('notify', [
                'type' => 'error',
                'title' => 'Tidak Dapat Dihapus',
                'message' => 'Pengajuan yang sudah diproses atau disetujui tidak dapat dihapus kembali.',
            ]);
            return;
        }

        $pengajuan->delete();

        $this->dispatch('notify', [
            'type' => 'success',
            'title' => 'Pengajuan Dibatalkan',
            'message' => 'Pengajuan izin telah berhasil dibatalkan dan dihapus.',
        ]);
    }

    public function render()
    {
        /** @var User $user */
        $user = Auth::user();

        // Tentukan apakah user merupakan pimpinan / atasan
        $isPimpinan = $user->bawahan()->exists()
            || $user->hasAnyRole(['super_admin', 'admin', 'pa', 'ppk', 'pptk']);

        // Hitung pengajuan bawahan yang menunggu
        $bawahanIds = $user->bawahan()->pluck('id')->toArray();
        $queryMenungguBawahan = PengajuanIzin::where('status', 'menunggu');

        if (! $user->hasAnyRole(['super_admin', 'admin', 'pa'])) {
            $queryMenungguBawahan->whereIn('user_id', $bawahanIds);
        }
        $countMenungguBawahan = $queryMenungguBawahan->count();

        // Query berdasarkan Tab Aktif
        if ($this->activeTab === 'verifikasi' && $isPimpinan) {
            $query = PengajuanIzin::with(['jenisIzin', 'user.jabatan', 'approver'])
                ->latest('created_at');

            if (! $user->hasAnyRole(['super_admin', 'admin', 'pa'])) {
                $query->whereIn('user_id', $bawahanIds);
            }
        } else {
            $query = PengajuanIzin::with(['jenisIzin', 'user', 'approver'])
                ->where('user_id', $user->id)
                ->latest('tanggal_mulai');
        }

        if ($this->statusFilter !== 'semua') {
            $query->where('status', $this->statusFilter);
        }

        $pengajuans = $query->get();

        return view('livewire.mobile.izin.index', [
            'pengajuans' => $pengajuans,
            'isPimpinan' => $isPimpinan,
            'countMenungguBawahan' => $countMenungguBawahan,
        ]);
    }
}
