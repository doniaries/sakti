<?php

namespace App\Livewire\Mobile\Izin;

use App\Enums\JenisKelamin;
use App\Models\HariLibur;
use App\Models\JenisIzin;
use App\Models\PengajuanIzin;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('components.layouts.mobile')]
#[Title('Form Pengajuan Izin & Cuti - Absensi LHK Mobile')]
class Form extends Component
{
    use WithFileUploads;

    public $kategoriFilter = 'semua';
    public $jenis_izin_id = '';
    public ?string $tanggal_mulai = null;
    public ?string $tanggal_selesai = null;
    public $jumlah_hari = 1;
    public $alasan = '';
    public mixed $lampiran = null;

    public function mount()
    {
        $kategoriParam = request()->query('kategori');
        if ($kategoriParam && in_array($kategoriParam, ['izin', 'sakit', 'cuti'])) {
            $this->kategoriFilter = $kategoriParam;
        }

        $this->tanggal_mulai = now()->format('Y-m-d');
        $this->tanggal_selesai = now()->format('Y-m-d');

        $this->autoSelectJenisIzin();
        $this->hitungJumlahHari();
    }

    public function setKategori(string $kategori): void
    {
        $this->kategoriFilter = $kategori;
        $this->autoSelectJenisIzin();
    }

    protected function autoSelectJenisIzin()
    {
        $user = Auth::user();
        $isPerempuan = $user?->jenis_kelamin === JenisKelamin::PEREMPUAN;
        $query = JenisIzin::query()
            ->where('aktif', true)
            ->when(! $isPerempuan, fn ($q) => $q->where('kode', '!=', 'cuti_melahirkan'));

        if ($this->kategoriFilter !== 'semua') {
            $query->where('kategori', $this->kategoriFilter);
        }

        $first = $query->first();
        $this->jenis_izin_id = $first ? (string) $first->id : '';
    }

    public function updatedTanggalMulai()
    {
        if ($this->tanggal_selesai && $this->tanggal_mulai > $this->tanggal_selesai) {
            $this->tanggal_selesai = $this->tanggal_mulai;
        }
        $this->hitungJumlahHari();
    }

    public function updatedTanggalSelesai()
    {
        if ($this->tanggal_mulai && $this->tanggal_selesai < $this->tanggal_mulai) {
            $this->tanggal_mulai = $this->tanggal_selesai;
        }
        $this->hitungJumlahHari();
    }

    public function hitungJumlahHari()
    {
        if (!$this->tanggal_mulai || !$this->tanggal_selesai) {
            $this->jumlah_hari = 1;
            return;
        }

        try {
            $start = Carbon::parse($this->tanggal_mulai);
            $end = Carbon::parse($this->tanggal_selesai);

            $count = 0;
            $period = CarbonPeriod::create($start, $end);
            foreach ($period as $date) {
                if (! HariLibur::isHariLibur($date)) {
                    $count++;
                }
            }
            $this->jumlah_hari = max(1, $count);
        } catch (\Exception $e) {
            $this->jumlah_hari = 1;
        }
    }

    public function save()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $selectedJenis = JenisIzin::find($this->jenis_izin_id);
        $wajibLampiran = ($selectedJenis?->kategori === 'sakit' && ($selectedJenis?->butuh_lampiran ?? false));

        // Validasi pembatasan kuota cuti pegawai sesuai jenis cuti yang dipilih
        if ($selectedJenis?->kategori === 'cuti' && $selectedJenis->kuota_hari) {
            $kuotaInfo = $this->hitungKuotaJenis($user, $selectedJenis);
            $sisaCuti = $kuotaInfo['sisa'];
            if ($this->jumlah_hari > $sisaCuti) {
                $this->addError('jumlah_hari', "Jumlah hari cuti ({$this->jumlah_hari} hari) melebihi sisa kuota {$selectedJenis->nama} Anda ({$sisaCuti} hari).");
                return;
            }
        }

        $rules = [
            'jenis_izin_id' => 'required|exists:jenis_izins,id',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'alasan' => 'required|string|min:5|max:500',
        ];

        // Lampiran hanya diwajibkan untuk Sakit dengan Surat Dokter
        if ($wajibLampiran) {
            $rules['lampiran'] = 'required|file|mimes:pdf,jpg,jpeg,png|max:2048';
        } else {
            $rules['lampiran'] = 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048';
        }

        $this->validate($rules, [
            'jenis_izin_id.required' => 'Pilih jenis izin/cuti terlebih dahulu.',
            'alasan.required' => 'Alasan / keterangan permohonan wajib diisi.',
            'lampiran.required' => 'Wajib mengunggah Surat Keterangan Sakit dari Dokter.',
            'lampiran.max' => 'Ukuran berkas lampiran maksimal 2MB.',
        ]);

        $pathLampiran = null;
        if ($wajibLampiran && $this->lampiran) {
            $pathLampiran = $this->lampiran->store('lampiran-izin', 'public');
        } elseif ($this->lampiran && $selectedJenis?->kategori !== 'izin') {
            $pathLampiran = $this->lampiran->store('lampiran-izin', 'public');
        }

        $pengajuan = PengajuanIzin::create([
            'user_id' => $user->id,
            'jenis_izin_id' => $this->jenis_izin_id,
            'tanggal_mulai' => $this->tanggal_mulai,
            'tanggal_selesai' => $this->tanggal_selesai,
            'jumlah_hari' => $this->jumlah_hari,
            'alasan' => $this->alasan,
            'lampiran' => $pathLampiran,
            'status' => 'menunggu',
        ]);

        // Kirim Notifikasi Database ke Atasan Langsung dan Admin Kepegawaian
        try {
            $recipients = collect();
            if ($user->atasan) {
                $recipients->push($user->atasan);
            }
            $admins = \App\Models\User::role(['super_admin', 'admin'])->get();
            $recipients = $recipients->merge($admins)->unique('id');

            foreach ($recipients as $recipient) {
                \Filament\Notifications\Notification::make()
                    ->title("Pengajuan Izin Baru: {$user->name}")
                    ->body("Pegawai {$user->name} mengajukan {$selectedJenis?->nama} ({$this->jumlah_hari} hari: {$this->tanggal_mulai} s.d {$this->tanggal_selesai}).")
                    ->icon('heroicon-o-document-text')
                    ->warning()
                    ->sendToDatabase($recipient);
            }
        } catch (\Throwable $e) {
            // Silently continue if notification fails
        }

        session()->flash('success', 'Permohonan berhasil dikirim dan menunggu persetujuan atasan.');

        if ($selectedJenis?->kategori === 'cuti') {
            return redirect()->route('pengajuan-izin.cetak-pdf', $pengajuan);
        }

        if ($selectedJenis?->kategori === 'cuti') {
            return redirect()->route('pengajuan-izin.cetak-pdf', $pengajuan);
        }

        return redirect()->route('mobile.izin.index');
    }

    protected function hitungKuotaJenis(?\App\Models\User $user, ?JenisIzin $jenis): array
    {
        if (! $jenis || ! $jenis->kuota_hari) {
            return [
                'total' => (int) ($user->total_kuota_cuti ?? 12),
                'terpakai' => (int) ($user->cuti_terpakai ?? 0),
                'sisa' => (int) ($user->sisa_cuti ?? 12),
            ];
        }

        $tahun = now()->year;
        $total = (int) $jenis->kuota_hari;
        $terpakai = 0;

        $riwayat = PengajuanIzin::query()
            ->where('user_id', $user->id)
            ->where('jenis_izin_id', $jenis->id)
            ->where('status', 'disetujui')
            ->whereYear('tanggal_mulai', $tahun)
            ->get(['tanggal_mulai', 'tanggal_selesai']);

        foreach ($riwayat as $item) {
            $period = CarbonPeriod::create(
                Carbon::parse($item->tanggal_mulai),
                Carbon::parse($item->tanggal_selesai)
            );
            foreach ($period as $date) {
                if ($date->year === $tahun && ! HariLibur::isHariLibur($date)) {
                    $terpakai++;
                }
            }
        }

        return [
            'total' => $total,
            'terpakai' => $terpakai,
            'sisa' => max(0, $total - $terpakai),
        ];
    }

    public function render()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $isPerempuan = $user?->jenis_kelamin === JenisKelamin::PEREMPUAN;
        $queryJenis = JenisIzin::query()
            ->where('aktif', true)
            ->when(! $isPerempuan, fn ($q) => $q->where('kode', '!=', 'cuti_melahirkan'));

        if ($this->kategoriFilter !== 'semua') {
            $queryJenis->where('kategori', $this->kategoriFilter);
        }

        $jenisIzins = $queryJenis->get();
        $selectedJenis = JenisIzin::find($this->jenis_izin_id);
        $kuotaInfo = $this->hitungKuotaJenis($user, $selectedJenis);

        return view('livewire.mobile.izin.form', [
            'user' => $user,
            'jenisIzins' => $jenisIzins,
            'selectedJenis' => $selectedJenis,
            'totalKuotaCuti' => $kuotaInfo['total'],
            'cutiTerpakai' => $kuotaInfo['terpakai'],
            'sisaCuti' => $kuotaInfo['sisa'],
        ]);
    }
}
