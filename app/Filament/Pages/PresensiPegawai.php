<?php

namespace App\Filament\Pages;

use App\Enums\StatusKehadiran;
use App\Models\AbsensiHarian;
use App\Models\JadwalKerja;
use App\Models\LogAbsensi;
use App\Models\User;
use Carbon\Carbon;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class PresensiPegawai extends Page
{
    protected string $view = 'filament.pages.presensi-pegawai';

    protected static \UnitEnum|string|null $navigationGroup = null;

    protected static ?int $navigationSort = 2;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-finger-print';

    protected static ?string $navigationLabel = 'Presensi Pegawai';

    protected static ?string $title = 'Presensi Pegawai (Portal Laptop)';

    public ?AbsensiHarian $todayAbsensi = null;

    public ?JadwalKerja $jadwal = null;

    public Collection $recentAbsensi;

    public string $todayDate = '';

    public string $jamMasukResmi = '07:30';

    public string $jamPulangResmi = '16:00';

    public string $bukaMasuk = '06:00';

    public string $tutupMasuk = '09:00';

    public int $toleransi = 15;

    public function mount(): void
    {
        $this->refreshData();
    }

    public function refreshData(): void
    {
        /** @var User|null $user */
        $user = Auth::user();
        if (!$user) {
            $this->recentAbsensi = collect();
            return;
        }

        $today = Carbon::today()->format('Y-m-d');
        $this->todayDate = Carbon::today()->locale('id')->isoFormat('dddd, D MMMM Y');

        $this->jadwal = $user->getJadwalKerja() ?? JadwalKerja::getDefault();
        if ($this->jadwal) {
            $this->bukaMasuk = substr($this->jadwal->buka_absen_masuk, 0, 5);
            $this->tutupMasuk = substr($this->jadwal->tutup_absen_masuk, 0, 5);
            $this->jamMasukResmi = $this->jadwal->getJamMasukHari($today);
            $this->jamPulangResmi = $this->jadwal->getJamPulangHari($today);
            $this->toleransi = (int) $this->jadwal->toleransi_terlambat_menit;
        }

        $this->todayAbsensi = AbsensiHarian::where('user_id', $user->id)
            ->where('tanggal', $today)
            ->first();

        $this->recentAbsensi = AbsensiHarian::where('user_id', $user->id)
            ->orderBy('tanggal', 'desc')
            ->limit(7)
            ->get();
    }

    public function punchMasuk(): void
    {
        /** @var User|null $user */
        $user = Auth::user();
        if (!$user) {
            return;
        }

        $today = Carbon::today()->format('Y-m-d');
        $now = Carbon::now()->format('H:i:s');
        $nowTime = substr($now, 0, 5);

        $this->refreshData();

        if ($this->todayAbsensi && filled($this->todayAbsensi->jam_masuk)) {
            Notification::make()
                ->title('Anda sudah mengambil absen masuk hari ini!')
                ->warning()
                ->send();
            return;
        }

        // Cek jam buka masuk
        if ($nowTime < $this->bukaMasuk) {
            Notification::make()
                ->title('Belum Waktu Absen Masuk!')
                ->body("Presensi masuk dibuka mulai pukul {$this->bukaMasuk} WIB s.d {$this->tutupMasuk} WIB.")
                ->warning()
                ->send();
            return;
        }

        // Cek tutup masuk
        if ($nowTime > $this->tutupMasuk) {
            if ($this->todayAbsensi) {
                $this->todayAbsensi->update(['status' => StatusKehadiran::TIDAK_HADIR]);
            } else {
                AbsensiHarian::create([
                    'user_id' => $user->id,
                    'tanggal' => $today,
                    'status' => StatusKehadiran::TIDAK_HADIR,
                    'keterangan' => 'Alpa / Terlewat Batas Absen Masuk (' . $this->tutupMasuk . ' WIB)',
                ]);
            }

            Notification::make()
                ->title('Batas Akhir Absen Masuk Telah Berakhir!')
                ->body("Batas akhir absen masuk adalah pukul {$this->tutupMasuk} WIB. Status tercatat Tidak Hadir / Alpa.")
                ->danger()
                ->send();

            $this->refreshData();
            return;
        }

        // Hitung toleransi keterlambatan
        $batasTepatWaktu = Carbon::parse("$today {$this->jamMasukResmi}:00")->addMinutes($this->toleransi)->format('H:i:s');
        $status = ($now <= $batasTepatWaktu) ? StatusKehadiran::HADIR : StatusKehadiran::TERLAMBAT;

        $nomorAbsensi = (string) ($user->nomor_absensi ?? $user->id);

        $log = LogAbsensi::create([
            'user_id' => $user->id,
            'nomor_absensi' => $nomorAbsensi,
            'waktu_absensi' => Carbon::now(),
            'perangkat' => 'web',
            'metode_verifikasi' => 'portal_laptop',
            'jenis' => 'masuk',
            'data_asli' => [
                'user_agent' => request()->userAgent(),
                'ip' => request()->ip(),
            ],
        ]);

        $absenHarian = AbsensiHarian::firstOrCreate(
            ['user_id' => $user->id, 'tanggal' => $today],
            ['status' => $status]
        );

        $absenHarian->update([
            'status' => $status,
            'log_masuk_id' => $log->id,
            'jam_masuk' => $now,
            'is_absen_mobile' => false,
        ]);

        $label = ($status === StatusKehadiran::HADIR) ? 'Tepat Waktu' : 'Terlambat';
        Notification::make()
            ->title('Absen Masuk Berhasil Tercatat!')
            ->body("Waktu masuk: {$now} WIB ({$label}). Selamat bekerja!")
            ->success()
            ->send();

        $this->refreshData();
    }

    public function punchPulang(): void
    {
        /** @var User|null $user */
        $user = Auth::user();
        if (!$user) {
            return;
        }

        $today = Carbon::today()->format('Y-m-d');
        $now = Carbon::now()->format('H:i:s');
        $nowTime = substr($now, 0, 5);

        $this->refreshData();

        if (!$this->todayAbsensi || empty($this->todayAbsensi->jam_masuk)) {
            Notification::make()
                ->title('Tidak Dapat Mengambil Absen Pulang!')
                ->body('Anda belum tercatat mengambil absen masuk pagi hari ini.')
                ->danger()
                ->send();
            return;
        }

        if (filled($this->todayAbsensi->jam_pulang)) {
            Notification::make()
                ->title('Anda sudah mengambil absen pulang hari ini!')
                ->info()
                ->send();
            return;
        }

        // Cek jam pulang resmi
        if ($nowTime < $this->jamPulangResmi) {
            Notification::make()
                ->title('Belum Waktu Pulang!')
                ->body("Presensi pulang hari ini baru dibuka mulai pukul {$this->jamPulangResmi} WIB.")
                ->warning()
                ->send();
            return;
        }

        $nomorAbsensi = (string) ($user->nomor_absensi ?? $user->id);

        $log = LogAbsensi::create([
            'user_id' => $user->id,
            'nomor_absensi' => $nomorAbsensi,
            'waktu_absensi' => Carbon::now(),
            'perangkat' => 'web',
            'metode_verifikasi' => 'portal_laptop',
            'jenis' => 'pulang',
            'data_asli' => [
                'user_agent' => request()->userAgent(),
                'ip' => request()->ip(),
            ],
        ]);

        $this->todayAbsensi->update([
            'log_pulang_id' => $log->id,
            'jam_pulang' => $now,
            'is_absen_mobile' => false,
        ]);

        Notification::make()
            ->title('Absen Pulang Berhasil Tercatat!')
            ->body("Waktu pulang: {$now} WIB. Terima kasih atas kerja keras Anda hari ini!")
            ->success()
            ->send();

        $this->refreshData();
    }
}

