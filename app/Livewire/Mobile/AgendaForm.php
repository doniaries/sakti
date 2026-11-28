<?php

namespace App\Livewire\Mobile;

use App\Models\AgendaDisposisi;
use App\Models\AgendaKegiatan;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
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

    public ?int $agendaId = null;
    public ?string $existingLampiran = null;
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

    public function mount(?AgendaKegiatan $agenda = null): void
    {
        if ($agenda && $agenda->exists) {
            /** @var \App\Models\User|null $user */
            $user = Auth::user();
            $canManage = $user && ((int) $agenda->created_by === (int) $user->id
                || $user->hasAnyRole(['super_admin', 'superadmin', 'admin', 'pa', 'kadis']));
            abort_unless($canManage, 403);

            $agenda->load('disposisis');
            $this->agendaId = $agenda->id;
            $this->judul = $agenda->judul;
            $this->tanggal = $agenda->tanggal?->toDateString() ?? Carbon::today()->toDateString();
            $this->jam_mulai = substr((string) $agenda->jam_mulai, 0, 5);
            $this->jam_selesai = $agenda->jam_selesai ? substr((string) $agenda->jam_selesai, 0, 5) : '';
            $this->tempat = $agenda->tempat;
            $this->asal_undangan = (string) ($agenda->asal_undangan ?? '');
            $this->existingLampiran = $agenda->lampiran_file;
            $this->disposisi_user_ids = $agenda->disposisis->pluck('user_id')->map(fn ($id): string => (string) $id)->all();
            $this->instruksi = (string) ($agenda->disposisis->pluck('instruksi')->filter()->first() ?? '');
            return;
        }

        $this->tanggal = Carbon::today()->toDateString();
    }

    public function simpanAgenda(): void
    {
        $validated = $this->validate([
            'judul' => ['required', 'string', 'max:255'],
            'tanggal' => ['required', 'date'],
            'jam_mulai' => ['required', 'string'],
            'jam_selesai' => ['nullable', 'string'],
            'tempat' => ['required', 'string', 'max:255'],
            'asal_undangan' => ['required', 'string', 'max:255'],
            'disposisi_user_ids' => ['required', 'array', 'min:1'],
            'disposisi_user_ids.*' => ['integer', 'exists:users,id'],
            'instruksi' => ['nullable', 'string', 'max:500'],
            'lampiran_file' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],
        ]);

        $lampiranPath = $this->lampiran_file
            ? $this->lampiran_file->store('agenda-lampiran', 'public')
            : $this->existingLampiran;

        if ($this->agendaId) {
            $agenda = AgendaKegiatan::findOrFail($this->agendaId);
            /** @var \App\Models\User|null $user */
            $user = Auth::user();
            $canManage = $user && ((int) $agenda->created_by === (int) $user->id
                || $user->hasAnyRole(['super_admin', 'superadmin', 'admin', 'pa', 'kadis']));
            abort_unless($canManage, 403);

            DB::transaction(function () use ($agenda, $validated, $lampiranPath): void {
                $agenda->update([
                    'judul' => $validated['judul'],
                    'tanggal' => $validated['tanggal'],
                    'jam_mulai' => $validated['jam_mulai'],
                    'jam_selesai' => $validated['jam_selesai'] ?: null,
                    'tempat' => $validated['tempat'],
                    'asal_undangan' => $validated['asal_undangan'] ?: null,
                    'lampiran_file' => $lampiranPath,
                ]);

                $agenda->syncDisposisis(
                    $validated['disposisi_user_ids'] ?? [],
                    $validated['instruksi'] ?: 'Hadir mewakili / menindaklanjuti'
                );
            });

            $this->dispatch('notify', type: 'success', title: 'Agenda Diperbarui', message: 'Perubahan agenda kegiatan berhasil disimpan.');
            $this->redirect(route('mobile.agenda'), navigate: true);
            return;
        }

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

    /**
     * @return \Illuminate\Support\Collection<int, string>
     */
    private function recentDistinctValues(string $modelClass, string $column, int $limit = 5): \Illuminate\Support\Collection
    {
        return $modelClass::query()
            ->whereNotNull($column)
            ->where($column, '!=', '')
            ->orderByDesc('id')
            ->limit(30)
            ->pluck($column)
            ->unique()
            ->take($limit)
            ->values();
    }

    public function render()
    {
        return view('livewire.mobile.agenda-form', [
            'pegawais' => Cache::remember('mobile.agenda.pegawais_aktif', 300, fn () => User::query()
                ->where('aktif', true)
                ->with('jabatan:id,nama')
                ->orderBy('id')
                ->get(['id', 'name', 'jabatan_id'])),
            'riwayatJudul' => $this->recentDistinctValues(AgendaKegiatan::class, 'judul'),
            'riwayatTempat' => $this->recentDistinctValues(AgendaKegiatan::class, 'tempat'),
            'riwayatAsalUndangan' => $this->recentDistinctValues(AgendaKegiatan::class, 'asal_undangan'),
            'riwayatInstruksi' => $this->recentDistinctValues(AgendaDisposisi::class, 'instruksi'),
        ]);
    }
}
