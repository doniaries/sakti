<?php

namespace App\Livewire\Mobile;

use App\Models\AgendaDisposisi;
use App\Models\AgendaKegiatan;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('components.layouts.mobile')]
#[Title('Tambah Agenda Kegiatan - Absensi LHK Mobile')]
class AgendaForm extends Component
{
    use WithFileUploads;

    public string $judul = '';
    public string $tanggal = '';
    public string $jam_mulai = '08:00';
    public string $jam_selesai = '';
    public string $tempat = '';
    public string $asal_undangan = '';
    /** @var array<int, int|string> */
    public array $disposisi_user_ids = [];
    public string $instruksi = '';
    /** @var \Livewire\Features\SupportFileUploads\TemporaryUploadedFile|string|null */
    public mixed $lampiran_file = null;

    public function mount(): void
    {
        $this->tanggal = Carbon::today()->toDateString();
    }

    public function simpanAgenda(): void
    {
        $validated = $this->validate([
            'judul' => ['required', 'string', 'max:255'],
            'tanggal' => ['required', 'date'],
            'jam_mulai' => ['required', 'string'],
            'jam_selesai' => ['required', 'string'],
            'tempat' => ['required', 'string', 'max:255'],
            'asal_undangan' => ['required', 'string', 'max:255'],
            'disposisi_user_ids' => ['required', 'array', 'min:1'],
            'disposisi_user_ids.*' => ['integer', 'exists:users,id'],
            'instruksi' => ['nullable', 'string', 'max:500'],
            'lampiran_file' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],
        ]);

        $lampiranPath = $this->lampiran_file
            ? $this->lampiran_file->store('agenda-lampiran', 'public')
            : null;

        $now = now();
        $agenda = DB::transaction(function () use ($validated, $lampiranPath, $now): AgendaKegiatan {
            $agenda = AgendaKegiatan::create([
                'judul' => $validated['judul'],
                'tanggal' => $validated['tanggal'],
                'jam_mulai' => $validated['jam_mulai'],
                'jam_selesai' => $validated['jam_selesai'] ?: null,
                'tempat' => $validated['tempat'],
                'asal_undangan' => $validated['asal_undangan'] ?: null,
                'keterangan' => null,
                'lampiran_file' => $lampiranPath,
                'created_by' => Auth::id(),
            ]);

            $instruksi = $validated['instruksi'] ?: 'Hadir mewakili / menindaklanjuti';
            $rows = collect($validated['disposisi_user_ids'] ?? [])
                ->map(fn ($id): int => (int) $id)
                ->filter(fn (int $id): bool => $id > 0)
                ->unique()
                ->values()
                ->map(fn (int $uid): array => [
                    'agenda_kegiatan_id' => $agenda->id,
                    'user_id' => $uid,
                    'instruksi' => $instruksi,
                    'notified_at' => $now,
                    'created_at' => $now,
                    'updated_at' => $now,
                ])
                ->all();

            if ($rows !== []) {
                AgendaDisposisi::insert($rows);
            }

            return $agenda;
        });

        $recipientIds = collect($validated['disposisi_user_ids'] ?? [])
            ->map(fn ($id): int => (int) $id)
            ->filter()
            ->unique()
            ->values()
            ->all();

        if ($recipientIds !== []) {
            $agenda->notifyDisposisiRecipients($recipientIds, $validated['instruksi'] ?? null);
        }

        $this->dispatch('notify', type: 'success', title: 'Agenda Ditambahkan', message: 'Agenda kegiatan & disposisi berhasil disimpan.');

        $this->redirect(route('mobile.agenda'), navigate: true);
    }

    public function render()
    {
        return view('livewire.mobile.agenda-form', [
            'pegawais' => User::where('aktif', true)->with('jabatan:id,nama')->orderBy('id')->get(['id', 'name', 'jabatan_id']),
            'riwayatTempat' => AgendaKegiatan::query()
                ->whereNotNull('tempat')
                ->where('tempat', '!=', '')
                ->orderByDesc('id')
                ->limit(50)
                ->pluck('tempat')
                ->unique()
                ->take(8)
                ->values(),
            'riwayatInstruksi' => AgendaDisposisi::query()
                ->whereNotNull('instruksi')
                ->where('instruksi', '!=', '')
                ->orderByDesc('id')
                ->limit(50)
                ->pluck('instruksi')
                ->unique()
                ->take(8)
                ->values(),
        ]);
    }
}
