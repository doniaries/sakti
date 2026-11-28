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
    public $lampiran_file;

    public function mount(): void
    {
        $this->tanggal = Carbon::today()->toDateString();
    }

    public function simpanAgenda(): mixed
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

        $agenda = DB::transaction(function () use ($validated, $lampiranPath): AgendaKegiatan {
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

        $this->dispatch('notify', type: 'success', title: 'Agenda Ditambahkan', message: 'Agenda kegiatan & disposisi berhasil disimpan.');

        return $this->redirect(route('mobile.agenda'), navigate: true);
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
