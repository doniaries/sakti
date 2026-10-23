<?php

namespace App\Livewire\Mobile;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('components.layouts.mobile')]
#[Title('Profil Pegawai - Absensi LHK Mobile')]
class Profil extends Component
{
    use WithFileUploads;

    public $activeTab = 'utama'; // 'utama', 'golongan', 'jabatan', 'pendidikan', 'keluarga'
    public $isEditing = false;

    // Editable Fields
    public $name = '';
    public $email = '';
    public $email_pribadi = '';
    public $no_hp = '';
    public $no_telp = '';
    public $alamat = '';
    public $agama = '';
    public $nik = '';
    public $no_kk = '';
    public $npwp = '';
    public $no_bpjs = '';
    public $no_taspen = '';
    public $tempat_lahir = '';
    public $tanggal_lahir = '';
    public $gelar_depan = '';
    public $gelar_belakang = '';
    public $pendidikan = '';
    public $jenjang_pendidikan = '';
    public $new_avatar;

    public function mount()
    {
        $this->loadUserData();
    }

    public function loadUserData()
    {
        /** @var User $user */
        $user = Auth::user()->load(['jabatan', 'bidang', 'atasan', 'jadwalKerja']);

        $this->name = $user->name ?? '';
        $this->email = $user->email ?? '';
        $this->email_pribadi = $user->email_pribadi ?? '';
        $this->no_hp = $user->no_hp ?? '';
        $this->no_telp = $user->no_telp ?? '';
        $this->alamat = $user->alamat ?? '';
        $this->agama = $user->agama ?? '';
        $this->nik = $user->nik ?? '';
        $this->no_kk = $user->no_kk ?? '';
        $this->npwp = $user->npwp ?? '';
        $this->no_bpjs = $user->no_bpjs ?? '';
        $this->no_taspen = $user->no_taspen ?? '';
        $this->tempat_lahir = $user->tempat_lahir ?? '';
        $this->tanggal_lahir = $user->tanggal_lahir ? $user->tanggal_lahir->format('Y-m-d') : '';
        $this->gelar_depan = $user->gelar_depan ?? '';
        $this->gelar_belakang = $user->gelar_belakang ?? '';
        $this->pendidikan = $user->pendidikan ?? '';
        $this->jenjang_pendidikan = $user->jenjang_pendidikan ?? '';
    }

    public function setTab($tab)
    {
        $this->activeTab = $tab;
    }

    public function updatedNewAvatar()
    {
        $this->validate([
            'new_avatar' => 'required|image|max:2048',
        ], [
            'new_avatar.required' => 'Pilih file foto terlebih dahulu.',
            'new_avatar.image' => 'File harus berupa gambar (JPG, PNG, WEBP).',
            'new_avatar.max' => 'Ukuran foto maksimal 2MB.',
        ]);

        /** @var User $freshUser */
        $freshUser = User::findOrFail(Auth::id());

        // Hapus avatar lama dari storage jika ada
        $oldAvatar = $freshUser->avatar_url;
        if (!empty($oldAvatar) && \Illuminate\Support\Facades\Storage::disk('public')->exists($oldAvatar)) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($oldAvatar);
        }

        $path = $this->new_avatar->store('avatars', 'public');
        $freshUser->update(['avatar_url' => $path]);

        $this->new_avatar = null;

        session()->flash('notify', [
            'type' => 'success',
            'title' => 'Foto Profil Berhasil Diganti!',
            'message' => 'Foto profil terbaru Anda telah tersimpan.',
        ]);
    }

    public function toggleEdit()
    {
        $this->isEditing = !$this->isEditing;
        if ($this->isEditing) {
            $this->activeTab = 'utama';
        } else {
            $this->new_avatar = null;
            $this->loadUserData(); // Cancel & reset
        }
    }

    public function save()
    {
        // Sanitize nomor WhatsApp jika diisi (bersihkan non-angka dan standardisasi awal ke 08)
        if (!empty(trim((string) $this->no_hp))) {
            $cleanNoHp = preg_replace('/[^0-9]/', '', (string) $this->no_hp);
            if (str_starts_with($cleanNoHp, '62')) {
                $cleanNoHp = '0' . substr($cleanNoHp, 2);
            }
            $this->no_hp = $cleanNoHp;
        } else {
            $this->no_hp = null;
        }

        $userId = Auth::id();

        $this->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', 'max:255', \Illuminate\Validation\Rule::unique('users', 'email')->ignore($userId)],
            'email_pribadi' => 'nullable|email|max:255',
            'no_hp' => ['nullable', 'string', 'max:20'],
        ], [
            'name.required' => 'Nama lengkap wajib diisi.',
            'email.required' => 'Email login akun dinas wajib diisi.',
            'email.email' => 'Format email login tidak valid.',
            'email.unique' => 'Email login ini sudah digunakan akun lain.',
            'email_pribadi.email' => 'Format email pribadi tidak valid.',
        ]);

        $data = [
            'name' => $this->name,
            'email' => $this->email,
            'email_pribadi' => $this->email_pribadi,
            'no_hp' => $this->no_hp,
            'no_telp' => $this->no_telp,
            'alamat' => $this->alamat,
            'agama' => $this->agama,
            'nik' => $this->nik,
            'no_kk' => $this->no_kk,
            'npwp' => $this->npwp,
            'no_bpjs' => $this->no_bpjs,
            'no_taspen' => $this->no_taspen,
            'tempat_lahir' => $this->tempat_lahir,
            'tanggal_lahir' => $this->tanggal_lahir ?: null,
            'gelar_depan' => $this->gelar_depan,
            'gelar_belakang' => $this->gelar_belakang,
            'pendidikan' => $this->pendidikan,
            'jenjang_pendidikan' => $this->jenjang_pendidikan,
        ];

        /** @var User $freshUser */
        $freshUser = User::findOrFail(Auth::id());
        $freshUser->update($data);

        session()->flash('notify', [
            'type' => 'success',
            'title' => 'Tersimpan!',
            'message' => 'Data profil Anda berhasil diperbarui.',
        ]);

        return redirect()->route('mobile.dashboard');
    }

    public function render()
    {
        /** @var User $user */
        $user = User::with(['jabatan', 'bidang', 'atasan', 'jadwalKerja'])->find(Auth::id()) ?? Auth::user();

        return view('livewire.mobile.profil', [
            'user' => $user,
        ]);
    }
}
