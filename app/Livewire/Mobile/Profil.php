<?php

namespace App\Livewire\Mobile;

use App\Enums\Eselon;
use App\Enums\GolonganRuang;
use App\Enums\JenjangPendidikan;
use App\Models\AbsensiHarian;
use App\Models\Bidang;
use App\Models\Lhk;
use App\Models\PengaturanAplikasi;
use App\Models\RiwayatGolongan;
use App\Models\RiwayatJabatan;
use App\Models\RiwayatPendidikan;
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
    public bool $fullEditMode = false;
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
    public string $jenis_kelamin = '';
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

    // Tab Pangkat / Golongan
    public string $eselon = '';
    public ?int $editingGolonganId = null;
    public string $gol_golongan = '';
    public string $gol_nama_pangkat = '';
    public string $gol_nomor_sk = '';
    public string $gol_tanggal_sk = '';
    public string $gol_tmt_golongan = '';
    public bool $gol_is_terakhir = true;

    // Tab Jabatan
    public ?int $bidang_id = null;
    public ?int $atasan_id = null;
    public ?int $editingJabatanId = null;
    public string $jab_nama_jabatan = '';
    public string $jab_jenis_jabatan = 'Struktural';
    public string $jab_unit_kerja = '';
    public string $jab_nomor_sk = '';
    public string $jab_tanggal_sk = '';
    public string $jab_tmt_jabatan = '';
    public bool $jab_is_sekarang = true;

    // Tab Pendidikan
    public ?int $editingPendidikanId = null;
    public string $pend_jenjang = '';
    public string $pend_nama_institusi = '';
    public string $pend_jurusan = '';
    public string $pend_tahun_lulus = '';
    public string $pend_nomor_ijazah = '';
    public bool $pend_is_terakhir = true;

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
        $user = Auth::user()->load(['jabatan', 'bidang', 'atasan', 'jadwalKerja', 'riwayatGolongan', 'riwayatJabatan', 'riwayatPendidikan']);

        $this->name = $user->name ?? '';
        $this->email = $user->email ?? '';
        $this->no_hp = $user->no_hp ?? '';
        $this->no_telp = $user->no_telp ?? '';
        $this->alamat = $user->alamat ?? '';
        $this->agama = $user->agama ?? '';
        $this->jenis_kelamin = (string) ($user->jenis_kelamin?->value ?? ($user->jenis_kelamin ?? ''));
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
        $this->eselon = (string) ($user->eselon?->value ?? ($user->eselon ?? ''));
        $this->bidang_id = $user->bidang_id;
        $this->atasan_id = $user->atasan_id;
        $this->password = '';
        $this->password_confirmation = '';

        $latestGol = $user->riwayatGolongan->firstWhere('is_golongan_terakhir', true) ?? $user->riwayatGolongan->first();
        $this->fillGolonganForm($latestGol);

        $latestJab = $user->riwayatJabatan->firstWhere('is_jabatan_sekarang', true) ?? $user->riwayatJabatan->first();
        $this->fillJabatanForm($latestJab);

        $latestPend = $user->riwayatPendidikan->firstWhere('is_pendidikan_terakhir', true) ?? $user->riwayatPendidikan->last();
        $this->fillPendidikanForm($latestPend);
    }

    private function fillGolonganForm(?RiwayatGolongan $item): void
    {
        $this->editingGolonganId = $item?->id;
        $this->gol_golongan = (string) ($item?->golongan?->value ?? ($item?->golongan ?? ''));
        $this->gol_nama_pangkat = $item?->nama_pangkat ?? '';
        $this->gol_nomor_sk = $item?->nomor_sk ?? '';
        $this->gol_tanggal_sk = $item?->tanggal_sk ? $item->tanggal_sk->format('Y-m-d') : '';
        $this->gol_tmt_golongan = $item?->tmt_golongan ? $item->tmt_golongan->format('Y-m-d') : '';
        $this->gol_is_terakhir = $item ? (bool) $item->is_golongan_terakhir : true;
    }

    private function fillJabatanForm(?RiwayatJabatan $item): void
    {
        $this->editingJabatanId = $item?->id;
        $this->jab_nama_jabatan = $item?->nama_jabatan ?? '';
        $this->jab_jenis_jabatan = $item?->jenis_jabatan ?? 'Struktural';
        $this->jab_unit_kerja = $item?->unit_kerja ?? '';
        $this->jab_nomor_sk = $item?->nomor_sk ?? '';
        $this->jab_tanggal_sk = $item?->tanggal_sk ? $item->tanggal_sk->format('Y-m-d') : '';
        $this->jab_tmt_jabatan = $item?->tmt_jabatan ? $item->tmt_jabatan->format('Y-m-d') : '';
        $this->jab_is_sekarang = $item ? (bool) $item->is_jabatan_sekarang : true;
    }

    private function fillPendidikanForm(?RiwayatPendidikan $item): void
    {
        $this->editingPendidikanId = $item?->id;
        $this->pend_jenjang = (string) ($item?->jenjang?->value ?? ($item?->jenjang ?? ''));
        $this->pend_nama_institusi = $item?->nama_institusi ?? '';
        $this->pend_jurusan = $item?->jurusan ?? '';
        $this->pend_tahun_lulus = $item?->tahun_lulus ? (string) $item->tahun_lulus : '';
        $this->pend_nomor_ijazah = $item?->nomor_ijazah ?? '';
        $this->pend_is_terakhir = $item ? (bool) $item->is_pendidikan_terakhir : true;
    }

    public function newGolongan(): void
    {
        $this->fillGolonganForm(null);
    }

    public function editGolongan(int $id): void
    {
        $item = RiwayatGolongan::where('user_id', Auth::id())->find($id);
        if ($item) {
            $this->fillGolonganForm($item);
        }
    }

    public function newJabatan(): void
    {
        $this->fillJabatanForm(null);
    }

    public function editJabatan(int $id): void
    {
        $item = RiwayatJabatan::where('user_id', Auth::id())->find($id);
        if ($item) {
            $this->fillJabatanForm($item);
        }
    }

    public function newPendidikan(): void
    {
        $this->fillPendidikanForm(null);
    }

    public function editPendidikan(int $id): void
    {
        $item = RiwayatPendidikan::where('user_id', Auth::id())->find($id);
        if ($item) {
            $this->fillPendidikanForm($item);
        }
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

    public function saveCroppedAvatar(string $dataUrl): void
    {
        if (! preg_match('/^data:image\/(jpeg|jpg|png|webp);base64,(.+)$/i', $dataUrl, $matches)) {
            $this->dispatch('notify', [
                'type' => 'error',
                'title' => 'Format Tidak Valid',
                'message' => 'Pilih file gambar JPG, PNG, atau WEBP.',
            ]);
            return;
        }

        $binary = base64_decode($matches[2], true);
        if ($binary === false || strlen($binary) > 2 * 1024 * 1024) {
            $this->dispatch('notify', [
                'type' => 'error',
                'title' => 'Ukuran Terlalu Besar',
                'message' => 'Ukuran foto hasil crop maksimal 2MB.',
            ]);
            return;
        }

        /** @var User $freshUser */
        $freshUser = User::findOrFail(Auth::id());

        $oldAvatar = $freshUser->avatar_url;
        if (! empty($oldAvatar) && \Illuminate\Support\Facades\Storage::disk('public')->exists($oldAvatar)) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($oldAvatar);
        }

        $ext = strtolower($matches[1]) === 'jpeg' ? 'jpg' : strtolower($matches[1]);
        $path = 'avatars/' . $freshUser->id . '_' . time() . '.' . $ext;
        \Illuminate\Support\Facades\Storage::disk('public')->put($path, $binary);
        $freshUser->update(['avatar_url' => $path]);

        $this->dispatch('notify', [
            'type' => 'success',
            'title' => 'Foto Profil Diperbarui!',
            'message' => 'Foto profil hasil crop berhasil disimpan.',
        ]);
    }

    public function toggleEdit(bool $full = false): void
    {
        $this->fullEditMode = $full;
        $this->isEditing = !$this->isEditing;
        if ($this->isEditing) {
            $this->loadUserData();
            $this->password = '';
            $this->password_confirmation = '';
        } else {
            $this->new_avatar = null;
            $this->loadUserData(); // Cancel & reset
        }
    }

    public function save()
    {
        /** @var User $freshUser */
        $freshUser = User::findOrFail(Auth::id());

        if ($this->activeTab === 'golongan') {
            $this->saveGolongan($freshUser);
        } elseif ($this->activeTab === 'jabatan') {
            $this->saveJabatan($freshUser);
        } elseif ($this->activeTab === 'pendidikan') {
            $this->savePendidikan($freshUser);
        } elseif ($this->activeTab === 'keluarga') {
            $freshUser->update([
                'nik' => $this->nik ?: null,
                'no_kk' => $this->no_kk ?: null,
                'alamat' => $this->alamat ?: null,
            ]);
        } else {
            $this->saveUtama($freshUser);
        }

        $this->password = '';
        $this->password_confirmation = '';
        $this->isEditing = false;
        $this->fullEditMode = false;
        $this->loadUserData();

        $this->dispatch('notify', [
            'type' => 'success',
            'title' => 'Tersimpan!',
            'message' => 'Data profil Anda berhasil diperbarui.',
        ]);
    }

    private function saveGolongan(User $freshUser): void
    {
        $freshUser->update(['eselon' => $this->eselon ?: null]);

        if ($this->gol_golongan === '') {
            return;
        }

        if ($this->gol_is_terakhir) {
            RiwayatGolongan::where('user_id', $freshUser->id)
                ->when($this->editingGolonganId, fn ($q) => $q->where('id', '!=', $this->editingGolonganId))
                ->update(['is_golongan_terakhir' => false]);
        }

        $payload = [
            'golongan' => $this->gol_golongan,
            'nama_pangkat' => $this->gol_nama_pangkat,
            'nomor_sk' => $this->gol_nomor_sk ?: null,
            'tanggal_sk' => $this->gol_tanggal_sk ?: null,
            'tmt_golongan' => $this->gol_tmt_golongan ?: null,
            'is_golongan_terakhir' => $this->gol_is_terakhir,
        ];

        $existing = $this->editingGolonganId
            ? RiwayatGolongan::where('user_id', $freshUser->id)->find($this->editingGolonganId)
            : null;

        if ($existing) {
            $existing->update($payload);
        } else {
            RiwayatGolongan::create($payload + ['user_id' => $freshUser->id]);
        }
    }

    private function saveJabatan(User $freshUser): void
    {
        $freshUser->update([
            'bidang_id' => $this->bidang_id ?: null,
            'atasan_id' => $this->atasan_id ?: null,
        ]);

        if (trim($this->jab_nama_jabatan) === '') {
            return;
        }

        if ($this->jab_is_sekarang) {
            RiwayatJabatan::where('user_id', $freshUser->id)
                ->when($this->editingJabatanId, fn ($q) => $q->where('id', '!=', $this->editingJabatanId))
                ->update(['is_jabatan_sekarang' => false]);
        }

        $payload = [
            'nama_jabatan' => trim($this->jab_nama_jabatan),
            'jenis_jabatan' => $this->jab_jenis_jabatan ?: 'Struktural',
            'unit_kerja' => $this->jab_unit_kerja ?: null,
            'nomor_sk' => $this->jab_nomor_sk ?: null,
            'tanggal_sk' => $this->jab_tanggal_sk ?: null,
            'tmt_jabatan' => $this->jab_tmt_jabatan ?: null,
            'is_jabatan_sekarang' => $this->jab_is_sekarang,
        ];

        $existing = $this->editingJabatanId
            ? RiwayatJabatan::where('user_id', $freshUser->id)->find($this->editingJabatanId)
            : null;

        if ($existing) {
            $existing->update($payload);
        } else {
            RiwayatJabatan::create($payload + ['user_id' => $freshUser->id]);
        }
    }

    private function savePendidikan(User $freshUser): void
    {
        if ($this->pend_jenjang !== '' && trim($this->pend_nama_institusi) !== '') {
            if ($this->pend_is_terakhir) {
                RiwayatPendidikan::where('user_id', $freshUser->id)
                    ->when($this->editingPendidikanId, fn ($q) => $q->where('id', '!=', $this->editingPendidikanId))
                    ->update(['is_pendidikan_terakhir' => false]);
            }

            $payload = [
                'jenjang' => $this->pend_jenjang,
                'nama_institusi' => trim($this->pend_nama_institusi),
                'jurusan' => $this->pend_jurusan ?: null,
                'tahun_lulus' => $this->pend_tahun_lulus !== '' ? (int) $this->pend_tahun_lulus : null,
                'nomor_ijazah' => $this->pend_nomor_ijazah ?: null,
                'is_pendidikan_terakhir' => $this->pend_is_terakhir,
            ];

            $existing = $this->editingPendidikanId
                ? RiwayatPendidikan::where('user_id', $freshUser->id)->find($this->editingPendidikanId)
                : null;

            if ($existing) {
                $existing->update($payload);
            } else {
                RiwayatPendidikan::create($payload + ['user_id' => $freshUser->id]);
            }

            return;
        }

        $freshUser->update([
            'jenjang_pendidikan' => $this->jenjang_pendidikan ?: null,
            'pendidikan' => $this->pendidikan ?: null,
        ]);
    }

    private function saveUtama(User $freshUser): void
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

        $this->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', 'max:255', \Illuminate\Validation\Rule::unique('users', 'email')->ignore($freshUser->id)],
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
            'jenis_kelamin' => $this->jenis_kelamin ?: null,
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

        $freshUser->update($data);
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
        $user = User::with(['jabatan', 'bidang', 'atasan', 'jadwalKerja', 'riwayatGolongan', 'riwayatJabatan', 'riwayatPendidikan'])->find(Auth::id()) ?? Auth::user();

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
            'eselonOptions' => Eselon::cases(),
            'golonganOptions' => GolonganRuang::cases(),
            'jenjangOptions' => JenjangPendidikan::cases(),
            'bidangOptions' => Bidang::orderBy('nama')->get(['id', 'nama']),
            'atasanOptions' => User::where('id', '!=', $user->id)->orderBy('name')->get(['id', 'name']),
        ]);
    }
}
