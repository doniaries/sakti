<?php

namespace App\Livewire\Mobile;

use App\Models\AgendaDisposisi;
use App\Models\AgendaKegiatan;
use App\Models\User;
use App\Services\PushNotificationService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('components.layouts.mobile')]
class Agenda extends Component
{
    use WithFileUploads;

    public bool $showForm = false;
    public string $judul = '';
    public string $tanggal = '';
    public string $jam_mulai = '08:00';
    public string $jam_selesai = '';
    public string $tempat = '';
    public string $asal_undangan = '';
    public string $keterangan = '';
    /** @var array<int, int|string> */
    public array $disposisi_user_ids = [];
    public string $instruksi = '';
    public $lampiran_file;

    public function mount(): void
    {
        $this->tanggal = Carbon::today()->toDateString();
    }

    public function openCreate(): void
    {
        $this->reset(['judul', 'jam_selesai', 'tempat', 'asal_undangan', 'keterangan', 'disposisi_user_ids', 'instruksi', 'lampiran_file']);
        $this->tanggal = Carbon::today()->toDateString();
        $this->jam_mulai = '08:00';
        $this->showForm = true;
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
            'keterangan' => ['required', 'string'],
            'disposisi_user_ids' => ['required', 'array', 'min:1'],
            'disposisi_user_ids.*' => ['integer', 'exists:users,id'],
            'instruksi' => ['nullable', 'string', 'max:500'],
            'lampiran_file' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],
        ]);

        $lampiranPath = $this->lampiran_file
            ? $this->lampiran_file->store('agenda-lampiran', 'public')
            : null;

        $agenda = DB::transaction(function () use ($validated, $lampiranPath): AgendaKegiatan {
            $agenda = AgendaKegiatan::create([
                'judul' => $validated['judul'],
                'tanggal' => $validated['tanggal'],
                'jam_mulai' => $validated['jam_mulai'],
                'jam_selesai' => $validated['jam_selesai'] ?: null,
                'tempat' => $validated['tempat'],
                'asal_undangan' => $validated['asal_undangan'] ?: null,
                'keterangan' => $validated['keterangan'] ?: null,
                'lampiran_file' => $lampiranPath,
                'created_by' => Auth::id(),
            ]);

            $userIds = collect($validated['disposisi_user_ids'] ?? [])
                ->map(fn ($id) => (int) $id)
                ->filter(fn (int $id) => $id > 0)
                ->unique()
                ->values();

            foreach ($userIds as $uid) {
                AgendaDisposisi::create([
                    'agenda_kegiatan_id' => $agenda->id,
                    'user_id' => $uid,
                    'instruksi' => $validated['instruksi'] ?: 'Hadir mewakili / menindaklanjuti',
                    'notified_at' => now(),
                ]);
            }

            return $agenda;
        });

        $recipientIds = collect($validated['disposisi_user_ids'] ?? [])->map(fn ($id) => (int) $id)->filter()->unique()->all();
        if ($recipientIds !== []) {
            $recipients = User::whereIn('id', $recipientIds)->where('aktif', true)->get();
            $waktu = Carbon::parse($agenda->tanggal)->translatedFormat('d M Y') . ' ' . substr((string) $agenda->jam_mulai, 0, 5);
            $instruksiTeks = $validated['instruksi'] ?: 'Hadir mewakili / menindaklanjuti';
            PushNotificationService::sendToUsers(
                $recipients,
                'Perintah Menghadiri Agenda',
                "{$agenda->judul} ({$waktu} di {$agenda->tempat}) — {$instruksiTeks}",
                ['url' => route('mobile.agenda')],
                'info'
            );
        }

        $this->showForm = false;
        $this->reset(['lampiran_file']);
        $this->dispatch('notify', type: 'success', title: 'Agenda Ditambahkan', message: 'Agenda kegiatan & disposisi berhasil disimpan.');
    }

    public function render()
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        $today = Carbon::today()->toDateString();

        $isKadisOrAdmin = $user && $user->hasAnyRole(['super_admin', 'superadmin', 'admin', 'pa', 'kadis']);

        $baseQuery = AgendaKegiatan::query()
            ->with(['disposisis' => fn ($q) => $q->where('user_id', $user?->id)])
            ->when(! $isKadisOrAdmin && $user, function ($query) use ($user) {
                $query->where(function ($q) use ($user) {
                    $q->whereHas('disposisis', fn ($d) => $d->where('user_id', $user->id))
                        ->orWhere('created_by', $user->id);
                });
            });

        $todayAgendas = (clone $baseQuery)
            ->whereDate('tanggal', $today)
            ->orderBy('jam_mulai')
            ->get();

        $upcomingAgendas = (clone $baseQuery)
            ->whereDate('tanggal', '>', $today)
            ->orderBy('tanggal')
            ->orderBy('jam_mulai')
            ->limit(15)
            ->get();

        return view('livewire.mobile.agenda', [
            'todayAgendas' => $todayAgendas,
            'upcomingAgendas' => $upcomingAgendas,
            'pegawais' => User::where('aktif', true)->with('jabatan:id,nama')->orderBy('id')->get(['id', 'name', 'jabatan_id']),
        ]);
    }
}
