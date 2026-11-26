<?php

declare(strict_types=1);

namespace App\Livewire\Mobile\SuratTugas;

use App\Enums\Eselon;
use App\Models\SuratTugas;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('components.layouts.mobile')]
#[Title('Buat Surat Tugas - SAKTI Mobile')]
class Form extends Component
{
    use WithFileUploads;

    public ?int $suratTugasId = null;
    public ?string $existingBuktiPath = null;
    public string $jenis_dasar = 'lisan';
    public string $dasar_surat_undangan = '';
    public string $asal_surat_undangan = '';
    public string $perihal_surat_undangan = '';
    public string $tanggal_surat_undangan = '';
    public string $lokasi = '';
    public string $jam_acara = '';
    public string $tanggal_mulai = '';
    public string $tanggal_selesai = '';
    /** @var \Livewire\Features\SupportFileUploads\TemporaryUploadedFile|null */
    public $bukti_surat_undangan = null;
    /** @var array<int, int|string> */
    public array $penanggung_jawab_ids = [];
    public ?int $penandatangan_id = null;
    /** @var array<int, int|string> */
    public array $pegawai_ids = [];
    public string $keterangan = '';

    public function mount(?SuratTugas $suratTugas = null): void
    {
        /** @var User|null $user */
        $user = Auth::user();
        abort_unless($user && $user->canManageSuratTugas(), 403);

        if ($suratTugas && $suratTugas->exists) {
            if ($suratTugas->status === 'terbit') {
                session()->flash('error', 'Surat Tugas yang sudah terbit tidak dapat diedit.');
                $this->redirectRoute('mobile.surat-tugas.index');
                return;
            }

            $this->suratTugasId = $suratTugas->id;
            $this->existingBuktiPath = $suratTugas->bukti_surat_undangan;
            $this->jenis_dasar = ($suratTugas->dasar_surat_undangan === 'Perintah Lisan Pimpinan' && blank($suratTugas->asal_surat_undangan))
                ? 'lisan'
                : 'ada_surat';
            $this->dasar_surat_undangan = (string) ($suratTugas->dasar_surat_undangan ?? '');
            $this->asal_surat_undangan = (string) ($suratTugas->asal_surat_undangan ?? '');
            $this->perihal_surat_undangan = (string) ($suratTugas->perihal_surat_undangan ?? $suratTugas->nama_kegiatan ?? '');
            $this->tanggal_surat_undangan = $suratTugas->tanggal_surat_undangan ? $suratTugas->tanggal_surat_undangan->toDateString() : '';
            $this->lokasi = (string) ($suratTugas->lokasi ?? '');
            $this->jam_acara = (string) ($suratTugas->jam_acara ?? '');
            $this->tanggal_mulai = $suratTugas->tanggal_mulai ? $suratTugas->tanggal_mulai->toDateString() : now()->addDay()->toDateString();
            $this->tanggal_selesai = $suratTugas->tanggal_selesai ? $suratTugas->tanggal_selesai->toDateString() : $this->tanggal_mulai;
            $this->penanggung_jawab_ids = array_map('intval', $suratTugas->penanggung_jawab_ids ?? []);
            $this->penandatangan_id = $suratTugas->penandatangan_id ? (int) $suratTugas->penandatangan_id : null;
            $this->pegawai_ids = $suratTugas->pegawais()->pluck('users.id')->map(fn ($id) => (int) $id)->all();
            $this->keterangan = (string) ($suratTugas->keterangan ?? '');
            return;
        }

        $tomorrow = now()->addDay()->toDateString();
        $this->tanggal_mulai = $tomorrow;
        $this->tanggal_selesai = $tomorrow;
    }

    public function save(): void
    {
        /** @var User|null $user */
        $user = Auth::user();
        abort_unless($user && $user->canManageSuratTugas(), 403);

        $validated = $this->validate([
            'dasar_surat_undangan' => ['nullable', 'string', 'max:500'],
            'asal_surat_undangan' => ['nullable', 'string', 'max:255'],
            'perihal_surat_undangan' => ['required', 'string', 'max:255'],
            'tanggal_surat_undangan' => ['nullable', 'date'],
            'lokasi' => ['required', 'string', 'max:255'],
            'jam_acara' => ['nullable', 'string', 'max:20'],
            'tanggal_mulai' => ['required', 'date'],
            'tanggal_selesai' => ['required', 'date', 'after_or_equal:tanggal_mulai'],
            'bukti_surat_undangan' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:5120'],
            'penanggung_jawab_ids' => ['required', 'array', 'min:1'],
            'penanggung_jawab_ids.*' => ['integer', 'exists:users,id'],
            'penandatangan_id' => ['nullable', 'integer', 'exists:users,id'],
            'pegawai_ids' => ['nullable', 'array'],
            'pegawai_ids.*' => ['integer', 'exists:users,id'],
            'keterangan' => ['nullable', 'string'],
        ]);

        $buktiPath = ($this->jenis_dasar === 'ada_surat' && $this->bukti_surat_undangan)
            ? $this->bukti_surat_undangan->store('surat-tugas/undangan', 'public')
            : ($this->jenis_dasar === 'ada_surat' ? $this->existingBuktiPath : null);

        $payload = [
            'dasar_surat_undangan' => $this->jenis_dasar === 'lisan'
                ? 'Perintah Lisan Pimpinan'
                : ($validated['dasar_surat_undangan'] ?: null),
            'asal_surat_undangan' => $this->jenis_dasar === 'ada_surat' ? ($validated['asal_surat_undangan'] ?: null) : null,
            'perihal_surat_undangan' => $validated['perihal_surat_undangan'],
            'nama_kegiatan' => $validated['perihal_surat_undangan'],
            'tanggal_surat_undangan' => $this->jenis_dasar === 'ada_surat' ? ($validated['tanggal_surat_undangan'] ?: null) : null,
            'lokasi' => $validated['lokasi'],
            'jam_acara' => $validated['jam_acara'] ?: null,
            'tanggal_mulai' => $validated['tanggal_mulai'],
            'tanggal_selesai' => $validated['tanggal_selesai'],
            'bukti_surat_undangan' => $buktiPath,
            'penanggung_jawab_ids' => array_map('intval', $validated['penanggung_jawab_ids']),
            'penandatangan_id' => $validated['penandatangan_id'] ?: null,
            'keterangan' => $validated['keterangan'] ?: null,
        ];

        if ($this->suratTugasId) {
            $suratTugas = SuratTugas::findOrFail($this->suratTugasId);
            abort_if($suratTugas->status === 'terbit', 403);
            $suratTugas->update($payload);
            $suratTugas->pegawais()->sync($validated['pegawai_ids'] ?? []);
            if ($suratTugas->penandatangan_id) {
                $suratTugas->kirimNotifikasiPengajuan();
            }
            session()->flash('success', 'Surat Tugas berhasil diperbarui.');
        } else {
            $suratTugas = SuratTugas::create($payload + [
                'status' => 'draft',
                'sumber' => 'mobile_pwa',
                'created_by' => $user->id,
            ]);

            if (! empty($validated['pegawai_ids'])) {
                $suratTugas->pegawais()->sync($validated['pegawai_ids']);
            }
            $suratTugas->kirimNotifikasiPengajuan();
            session()->flash('success', 'Surat Tugas berhasil dibuat.');
        }

        $this->redirectRoute('mobile.surat-tugas.index');
    }

    public function render(): View
    {
        $penanggungJawabs = User::query()
            ->bukanSuperAdmin()
            ->where('aktif', true)
            ->whereIn('eselon', [
                Eselon::II_A->value,
                Eselon::II_B->value,
                Eselon::III_A->value,
                Eselon::III_B->value,
                Eselon::IV_A->value,
                Eselon::IV_B->value,
            ])
            ->with('jabatan')
            ->orderBy('name')
            ->get(['id', 'name', 'jabatan_id', 'eselon']);

        $penandatangans = $penanggungJawabs->filter(fn (User $u) => in_array(
            $u->eselon instanceof Eselon ? $u->eselon->value : $u->eselon,
            [Eselon::III_A->value, Eselon::III_B->value],
            true
        ));

        $timKreatif = User::query()
            ->whereNotNull('jabatan_tim_kreatif')
            ->where('jabatan_tim_kreatif', '!=', '')
            ->whereNotIn('jabatan_tim_kreatif', ['pembina', 'pengarah', 'penanggung_jawab'])
            ->orderBy('name')
            ->get(['id', 'name', 'jabatan_tim_kreatif']);

        $jamOptions = [
            '07:30', '08:00', '08:30', '09:00', '09:30',
            '10:00', '10:30', '11:00', '13:00', '13:30',
            '14:00', '14:30', '15:00', '16:00', '19:00', '20:00', '20:30', '21:00',
        ];

        return view('livewire.mobile.surat-tugas.form', [
            'penanggungJawabs' => $penanggungJawabs,
            'penandatangans' => $penandatangans,
            'timKreatif' => $timKreatif,
            'jamOptions' => $jamOptions,
        ]);
    }
}
