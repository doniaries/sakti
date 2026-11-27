<?php

namespace App\Livewire\Mobile\Lhk;

use App\Models\AbsensiHarian;
use App\Models\Aktivitas;
use App\Models\JadwalKerja;
use App\Models\Lhk;
use App\Models\LhkPekerjaan;
use App\Models\RencanaAksi;
use App\Rules\PublicGoogleDriveUrl;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.mobile')]
#[Title('Buat LHK - Absensi LHK Mobile')]
class Form extends Component
{
    public $tanggal;
    public $absenHariIni;
    public ?Lhk $existingLhk = null;

    public $pekerjaans = [];
    public $aktivitasList = [];

    protected function rules(): array
    {
        return [
            'tanggal' => 'required|date',
            'pekerjaans.*.jam_mulai' => 'required',
            'pekerjaans.*.jam_selesai' => 'required',
            'pekerjaans.*.rencana_aksi_id' => 'nullable|integer|exists:rencana_aksis,id',
            'pekerjaans.*.hasil_pekerjaan' => 'required',
            'pekerjaans.*.bukti_url' => [
                'nullable',
                'url',
                new PublicGoogleDriveUrl(),
            ],
        ];
    }

    public function getGoogleDrivePreviewUrl(?string $url): ?string
    {
        if (empty($url)) {
            return null;
        }

        $host = strtolower((string) parse_url($url, PHP_URL_HOST));
        if (! in_array($host, ['drive.google.com', 'docs.google.com'], true)) {
            return null;
        }

        if (preg_match('~drive\.google\.com/drive/(?:u/\d+/)?folders/([a-zA-Z0-9_-]+)~', $url, $m)) {
            return "https://drive.google.com/embeddedfolderview?id={$m[1]}#grid";
        }

        if (preg_match('~drive\.google\.com/file/d/([a-zA-Z0-9_-]+)~', $url, $m)) {
            return "https://drive.google.com/file/d/{$m[1]}/preview";
        }

        if (preg_match('~drive\.google\.com/open\?id=([a-zA-Z0-9_-]+)~', $url, $m)) {
            return "https://drive.google.com/file/d/{$m[1]}/preview";
        }

        if (preg_match('~docs\.google\.com/(document|spreadsheets|presentation)/d/([a-zA-Z0-9_-]+)~', $url, $m)) {
            return "https://docs.google.com/{$m[1]}/d/{$m[2]}/preview";
        }

        return null;
    }

    public function mount()
    {
        $queryTanggal = request()->query('tanggal');
        if ($queryTanggal) {
            try {
                $this->tanggal = Carbon::parse($queryTanggal)->format('Y-m-d');
            } catch (\Throwable $e) {
                $this->tanggal = Carbon::today()->format('Y-m-d');
            }
        } else {
            $this->tanggal = Carbon::today()->format('Y-m-d');
        }

        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        $this->aktivitasList = Aktivitas::untukUser($user)
            ->with(['rencanaAksi:id,sasaran_sub_kegiatan,rencana_aksi', 'bidang:id,nama'])
            ->get();
        $this->loadAbsen();
    }

    public function updatedTanggal()
    {
        $this->loadAbsen();
    }

    public function loadAbsen()
    {
        $this->absenHariIni = AbsensiHarian::where('user_id', Auth::id())
            ->whereDate('tanggal', $this->tanggal)
            ->first();

        $this->existingLhk = Lhk::with('pekerjaans')
            ->where('user_id', Auth::id())
            ->whereDate('tanggal', $this->tanggal)
            ->first();

        if ($this->existingLhk && $this->existingLhk->pekerjaans->isNotEmpty()) {
            $this->pekerjaans = $this->existingLhk->pekerjaans->map(function (LhkPekerjaan $p) {
                return [
                    'jam_mulai' => $p->jam_mulai ? substr((string) $p->jam_mulai, 0, 5) : '',
                    'jam_selesai' => $p->jam_selesai ? substr((string) $p->jam_selesai, 0, 5) : '',
                    'lama_menit' => (int) ($p->lama_menit ?? 0),
                    'rencana_aksi_id' => $p->rencana_aksi_id ?? '',
                    'aktivitas_id' => $p->aktivitas_id,
                    'hasil_pekerjaan' => $p->hasil_pekerjaan ?? '',
                    'bukti_url' => $p->bukti_url ?? '',
                ];
            })->toArray();
        } else {
            $this->pekerjaans = [];
            $this->addPekerjaan();
        }
    }

    /**
     * Tentukan waktu awal kegiatan LHK:
     * - Tidak terlambat: mulai dari jam masuk resmi jadwal (misal 07:30)
     * - Terlambat: mulai dari jam absensi masuk aktual
     */
    public function getWaktuAwalKegiatan(): string
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        $jadwal = $user?->getJadwalKerja() ?? JadwalKerja::getDefault();
        $jamMasukResmi = $jadwal ? $jadwal->getJamMasukHari($this->tanggal) : '07:30';
        $jamAbsenMasuk = $this->absenHariIni?->jam_masuk
            ? substr((string) $this->absenHariIni->jam_masuk, 0, 5)
            : null;

        if ($jamAbsenMasuk && $jamAbsenMasuk > $jamMasukResmi) {
            return $jamAbsenMasuk;
        }

        return $jamMasukResmi;
    }

    public function updatedPekerjaans($value, string $key): void
    {
        $parts = explode('.', $key);
        if (count($parts) !== 2) {
            return;
        }

        [$indexStr, $field] = $parts;
        $index = (int) $indexStr;
        if (! isset($this->pekerjaans[$index])) {
            return;
        }

        if ($field === 'aktivitas_id') {
            $aktivitas = $value ? Aktivitas::find((int) $value) : null;
            if ($aktivitas) {
                $jamMulai = $this->resolveJamMulaiUntukIndex($index);
                $menit = max(1, (int) ($aktivitas->menit ?: 30));
                $jamSelesai = Carbon::createFromFormat('H:i', $jamMulai)->addMinutes($menit)->format('H:i');

                $this->pekerjaans[$index]['jam_mulai'] = $jamMulai;
                $this->pekerjaans[$index]['jam_selesai'] = $jamSelesai;
                $this->pekerjaans[$index]['lama_menit'] = $menit;
                if ($aktivitas->rencana_aksi_id && empty($this->pekerjaans[$index]['rencana_aksi_id'])) {
                    $this->pekerjaans[$index]['rencana_aksi_id'] = $aktivitas->rencana_aksi_id;
                }
            }
            return;
        }

        if ($field === 'jam_mulai' && ! empty($value)) {
            $aktivitasId = $this->pekerjaans[$index]['aktivitas_id'] ?? null;
            $menit = 0;
            if ($aktivitasId && ($aktivitas = Aktivitas::find((int) $aktivitasId))) {
                $menit = (int) $aktivitas->menit;
            } elseif (! empty($this->pekerjaans[$index]['lama_menit'])) {
                $menit = (int) $this->pekerjaans[$index]['lama_menit'];
            }

            if ($menit > 0) {
                try {
                    $this->pekerjaans[$index]['jam_selesai'] = Carbon::createFromFormat('H:i', substr((string) $value, 0, 5))
                        ->addMinutes($menit)
                        ->format('H:i');
                    $this->pekerjaans[$index]['lama_menit'] = $menit;
                } catch (\Throwable $e) {
                }
            }
            return;
        }

        if ($field === 'jam_selesai' && ! empty($value) && ! empty($this->pekerjaans[$index]['jam_mulai'])) {
            try {
                $mulai = Carbon::parse($this->pekerjaans[$index]['jam_mulai']);
                $selesai = Carbon::parse($value);
                $this->pekerjaans[$index]['lama_menit'] = $selesai->greaterThan($mulai)
                    ? (int) $mulai->diffInMinutes($selesai)
                    : 0;
            } catch (\Throwable $e) {
            }
        }
    }

    protected function resolveJamMulaiUntukIndex(int $index): string
    {
        if ($index > 0 && ! empty($this->pekerjaans[$index - 1]['jam_selesai'])) {
            return substr((string) $this->pekerjaans[$index - 1]['jam_selesai'], 0, 5);
        }

        if (! empty($this->pekerjaans[$index]['jam_mulai'])) {
            return substr((string) $this->pekerjaans[$index]['jam_mulai'], 0, 5);
        }

        return $this->getWaktuAwalKegiatan();
    }

    public function canFillLhk(): bool
    {
        if ($this->existingLhk) {
            return true;
        }

        if (! $this->absenHariIni) {
            return false;
        }

        $status = $this->absenHariIni->status?->value ?? $this->absenHariIni->status;
        $sudahAbsenMasuk = ! empty($this->absenHariIni->log_masuk_id) || ! empty($this->absenHariIni->jam_masuk);

        return $sudahAbsenMasuk || in_array($status, ['hadir', 'terlambat', 'dinas_luar'], true);
    }

    public function addPekerjaan()
    {
        $nextIndex = count($this->pekerjaans);
        $defaultMulai = $nextIndex > 0 && ! empty($this->pekerjaans[$nextIndex - 1]['jam_selesai'])
            ? substr((string) $this->pekerjaans[$nextIndex - 1]['jam_selesai'], 0, 5)
            : $this->getWaktuAwalKegiatan();

        $this->pekerjaans[] = [
            'jam_mulai' => $defaultMulai,
            'jam_selesai' => '',
            'lama_menit' => 0,
            'rencana_aksi_id' => '',
            'aktivitas_id' => null,
            'hasil_pekerjaan' => '',
            'bukti_url' => '',
        ];
    }

    public function removePekerjaan($index)
    {
        unset($this->pekerjaans[$index]);
        $this->pekerjaans = array_values($this->pekerjaans);
    }

    public function save()
    {
        $this->validate();

        if (! $this->canFillLhk()) {
            $this->dispatch('notify', [
                'type' => 'error',
                'title' => 'Belum Absen Masuk',
                'message' => 'Anda harus mengambil absen masuk (pagi) terlebih dahulu untuk mengisi LHK pada tanggal ini.',
            ]);
            return;
        }

        if (empty($this->pekerjaans)) {
            $this->dispatch('notify', [
                'type' => 'error',
                'title' => 'Kegiatan Kosong',
                'message' => 'Minimal 1 pekerjaan harus diisi.',
            ]);
            return;
        }

        $rawStatus = $this->absenHariIni ? ($this->absenHariIni->status?->value ?? $this->absenHariIni->status) : null;
        $statusKehadiran = match ($rawStatus) {
            'dinas_luar' => 'Dinas Luar',
            'izin' => 'Izin',
            'sakit' => 'Sakit',
            default => $this->existingLhk?->status_kehadiran ?? 'Hadir',
        };

        $lhk = Lhk::updateOrCreate(
            ['user_id' => Auth::id(), 'tanggal' => $this->tanggal],
            [
                'jam_masuk' => $this->absenHariIni?->jam_masuk ?? $this->existingLhk?->jam_masuk,
                'jam_pulang' => $this->absenHariIni?->jam_pulang ?? $this->existingLhk?->jam_pulang,
                'log_absensi_masuk_id' => $this->absenHariIni?->log_masuk_id ?? $this->existingLhk?->log_absensi_masuk_id,
                'log_absensi_pulang_id' => $this->absenHariIni?->log_pulang_id ?? $this->existingLhk?->log_absensi_pulang_id,
                'status_kehadiran' => $statusKehadiran,
            ]
        );

        LhkPekerjaan::where('lhk_id', $lhk->id)->delete();

        foreach ($this->pekerjaans as $p) {
            $lamaMenit = 0;
            try {
                if (! empty($p['jam_mulai']) && ! empty($p['jam_selesai'])) {
                    $mulai = Carbon::parse($p['jam_mulai']);
                    $selesai = Carbon::parse($p['jam_selesai']);
                    if ($selesai->greaterThan($mulai)) {
                        $lamaMenit = $mulai->diffInMinutes($selesai);
                    }
                }
            } catch (\Throwable $e) {
                $lamaMenit = 0;
            }

            LhkPekerjaan::create([
                'lhk_id' => $lhk->id,
                'jam_mulai' => $p['jam_mulai'],
                'jam_selesai' => $p['jam_selesai'],
                'lama_menit' => $lamaMenit,
                'rencana_aksi_id' => ! empty($p['rencana_aksi_id']) ? (int) $p['rencana_aksi_id'] : null,
                'aktivitas_id' => $p['aktivitas_id'] ?: null,
                'hasil_pekerjaan' => $p['hasil_pekerjaan'],
                'bukti_url' => $p['bukti_url'] ?: null,
            ]);
        }

        $this->dispatch('notify', [
            'type' => 'success',
            'title' => 'Berhasil!',
            'message' => 'LHK Berhasil disimpan!',
        ]);

        return $this->redirect(route('mobile.lhk.index'), navigate: true);
    }

    public function render()
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        $rencanaAksiOptions = RencanaAksi::untukUser($user)
            ->get()
            ->groupBy(fn (RencanaAksi $ra): string => $ra->sasaran_sub_kegiatan ?: 'Umum');

        return view('livewire.mobile.lhk.form', [
            'bisaIsiLhk' => $this->canFillLhk(),
            'waktuAwalKegiatan' => $this->getWaktuAwalKegiatan(),
            'rencanaAksiOptions' => $rencanaAksiOptions,
        ]);
    }
}
