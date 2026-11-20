<?php

namespace App\Livewire\Mobile\Lhk;

use App\Models\Aktivitas;
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
    public ?int $editingId = null;
    public string $kode = '';
    public string $nama_aktivitas = '';
    public int $menit = 30;

    public bool $showFormModal = false;
    public bool $showBulkModal = false;
    public string $bulkRaw = '';
    public int $bulkDefaultMenit = 30;

    public function openCreate(): void
    {
        $this->resetValidation();
        $this->editingId = null;
        $this->kode = (string) (Aktivitas::where('user_id', Auth::id())->count() + 1);
        $this->nama_aktivitas = '';
        $this->menit = 30;
        $this->showFormModal = true;
    }

    public function openEdit(int $id): void
    {
        $item = Aktivitas::where('user_id', Auth::id())->find($id);
        if (! $item) {
            return;
        }

        $this->resetValidation();
        $this->editingId = $item->id;
        $this->kode = (string) ($item->kode ?? '');
        $this->nama_aktivitas = (string) $item->nama_aktivitas;
        $this->menit = (int) $item->menit;
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

        $myAktivitas = Aktivitas::untukUser($user)
            ->when($this->search !== '', function ($q) {
                $term = '%' . trim($this->search) . '%';
                $q->where(function ($sub) use ($term) {
                    $sub->where('nama_aktivitas', 'like', $term)
                        ->orWhere('kode', 'like', $term);
                });
            })
            ->orderByRaw('CAST(kode AS UNSIGNED) ASC, kode ASC, id ASC')
            ->get();

        return view('livewire.mobile.lhk.bank-aktivitas', [
            'myAktivitas' => $myAktivitas,
        ]);
    }
}

