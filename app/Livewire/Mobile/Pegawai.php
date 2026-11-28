<?php

namespace App\Livewire\Mobile;

use App\Models\Bidang;
use App\Models\Jabatan;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.mobile')]
#[Title('Data Pegawai - SAKTI Mobile')]
class Pegawai extends Component
{
    public string $search = '';
    public string $bidangFilter = '';

    public bool $showFormModal = false;
    public ?int $editingId = null;
    public string $name = '';
    public string $nip = '';
    public string $email = '';
    public string $jabatan_id = '';
    public string $bidang_id = '';
    public string $atasan_id = '';
    public string $status_kerja = 'wfo';
    public bool $aktif = true;
    public string $password = '';

    private function canManage(): bool
    {
        /** @var User|null $user */
        $user = Auth::user();

        return $user?->hasAnyRole(['super_admin', 'superadmin', 'admin_kepegawaian']) ?? false;
    }

    public function openCreate(): void
    {
        abort_unless($this->canManage(), 403);

        $this->resetValidation();
        $this->editingId = null;
        $this->name = '';
        $this->nip = '';
        $this->email = '';
        $this->jabatan_id = '';
        $this->bidang_id = '';
        $this->atasan_id = '';
        $this->status_kerja = 'wfo';
        $this->aktif = true;
        $this->password = '';
        $this->showFormModal = true;
    }

    public function openEdit(int $id): void
    {
        abort_unless($this->canManage(), 403);

        $pegawai = User::query()->findOrFail($id);

        $this->resetValidation();
        $this->editingId = $pegawai->id;
        $this->name = (string) $pegawai->name;
        $this->nip = (string) ($pegawai->nip ?? '');
        $this->email = (string) $pegawai->email;
        $this->jabatan_id = $pegawai->jabatan_id ? (string) $pegawai->jabatan_id : '';
        $this->bidang_id = $pegawai->bidang_id ? (string) $pegawai->bidang_id : '';
        $this->atasan_id = $pegawai->atasan_id ? (string) $pegawai->atasan_id : '';
        $this->status_kerja = (string) ($pegawai->status_kerja ?? 'wfo');
        $this->aktif = (bool) $pegawai->aktif;
        $this->password = '';
        $this->showFormModal = true;
    }

    public function savePegawai(): void
    {
        abort_unless($this->canManage(), 403);

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'nip' => ['nullable', 'string', 'max:50'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($this->editingId),
            ],
            'jabatan_id' => ['nullable', 'integer', 'exists:jabatans,id'],
            'bidang_id' => ['nullable', 'integer', 'exists:bidangs,id'],
            'atasan_id' => ['nullable', 'integer', 'exists:users,id'],
            'status_kerja' => ['required', 'in:wfo,wfh'],
            'aktif' => ['boolean'],
            'password' => [$this->editingId ? 'nullable' : 'required', 'string', 'min:6'],
        ]);

        $payload = [
            'name' => trim($validated['name']),
            'nip' => filled($validated['nip']) ? trim((string) $validated['nip']) : null,
            'email' => strtolower(trim($validated['email'])),
            'jabatan_id' => $validated['jabatan_id'] ?: null,
            'bidang_id' => $validated['bidang_id'] ?: null,
            'atasan_id' => $validated['atasan_id'] ?: null,
            'status_kerja' => $validated['status_kerja'],
            'aktif' => (bool) $validated['aktif'],
        ];

        if (filled($validated['password'] ?? null)) {
            $payload['password'] = Hash::make((string) $validated['password']);
        }

        if ($this->editingId) {
            User::query()->findOrFail($this->editingId)->update($payload);
            $title = 'Data Pegawai Diperbarui';
            $message = 'Perubahan data utama pegawai berhasil disimpan.';
        } else {
            User::query()->create($payload);
            $title = 'Pegawai Ditambahkan';
            $message = 'Pegawai baru berhasil ditambahkan.';
        }

        Cache::forget('mobile.agenda.pegawais_aktif');
        $this->showFormModal = false;
        $this->dispatch('notify', type: 'success', title: $title, message: $message);
    }

    public function render()
    {
        $canManage = $this->canManage();

        $bidangs = Bidang::query()
            ->withCount(['users' => fn ($q) => $q->bukanSuperAdmin()->where('aktif', true)])
            ->orderBy('nama')
            ->get(['id', 'nama']);

        $totalAktif = User::query()->bukanSuperAdmin()->where('aktif', true)->count();

        $pegawais = User::query()
            ->bukanSuperAdmin()
            ->with(['jabatan:id,nama', 'bidang:id,nama', 'atasan:id,name'])
            ->when(! $canManage, fn ($q) => $q->where('aktif', true))
            ->when($this->bidangFilter !== '', fn ($q) => $q->where('bidang_id', (int) $this->bidangFilter))
            ->when(trim($this->search) !== '', function ($q) {
                $term = '%' . trim($this->search) . '%';
                $q->where(function ($sub) use ($term) {
                    $sub->where('name', 'like', $term)
                        ->orWhere('nip', 'like', $term)
                        ->orWhereHas('jabatan', fn ($j) => $j->where('nama', 'like', $term))
                        ->orWhereHas('bidang', fn ($b) => $b->where('nama', 'like', $term));
                });
            })
            ->orderBy('id')
            ->get([
                'id',
                'name',
                'gelar_depan',
                'gelar_belakang',
                'nip',
                'email',
                'avatar_url',
                'jabatan_id',
                'bidang_id',
                'atasan_id',
                'status_kerja',
                'aktif',
            ]);

        return view('livewire.mobile.pegawai', [
            'pegawais' => $pegawais,
            'bidangs' => $bidangs,
            'totalAktif' => $totalAktif,
            'canManage' => $canManage,
            'jabatans' => $canManage ? Jabatan::query()->orderBy('nama')->get(['id', 'nama']) : collect(),
            'atasans' => $canManage ? User::query()->bukanSuperAdmin()->where('aktif', true)->orderBy('name')->get(['id', 'name']) : collect(),
        ]);
    }
}
