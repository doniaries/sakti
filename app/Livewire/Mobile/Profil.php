<?php

namespace App\Livewire\Mobile;

use App\Models\AbsensiHarian;
use App\Models\Lhk;
use App\Models\PengaturanAplikasi;
use App\Models\User;
use Carbon\Carbon;
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
    public string $impersonateSearch = '';
    public bool $showImpersonateModal = false;
    public $isEditing = false;

    // Filter Statistik Bulan & Tahun
    public $bulan;
    public $tahun;

    // Editable Fields
    public $name = '';
    public $email = '';
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

    /** @var array<string, bool> */
    public array $modulAktif = [];

    public function mount()
    {
        $this->bulan = (int) date('m');
        $this->tahun = (int) date('Y');
        $this->loadUserData();

        /** @var User|null $user */
        $user = Auth::user();
        if ($user?->hasRole('super_admin')) {
            $this->modulAktif = PengaturanAplikasi::getModulAktifMap();
        }
    }

    public function toggleModul(string $key): void
    {
        /** @var User|null $user */
        $user = Auth::user();
        if (! $user?->hasRole('super_admin')) {
            $this->dispatch('notify', [
                'type' => 'error',
                'title' => 'Akses Ditolak',
                'message' => 'Hanya Super Admin yang dapat mengubah pengaturan modul.',
            ]);
            return;
        }

        if (! array_key_exists($key, PengaturanAplikasi::DEFAULT_MODULES)) {
            return;
        }

        $current = PengaturanAplikasi::getModulAktifMap();
        $current[$key] = ! ($current[$key] ?? false);

        $pengaturan = PengaturanAplikasi::getPengaturan();
        $pengaturan->update(['modul_aktif' => $current]);
        $this->modulAktif = PengaturanAplikasi::getModulAktifMap();

        $this->dispatch('notify', [
            'type' => 'success',
            'title' => 'Pengaturan Modul Diperbarui',
            'message' => 'Perubahan status modul langsung diterapkan di Web Admin & Mobile.',
        ]);
    }

    public function prevMonth()
    {
        $date = Carbon::createFromDate($this->tahun, $this->bulan, 1)->subMonth();
        $this->bulan = (int) $date->format('m');
        $this->tahun = (int) $date->format('Y');
    }

    public function nextMonth()
    {
        $date = Carbon::createFromDate($this->tahun, $this->bulan, 1)->addMonth();
        $this->bulan = (int) $date->format('m');
        $this->tahun = (int) $date->format('Y');
    }

    public function loadUserData()
    {
        /** @var User $user */
        $user = Auth::user()->load(['jabatan', 'bidang', 'atasan', 'jadwalKerja']);

        $this->name = $user->name ?? '';
        $this->email = $user->email ?? '';
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
        $this->password = '';
        $this->password_confirmation = '';
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
            $this->password = '';
            $this->password_confirmation = '';
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
            'no_hp' => ['nullable', 'string', 'max:20'],
        ], [
            'name.required' => 'Nama lengkap wajib diisi.',
            'email.required' => 'Email user wajib diisi.',
            'email.email' => 'Format email user tidak valid.',
            'email.unique' => 'Email user ini sudah digunakan akun lain.',
        ]);

        $data = [
            'name' => $this->name,
            'email' => $this->email,
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

        // Hanya validasi dan ubah password jika user sengaja mengisinya
        if (!empty($this->password)) {
            $this->validate([
                'password' => 'min:6|same:password_confirmation',
                'password_confirmation' => 'required',
            ], [
                'password.min' => 'Password minimal 6 karakter.',
                'password.same' => 'Konfirmasi password baru tidak cocok.',
                'password_confirmation.required' => 'Ketik ulang password baru pada konfirmasi password.',
            ]);

            $data['password'] = Hash::make($this->password);
        }

        /** @var User $freshUser */
        $freshUser = User::findOrFail(Auth::id());
        $freshUser->update($data);

        // Reset password state
        $this->password = '';
        $this->password_confirmation = '';

        session()->flash('notify', [
            'type' => 'success',
            'title' => 'Tersimpan!',
            'message' => 'Data profil Anda berhasil diperbarui.',
        ]);
    }

    public function impersonateUser(int $targetUserId): void
    {
        /** @var User|null $current */
        $current = Auth::user();
        if (!$current || (!$current->hasRole('super_admin') && !session()->has('impersonate_original_id'))) {
            return;
        }

        if (!session()->has('impersonate_original_id')) {
            session(['impersonate_original_id' => $current->id]);
        }

        Auth::loginUsingId($targetUserId);
        $this->redirect(route('mobile.dashboard'), navigate: true);
    }

    public function stopImpersonating(): void
    {
        $originalId = session()->pull('impersonate_original_id');
        if ($originalId) {
            Auth::loginUsingId($originalId);
        }

        $this->redirect(route('mobile.profil'), navigate: true);
    }

    /**
     * Logout Akun dari Mobile
     */
    public function logout()
    {
        Auth::logout();
        session()->invalidate();
        session()->regenerateToken();

        return redirect()->route('mobile.login');
    }

    public function render()
    {
        /** @var User $user */
        $user = User::with(['jabatan', 'bidang', 'atasan', 'jadwalKerja'])->find(Auth::id()) ?? Auth::user();

        // Statistik Bulan dan Tahun Terpilih
        $selectedDate = Carbon::createFromDate($this->tahun, $this->bulan, 1);
        $startOfMonth = $selectedDate->copy()->startOfMonth();
        $endOfMonth = $selectedDate->copy()->endOfMonth();

        $absensiBulanIni = AbsensiHarian::where('user_id', $user->id)
            ->whereBetween('tanggal', [$startOfMonth->format('Y-m-d'), $endOfMonth->format('Y-m-d')])
            ->get();

        $jadwalDefault = $user->getJadwalKerja();

        $statistik = [
            'hadir' => $absensiBulanIni->filter(fn($a) => in_array($a->status?->value ?? $a->status, ['hadir', 'terlambat']))->count(),
            'terlambat' => $absensiBulanIni->filter(fn($a) => ($a->status?->value ?? $a->status) === 'terlambat')->count(),
            'dinas_luar' => $absensiBulanIni->filter(fn($a) => ($a->status?->value ?? $a->status) === 'dinas_luar' || ($a->jenis_kehadiran?->value ?? $a->jenis_kehadiran) === 'dinas_luar')->count(),
            'pulang_cepat' => $absensiBulanIni->filter(function($a) use ($jadwalDefault) {
                if (empty($a->jam_pulang)) return false;
                $jamPulangResmi = $jadwalDefault ? $jadwalDefault->getJamPulangHari($a->tanggal) : '16:00';
                return $a->jam_pulang < $jamPulangResmi;
            })->count(),
            'tanpa_keterangan' => $absensiBulanIni->filter(fn($a) => in_array($a->status?->value ?? $a->status, ['tidak_hadir', 'alpa']) || (empty($a->jam_masuk) && empty($a->jam_pulang) && !in_array($a->status?->value ?? $a->status, ['izin', 'sakit', 'cuti', 'libur', 'dinas_luar'])))->count(),
            'izin' => $absensiBulanIni->filter(fn($a) => ($a->status?->value ?? $a->status) === 'izin')->count(),
            'sakit' => $absensiBulanIni->filter(fn($a) => ($a->status?->value ?? $a->status) === 'sakit')->count(),
            'lhk_dibuat' => Lhk::where('user_id', $user->id)
                ->whereBetween('tanggal', [$startOfMonth, $endOfMonth])
                ->count(),
        ];

        $impersonateCandidates = $user->hasRole('super_admin')
            ? User::query()
                ->where('id', '!=', $user->id)
                ->when($this->impersonateSearch, fn ($q) => $q->where(fn ($sub) => $sub
                    ->where('name', 'like', '%' . $this->impersonateSearch . '%')
                    ->orWhere('nip', 'like', '%' . $this->impersonateSearch . '%')))
                ->orderBy('name')
                ->limit(15)
                ->get(['id', 'name', 'nip'])
            : collect();

        return view('livewire.mobile.profil', [
            'user' => $user,
            'statistik' => $statistik,
            'bulanNama' => $selectedDate->translatedFormat('F Y'),
            'bulan' => $this->bulan,
            'tahun' => $this->tahun,
            'impersonateCandidates' => $impersonateCandidates,
        ]);
    }
}
