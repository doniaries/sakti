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
    public $password = '';
    public $password_confirmation = '';
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

    public function toggleEdit()
    {
        $this->isEditing = !$this->isEditing;
        if ($this->isEditing) {
            $this->activeTab = 'utama';
        } else {
            $this->loadUserData(); // Cancel & reset
        }
    }

    public function save()
    {
        /** @var User $user */
        $user = Auth::user();

        $this->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'email_pribadi' => 'nullable|email|max:255',
            'no_hp' => 'nullable|string|max:25',
            'password' => 'nullable|min:6|confirmed',
            'new_avatar' => 'nullable|image|max:2048',
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

        if (!empty($this->password)) {
            $data['password'] = Hash::make($this->password);
        }

        if ($this->new_avatar) {
            $path = $this->new_avatar->store('avatars', 'public');
            $data['avatar_url'] = $path;
        }

        $user->update($data);

        $this->isEditing = false;
        $this->password = '';
        $this->password_confirmation = '';
        $this->new_avatar = null;

        $this->dispatch('notify', [
            'type' => 'success',
            'title' => 'Tersimpan!',
            'message' => 'Data profil Anda berhasil diperbarui.',
        ]);
    }

    public function render()
    {
        /** @var User $user */
        $user = Auth::user()->load(['jabatan', 'bidang', 'atasan', 'jadwalKerja']);

        return view('livewire.mobile.profil', [
            'user' => $user,
        ]);
    }
}
