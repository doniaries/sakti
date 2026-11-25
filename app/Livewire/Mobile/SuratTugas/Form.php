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

    public string $dasar_surat_undangan = '';
    public string $asal_surat_undangan = '';
    public string $perihal_surat_undangan = '';
    public string $tanggal_surat_undangan = '';
    public string $lokasi = '';
    public string $jam_acara = '';
    public string $tanggal_mulai = '';
    public string $tanggal_selesai = '';
    public $bukti_surat_undangan = null;
    /** @var array<int, int|string> */
    public array $penanggung_jawab_ids = [];
    /** @var array<int, int|string> */
    public array $pegawai_ids = [];
    public string $keterangan = '';

    public function mount(): void
    {
        /** @var User|null $user */
        $user = Auth::user();
        abort_unless($user && $user->canManageSuratTugas(), 403);

        $today = now()->toDateString();
        $this->tanggal_mulai = $today;
        $this->tanggal_selesai = $today;
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
            'pegawai_ids' => ['nullable', 'array'],
            'pegawai_ids.*' => ['integer', 'exists:users,id'],
            'keterangan' => ['nullable', 'string'],
        ]);

        $buktiPath = $this->bukti_surat_undangan
            ? $this->bukti_surat_undangan->store('surat-tugas/undangan', 'public')
            : null;

        $suratTugas = SuratTugas::create([
            'dasar_surat_undangan' => $validated['dasar_surat_undangan'] ?: null,
            'asal_surat_undangan' => $validated['asal_surat_undangan'] ?: null,
            'perihal_surat_undangan' => $validated['perihal_surat_undangan'],
            'nama_kegiatan' => $validated['perihal_surat_undangan'],
            'tanggal_surat_undangan' => $validated['tanggal_surat_undangan'] ?: null,
            'lokasi' => $validated['lokasi'],
            'jam_acara' => $validated['jam_acara'] ?: null,
            'tanggal_mulai' => $validated['tanggal_mulai'],
            'tanggal_selesai' => $validated['tanggal_selesai'],
            'bukti_surat_undangan' => $buktiPath,
            'penanggung_jawab_ids' => array_map('intval', $validated['penanggung_jawab_ids']),
            'keterangan' => $validated['keterangan'] ?: null,
            'status' => 'draft',
            'sumber' => 'mobile_pwa',
            'created_by' => $user->id,
        ]);

        if (! empty($validated['pegawai_ids'])) {
            $suratTugas->pegawais()->sync($validated['pegawai_ids']);
        }

        session()->flash('success', 'Surat Tugas berhasil dibuat.');
        $this->redirect(route('mobile.surat-tugas.index'), navigate: true);
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
            ->get(['id', 'name', 'jabatan_id']);

        $timKreatif = User::query()
            ->whereNotNull('jabatan_tim_kreatif')
            ->where('jabatan_tim_kreatif', '!=', '')
            ->orderBy('name')
            ->get(['id', 'name', 'jabatan_tim_kreatif']);

        $jamOptions = [
            '07:30', '08:00', '08:30', '09:00', '09:30',
            '10:00', '10:30', '11:00', '13:00', '13:30',
            '14:00', '14:30', '15:00', '16:00', '19:00', '20:00', '20:30', '21:00',
        ];

        return view('livewire.mobile.surat-tugas.form', [
            'penanggungJawabs' => $penanggungJawabs,
            'timKreatif' => $timKreatif,
            'jamOptions' => $jamOptions,
        ]);
    }
}
