<?php

namespace App\Livewire\Mobile\Lhk;

use App\Models\Aktivitas;
use App\Models\RencanaAksi;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.mobile')]
#[Title('Bank Aktivitas Saya - Absensi LHK Mobile')]
class BankAktivitas extends Component
{
    public string $search = '';
    public string $filterSubKegiatan = 'semua';
    public string $selectedSubKegiatan = '';
    public ?int $editingId = null;
    public ?int $rencana_aksi_id = null;
    public string $nama_aktivitas = '';
    public int $menit = 30;
    public string $tipe = 'khusus';

    public bool $showFormModal = false;
    public bool $showBulkModal = false;
    public string $bulkRaw = '';
    public int $bulkDefaultMenit = 30;

    public function updatedFilterSubKegiatan(): void
    {
        $this->selectedSubKegiatan = '';
    }

    public function openCreate(): void
    {
        $this->resetValidation();
        $this->editingId = null;
        $this->rencana_aksi_id = null;
        $this->nama_aktivitas = '';
        $this->menit = 30;
        $this->tipe = 'khusus';
        $this->showFormModal = true;
    }

    public function openEdit(int $id): void
    {
        /** @var User|null $user */
        $user = Auth::user();
        $item = ($user && $user->hasAnyRole(['super_admin', 'admin']))
            ? Aktivitas::find($id)
            : Aktivitas::where('user_id', Auth::id())->find($id);
        if (! $item) {
            return;
        }

        $this->resetValidation();
        $this->editingId = $item->id;
        $this->kode = (string) ($item->kode ?? '');
        $this->nama_aktivitas = (string) $item->nama_aktivitas;
        $this->menit = (int) $item->menit;
        $this->tipe = is_null($item->user_id) && is_null($item->bidang_id) ? 'umum' : 'khusus';
        $this->showFormModal = true;
    }

    public function closeForm(): void
    {
        $this->showFormModal = false;
    }

    public function saveItem(): void
    {
        $this->validate([
            'kode' => 'nullable|string|max:50',
            'nama_aktivitas' => 'required|string|min:3|max:1000',
            'menit' => 'required|integer|min:1|max:1440',
        ], [
            'nama_aktivitas.required' => 'Nama aktivitas wajib diisi.',
            'menit.required' => 'Durasi menit wajib diisi.',
        ]);

        /** @var User $user */
        $user = Auth::user();
        $bidangId = $user->bidang_id ?: \App\Models\Bidang::where('kepala_id', $user->id)->value('id');
        $targetUserId = ($this->tipe === 'umum' && $user->hasAnyRole(['super_admin', 'superadmin', 'admin'])) ? null : $user->id;

        if (! is_null($targetUserId) && empty($this->rencana_aksi_id)) {
            $hasSubKegiatan = RencanaAksi::query()
                ->when($bidangId, fn ($q) => $q->where('bidang_id', $bidangId))
                ->whereNotNull('sasaran_sub_kegiatan')
                ->where('sasaran_sub_kegiatan', '!=', '')
                ->exists();

            if ($hasSubKegiatan) {
                $this->addError('rencana_aksi_id', 'Sub Kegiatan / Rencana Aksi di bidang wajib dipilih.');
                return;
            }
        }

        if (Aktivitas::sudahAdaDuplikat($this->nama_aktivitas, $targetUserId, $this->editingId)) {
            $this->addError('nama_aktivitas', 'Aktivitas dengan uraian yang sama sudah ada di Bank Aktivitas (Umum/Global atau milik Anda).');
            return;
        }

        $isGlobal = is_null($targetUserId);
        $payload = [
            'user_id' => $targetUserId,
            'jabatan_id' => $isGlobal ? null : $user->jabatan_id,
            'bidang_id' => $isGlobal ? null : $bidangId,
            'rencana_aksi_id' => $isGlobal ? null : ($this->rencana_aksi_id ?: null),
            'nama_aktivitas' => trim($this->nama_aktivitas),
            'menit' => $this->menit,
        ];

        if ($this->editingId) {
            $itemQuery = Aktivitas::query();
            if (! $user->hasAnyRole(['super_admin', 'superadmin', 'admin'])) {
                $itemQuery->where('user_id', $user->id);
            }
            $item = $itemQuery->find($this->editingId);
            if ($item) {
                $item->update([
                    'kode' => $this->kode ?: $item->kode,
                    ...$payload,
                ]);
            }
            $msg = 'Aktivitas berhasil diperbarui.';
        } else {
            Aktivitas::create([
                'kode' => $this->kode ?: (string) (Aktivitas::where('user_id', $targetUserId)->count() + 1),
                ...$payload,
            ]);
            $msg = 'Aktivitas baru berhasil ditambahkan ke Bank Aktivitas.';
        }

        $this->showFormModal = false;
        $this->dispatch('notify', [
            'type' => 'success',
            'title' => 'Berhasil',
            'message' => $msg,
        ]);
    }

    public function deleteItem(int $id): void
    {
        Aktivitas::where('user_id', Auth::id())->where('id', $id)->delete();

        $this->dispatch('notify', [
            'type' => 'success',
            'title' => 'Dihapus',
            'message' => 'Aktivitas telah dihapus dari Bank Aktivitas Anda.',
        ]);
    }

    public function openBulk(): void
    {
        $this->resetValidation();
        $this->bulkRaw = '';
        $this->bulkDefaultMenit = 30;
        $this->showBulkModal = true;
    }

    public function closeBulk(): void
    {
        $this->showBulkModal = false;
    }

    public function importBulk(): void
    {
        $this->validate([
            'bulkRaw' => 'required|string|min:3',
            'bulkDefaultMenit' => 'required|integer|min:1|max:1440',
        ], [
            'bulkRaw.required' => 'Tempelkan minimal 1 baris data aktivitas.',
        ]);

        /** @var User $user */
        $user = Auth::user();
        $items = Aktivitas::parseBulkLines($this->bulkRaw, $this->bulkDefaultMenit);

        if (empty($items)) {
            $this->dispatch('notify', [
                'type' => 'warning',
                'title' => 'Data Tidak Terbaca',
                'message' => 'Pastikan setiap baris berisi uraian nama aktivitas.',
            ]);
            return;
        }

        $nextKode = (int) Aktivitas::where('user_id', $user->id)->count();
        $added = 0;
        $skipped = 0;

        foreach ($items as $row) {
            if (Aktivitas::sudahAdaDuplikat($row['nama_aktivitas'], null)) {
                $skipped++;
                continue;
            }

            $nextKode++;
            $existing = Aktivitas::where('user_id', $user->id)
                ->whereRaw('LOWER(TRIM(nama_aktivitas)) = ?', [mb_strtolower(trim($row['nama_aktivitas']))])
                ->first();

            if ($existing) {
                $existing->update([
                    'kode' => $row['kode'] ?? $existing->kode,
                    'menit' => $row['menit'],
                    'jabatan_id' => $user->jabatan_id,
                    'bidang_id' => $user->bidang_id,
                ]);
            } else {
                Aktivitas::create([
                    'user_id' => $user->id,
                    'jabatan_id' => $user->jabatan_id,
                    'bidang_id' => $user->bidang_id,
                    'kode' => $row['kode'] ?? (string) $nextKode,
                    'nama_aktivitas' => $row['nama_aktivitas'],
                    'menit' => $row['menit'],
                ]);
                $added++;
            }
        }

        $this->showBulkModal = false;
        $this->dispatch('notify', [
            'type' => 'success',
            'title' => 'Impor Berhasil',
            'message' => "{$added} aktivitas baru berhasil disimpan" . ($skipped > 0 ? " ({$skipped} dilewati karena sudah ada di Umum/Global)." : ' ke Bank Aktivitas Anda.'),
        ]);
    }

    public function render()
    {
        /** @var User $user */
        $user = Auth::user();

        $allUserAktivitas = Aktivitas::untukUser($user)
            ->with([
                'rencanaAksi:id,sasaran_sub_kegiatan,rencana_aksi,level',
                'bidang:id,nama',
                'jabatan:id,nama',
            ])
            ->get();

        $subKegiatanList = $allUserAktivitas
            ->filter(fn(Aktivitas $a): bool => (! is_null($a->user_id) || ! is_null($a->jabatan_id))
                && $a->rencanaAksi?->sasaran_sub_kegiatan !== 'Tugas Tambahan')
            ->pluck('rencanaAksi.sasaran_sub_kegiatan')
            ->filter()
            ->countBy()
            ->sortKeys();

        $globalCount = $allUserAktivitas->filter(fn(Aktivitas $a): bool => is_null($a->user_id) && is_null($a->jabatan_id))->count();
        $timKreatifCount = $allUserAktivitas->filter(fn(Aktivitas $a): bool => $a->rencanaAksi?->sasaran_sub_kegiatan === 'Tugas Tambahan')->count();
        $bidangCount = $allUserAktivitas->filter(
            fn(Aktivitas $a): bool => (! is_null($a->user_id) || ! is_null($a->jabatan_id))
                && $a->rencanaAksi?->sasaran_sub_kegiatan !== 'Tugas Tambahan'
        )->count();
        $spesifikCount = $bidangCount + $timKreatifCount;
        $isTimKreatif = (bool) ($user->jabatan_tim_kreatif || $timKreatifCount > 0);

        $searchTerm = mb_strtolower(trim($this->search));
        $myAktivitas = $allUserAktivitas
            ->when($searchTerm !== '', fn($col) => $col->filter(
                fn(Aktivitas $a): bool => str_contains(mb_strtolower((string) $a->nama_aktivitas), $searchTerm)
                    || str_contains(mb_strtolower((string) ($a->rencanaAksi?->sasaran_sub_kegiatan ?? '')), $searchTerm)
            ))
            ->when($this->filterSubKegiatan === 'global', fn($col) => $col->filter(
                fn(Aktivitas $a): bool => is_null($a->user_id) && is_null($a->jabatan_id)
            ))
            ->when($this->filterSubKegiatan === 'bidang', fn($col) => $col->filter(
                fn(Aktivitas $a): bool => (! is_null($a->user_id) || ! is_null($a->jabatan_id))
                    && $a->rencanaAksi?->sasaran_sub_kegiatan !== 'Tugas Tambahan'
            ))
            ->when($this->filterSubKegiatan === 'bidang' && $this->selectedSubKegiatan !== '', fn($col) => $col->filter(
                fn(Aktivitas $a): bool => ($a->rencanaAksi?->sasaran_sub_kegiatan ?? '') === $this->selectedSubKegiatan
            ))
            ->when($this->filterSubKegiatan === 'tim_kreatif', fn($col) => $col->filter(
                fn(Aktivitas $a): bool => $a->rencanaAksi?->sasaran_sub_kegiatan === 'Tugas Tambahan'
            ))
            ->when(! in_array($this->filterSubKegiatan, ['semua', 'global', 'bidang', 'tim_kreatif'], true), fn($col) => $col->filter(
                fn(Aktivitas $a): bool => ($a->rencanaAksi?->sasaran_sub_kegiatan ?? '') === $this->filterSubKegiatan
            ))
            ->values();

        $groupedAktivitas = $myAktivitas->groupBy(function (Aktivitas $a): string {
            if (is_null($a->user_id) && is_null($a->jabatan_id)) {
                return 'Umum (Semua Pegawai)';
            }
            if ($a->rencanaAksi?->sasaran_sub_kegiatan === 'Tugas Tambahan') {
                return 'Tim Kreatif — ' . ($a->rencanaAksi?->rencana_aksi ?: 'Tugas Tambahan');
            }

            $bidangLabel = $a->bidang?->nama ? "{$a->bidang->nama} • " : '';

            return $bidangLabel . ($a->rencanaAksi?->sasaran_sub_kegiatan ?: 'Umum Bidang');
        });

        $bidangId = $user->bidang_id ?: \App\Models\Bidang::where('kepala_id', $user->id)->value('id');
        $bidangNama = $user->bidang?->nama
            ?: ($bidangId ? \App\Models\Bidang::whereKey($bidangId)->value('nama') : null);

        $rencanaAksiList = RencanaAksi::query()
            ->select(['id', 'bidang_id', 'jabatan_id', 'sasaran_sub_kegiatan', 'rencana_aksi', 'level'])
            ->when($bidangId, fn ($q) => $q->where('bidang_id', $bidangId))
            ->whereNotNull('sasaran_sub_kegiatan')
            ->where('sasaran_sub_kegiatan', '!=', '')
            ->orderBy('rencana_aksi')
            ->get();

        $selectedSubKegiatanNama = $this->rencana_aksi_id
            ? $rencanaAksiList->firstWhere('id', (int) $this->rencana_aksi_id)?->sasaran_sub_kegiatan
            : null;

        return view('livewire.mobile.lhk.bank-aktivitas', [
            'myAktivitas' => $myAktivitas,
            'groupedAktivitas' => $groupedAktivitas,
            'globalCount' => $globalCount,
            'bidangCount' => $bidangCount,
            'timKreatifCount' => $timKreatifCount,
            'spesifikCount' => $spesifikCount,
            'isTimKreatif' => $isTimKreatif,
            'userBidangNama' => $bidangNama,
            'subKegiatanList' => $subKegiatanList,
            'rencanaAksiList' => $rencanaAksiList,
            'selectedSubKegiatanNama' => $selectedSubKegiatanNama,
        ]);
    }
}
