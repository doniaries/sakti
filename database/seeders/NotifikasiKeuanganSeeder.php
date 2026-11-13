<?php

namespace Database\Seeders;

use App\Filament\Resources\PengajuanSpjs\PengajuanSpjResource;
use App\Models\PengajuanSpj;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class NotifikasiKeuanganSeeder extends Seeder
{
    /**
     * Menyinkronkan notifikasi database Keuangan (Pengajuan SPJ) agar konsisten
     * dengan status PengajuanSpj yang ada di database (idempoten, tanpa duplikasi).
     */
    public function run(): void
    {
        $spjs = PengajuanSpj::with(['user', 'pptk'])->where('status', '!=', 'draft')->get();

        if ($spjs->isEmpty()) {
            $this->command?->warn('Belum ada data PengajuanSpj. Jalankan PengajuanSpjSeeder terlebih dahulu.');
            return;
        }

        $superAdmins = User::query()
            ->where('aktif', true)
            ->whereHas('roles', fn ($q) => $q->where('name', 'super_admin'))
            ->get();

        $sentCount = 0;

        foreach ($spjs as $spj) {
            $url = PengajuanSpjResource::getUrl('index', ['tableSearch' => $spj->nomor_pengajuan]);
            $pemohon = $spj->user?->name ?? 'Pegawai';
            $nomor = $spj->nomor_pengajuan ?? ('#' . $spj->id);

            // 1. Notifikasi untuk pihak verifikator / pencairan sesuai tahap SPJ
            $roleTargets = match ($spj->status) {
                'diajukan', 'menunggu_verifikasi_pptk' => $spj->pptk_id
                    ? User::where('id', $spj->pptk_id)->get()
                    : User::role('pptk')->when($spj->bidang_id, fn ($q) => $q->where('bidang_id', $spj->bidang_id))->get(),
                'menunggu_verifikasi_ppk' => User::role('ppk')->get(),
                'menunggu_persetujuan_pa', 'menunggu_verifikasi_pa' => User::role('pa')->get(),
                'menunggu_verifikasi_bendahara', 'menunggu_pencairan', 'disetujui' => User::role('bendahara')->get(),
                default => collect(),
            };

            $targets = $roleTargets->isNotEmpty()
                ? $roleTargets->merge($superAdmins)->unique('id')->reject(fn (User $u) => $u->id === $spj->user_id)
                : collect();

            foreach ($targets as $target) {
                if ($this->notificationAlreadyExists($target->id, "Pengajuan SPJ: {$nomor}", "Status: {$spj->status_label}")) {
                    continue;
                }

                Notification::make()
                    ->title("Pengajuan SPJ: {$nomor}")
                    ->body("Status: {$spj->status_label} • Pemohon: {$pemohon}")
                    ->icon('heroicon-o-document-text')
                    ->color($spj->status_color)
                    ->actions([
                        Action::make('lihat')
                            ->label('Lihat SPJ')
                            ->url($url)
                            ->markAsRead(),
                    ])
                    ->sendToDatabase($target);

                $sentCount++;
            }

            // 2. Notifikasi progres untuk pemohon SPJ pada status lanjutan / selesai / revisi
            $isProgressStatus = in_array($spj->status, [
                'menunggu_realisasi',
                'revisi_pptk',
                'revisi_ppk',
                'revisi_pa',
                'revisi_bendahara',
                'disetujui',
                'dicairkan',
                'ditolak',
            ], true);

            if ($spj->user && $isProgressStatus) {
                $title = "Progres SPJ {$nomor}";
                $body = "Status diperbarui menjadi: {$spj->status_label}";

                if (! $this->notificationAlreadyExists($spj->user->id, $title, $body)) {
                    $isRevisi = str_contains((string) $spj->status, 'revisi') || $spj->status === 'ditolak';

                    Notification::make()
                        ->title($title)
                        ->body($body)
                        ->icon($isRevisi ? 'heroicon-o-exclamation-triangle' : 'heroicon-o-check-circle')
                        ->color($spj->status_color)
                        ->actions([
                            Action::make('lihat')
                                ->label('Buka')
                                ->url($url)
                                ->markAsRead(),
                        ])
                        ->sendToDatabase($spj->user);

                    $sentCount++;
                }
            }
        }

        $this->command?->info("Seeder Notifikasi Keuangan selesai: {$sentCount} notifikasi disinkronkan dari {$spjs->count()} Pengajuan SPJ.");
    }

    protected function notificationAlreadyExists(int $userId, string $title, string $bodyPrefix): bool
    {
        return DB::table('notifications')
            ->where('notifiable_type', User::class)
            ->where('notifiable_id', $userId)
            ->where('data', 'like', '%' . $title . '%')
            ->where('data', 'like', '%' . $bodyPrefix . '%')
            ->exists();
    }
}

