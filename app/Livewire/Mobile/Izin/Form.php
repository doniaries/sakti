<?php

namespace App\Livewire\Mobile\Izin;

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
    public $tanggal_mulai;
    public $tanggal_selesai;
    public $jumlah_hari = 1;
    public $alasan = '';
    public $lampiran;

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

    public function setKategori($kategori)
    {
        $this->kategoriFilter = $kategori;
        $this->autoSelectJenisIzin();
    }

    protected function autoSelectJenisIzin()
    {
        $query = JenisIzin::query()->where('aktif', true);

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
                if ($date->isWeekday()) {
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

        // Validasi pembatasan kuota cuti pegawai
        if ($selectedJenis?->kategori === 'cuti') {
            $sisaCuti = $user->sisa_cuti;
            if ($this->jumlah_hari > $sisaCuti) {
                $this->addError('jumlah_hari', "Jumlah hari cuti ({$this->jumlah_hari} hari) melebihi sisa kuota cuti Anda ({$sisaCuti} hari).");
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

        PengajuanIzin::create([
            'user_id' => $user->id,
            'jenis_izin_id' => $this->jenis_izin_id,
            'tanggal_mulai' => $this->tanggal_mulai,
            'tanggal_selesai' => $this->tanggal_selesai,
            'jumlah_hari' => $this->jumlah_hari,
            'alasan' => $this->alasan,
            'lampiran' => $pathLampiran,
            'status' => 'menunggu',
        ]);

        session()->flash('success', 'Permohonan berhasil dikirim dan menunggu persetujuan atasan.');

        return redirect()->route('mobile.izin.index');
    }

    public function render()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $queryJenis = JenisIzin::query()->where('aktif', true);

        if ($this->kategoriFilter !== 'semua') {
            $queryJenis->where('kategori', $this->kategoriFilter);
        }

        $jenisIzins = $queryJenis->get();
        $selectedJenis = JenisIzin::find($this->jenis_izin_id);

        return view('livewire.mobile.izin.form', [
            'user' => $user,
            'jenisIzins' => $jenisIzins,
            'selectedJenis' => $selectedJenis,
            'totalKuotaCuti' => $user->total_kuota_cuti,
            'cutiTerpakai' => $user->cuti_terpakai,
            'sisaCuti' => $user->sisa_cuti,
        ]);
    }
}
