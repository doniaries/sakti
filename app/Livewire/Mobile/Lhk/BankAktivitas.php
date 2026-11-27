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
    public ?int $editingId = null;
    public ?int $rencana_aksi_id = null;
    public string $nama_aktivitas = '';
    public int $menit = 30;
    public string $tipe = 'khusus';

    public bool $showFormModal = false;
    public bool $showBulkModal = false;
    public string $bulkRaw = '';
    public int $bulkDefaultMenit = 30;

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

        if ($this->editingId) {
            $item = Aktivitas::where('user_id', $user->id)->find($this->editingId);
            if ($item) {
                $item->update([
                    'kode' => $this->kode ?: null,
                    'nama_aktivitas' => trim($this->nama_aktivitas),
                    'menit' => $this->menit,
                    'jabatan_id' => $user->jabatan_id,
                    'bidang_id' => $user->bidang_id,
                ]);
            }
            $msg = 'Aktivitas berhasil diperbarui.';
        } else {
            Aktivitas::create([
                'user_id' => $user->id,
                'jabatan_id' => $user->jabatan_id,
                'bidang_id' => $user->bidang_id,
                'kode' => $this->kode ?: (string) (Aktivitas::where('user_id', $user->id)->count() + 1),
                'nama_aktivitas' => trim($this->nama_aktivitas),
                'menit' => $this->menit,
            ]);
            $msg = 'Aktivitas baru berhasil ditambahkan ke Bank Aktivitas Anda.';
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

        foreach ($items as $row) {
            $nextKode++;
            $existing = Aktivitas::where('user_id', $user->id)
                ->where('nama_aktivitas', $row['nama_aktivitas'])
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
            'message' => "{$added} aktivitas baru berhasil disimpan ke Bank Aktivitas Anda.",
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
            ->pluck('rencanaAksi.sasaran_sub_kegiatan')
            ->filter()
            ->countBy()
            ->sortKeys();

        $globalCount = $allUserAktivitas->filter(fn (Aktivitas $a): bool => is_null($a->user_id) && is_null($a->jabatan_id))->count();
        $spesifikCount = $allUserAktivitas->count() - $globalCount;

        $searchTerm = mb_strtolower(trim($this->search));
        $myAktivitas = $allUserAktivitas
            ->when($searchTerm !== '', fn ($col) => $col->filter(
                fn (Aktivitas $a): bool => str_contains(mb_strtolower((string) $a->nama_aktivitas), $searchTerm)
                    || str_contains(mb_strtolower((string) ($a->rencanaAksi?->sasaran_sub_kegiatan ?? '')), $searchTerm)
            ))
            ->when($this->filterSubKegiatan === 'global', fn ($col) => $col->filter(
                fn (Aktivitas $a): bool => is_null($a->user_id) && is_null($a->jabatan_id)
            ))
            ->when(! in_array($this->filterSubKegiatan, ['semua', 'global'], true), fn ($col) => $col->filter(
                fn (Aktivitas $a): bool => ($a->rencanaAksi?->sasaran_sub_kegiatan ?? '') === $this->filterSubKegiatan
            ))
            ->values();

        $rencanaAksiOptions = RencanaAksi::query()
            ->select(['id', 'sasaran_sub_kegiatan', 'rencana_aksi', 'level'])
            ->orderBy('sasaran_sub_kegiatan')
            ->orderBy('rencana_aksi')
            ->get()
            ->groupBy(fn (RencanaAksi $ra): string => $ra->sasaran_sub_kegiatan ?: 'Umum');

        return view('livewire.mobile.lhk.bank-aktivitas', [
            'myAktivitas' => $myAktivitas,
            'globalCount' => $globalCount,
            'spesifikCount' => $spesifikCount,
            'subKegiatanList' => $subKegiatanList,
            'rencanaAksiOptions' => $rencanaAksiOptions,
        ]);
    }
}

