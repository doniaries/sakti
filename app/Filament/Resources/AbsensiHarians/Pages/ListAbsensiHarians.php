<?php

namespace App\Filament\Resources\AbsensiHarians\Pages;

use App\Enums\JenisKehadiran;
use App\Enums\StatusKehadiran;
use App\Filament\Resources\AbsensiHarians\AbsensiHarianResource;
use App\Models\AbsensiHarian;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;
use ZeeshanTariq\FilamentStickyColumns\Concerns\InteractsWithStickyableColumns;

class ListAbsensiHarians extends ListRecords
{
    use InteractsWithStickyableColumns;

    protected static string $resource = AbsensiHarianResource::class;

    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Resources\AbsensiHarians\Widgets\AbsensiHarianStats::class,
        ];
    }

    public function getWidgetData(): array
    {
        $data = parent::getWidgetData();
        
        $data['tableColumnSearches'] = $data['tableColumnSearches'] ?? [];
        $data['tableFilters'] = $data['tableFilters'] ?? [];
        $data['tableSearch'] = $data['tableSearch'] ?? '';
        
        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('exportPresencySync')
                ->label('Export Sync Presency (JSON)')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('info')
                ->modalHeading('Export Data Absensi untuk Presency Kominfo')
                ->modalDescription('Pencocokan pegawai menggunakan NIP (bukan email). Pilih 1 pegawai jika ingin uji coba pada akun Anda sendiri terlebih dahulu.')
                ->schema([
                    Select::make('bulan')
                        ->label('Bulan')
                        ->options([
                            1 => 'Januari',
                            2 => 'Februari',
                            3 => 'Maret',
                            4 => 'April',
                            5 => 'Mei',
                            6 => 'Juni',
                            7 => 'Juli',
                            8 => 'Agustus',
                            9 => 'September',
                            10 => 'Oktober',
                            11 => 'November',
                            12 => 'Desember',
                        ])
                        ->default((int) now()->format('n'))
                        ->required(),
                    TextInput::make('tahun')
                        ->label('Tahun')
                        ->numeric()
                        ->default((int) now()->format('Y'))
                        ->minValue(2020)
                        ->maxValue(2100)
                        ->required(),
                    Select::make('user_id')
                        ->label('Filter 1 Pegawai (Opsional / Uji Coba)')
                        ->placeholder('Semua Pegawai yang memiliki NIP')
                        ->searchable()
                        ->options(fn (): array => User::query()
                            ->whereNotNull('nip')
                            ->where('nip', '!=', '')
                            ->orderBy('name')
                            ->get()
                            ->mapWithKeys(fn (User $u): array => [$u->id => "{$u->name} ({$u->nip})"])
                            ->all())
                        ->default(fn () => Auth::id()),
                ])
                ->action(function (array $data) {
                    $bulan = (int) $data['bulan'];
                    $tahun = (int) $data['tahun'];
                    $userId = $data['user_id'] ?? null;

                    $query = AbsensiHarian::query()
                        ->with('user:id,name,nip')
                        ->whereYear('tanggal', $tahun)
                        ->whereMonth('tanggal', $bulan)
                        ->whereHas('user', fn ($q) => $q->whereNotNull('nip')->where('nip', '!=', ''));

                    if (filled($userId)) {
                        $query->where('user_id', (int) $userId);
                    }

                    $records = $query->orderBy('tanggal')->get()
                        ->map(function (AbsensiHarian $row): ?array {
                            $status = $row->status;
                            $jenis = $row->jenis_kehadiran;

                            if ($status === StatusKehadiran::LIBUR) {
                                return null;
                            }

                            $keteranganId = match (true) {
                                $status === StatusKehadiran::DINAS_LUAR || $jenis === JenisKehadiran::DINAS_LUAR => '2',
                                $status === StatusKehadiran::HADIR || $status === StatusKehadiran::TERLAMBAT => '1',
                                $status === StatusKehadiran::IZIN => '3',
                                $status === StatusKehadiran::SAKIT => '5',
                                $status === StatusKehadiran::CUTI => '7',
                                $status === StatusKehadiran::TIDAK_HADIR => '6',
                                default => null,
                            };

                            if ($keteranganId === null) {
                                return null;
                            }

                            $nipClean = preg_replace('/[\s-]+/', '', (string) $row->user?->nip);
                            if ($nipClean === '') {
                                return null;
                            }

                            $jamMasuk = filled($row->jam_masuk)
                                ? substr((string) $row->jam_masuk, 0, 8)
                                : '07:30:00';

                            return [
                                'nip' => $nipClean,
                                'nama' => (string) $row->user?->name,
                                'tanggal' => $row->tanggal?->format('Y-m-d'),
                                'keterangan_id' => $keteranganId,
                                'keterangan_label' => $status?->getLabel() ?? 'Hadir',
                                'jam_masuk' => $keteranganId === '1' ? $jamMasuk : '00:00:00',
                            ];
                        })
                        ->filter()
                        ->values()
                        ->all();

                    $payload = [
                        'generated_at' => now()->toIso8601String(),
                        'periode' => sprintf('%04d-%02d', $tahun, $bulan),
                        'total' => count($records),
                        'records' => $records,
                    ];

                    $filename = sprintf('presency-sync-%04d-%02d.json', $tahun, $bulan);

                    return response()->streamDownload(
                        function () use ($payload): void {
                            echo json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                        },
                        $filename,
                        ['Content-Type' => 'application/json']
                    );
                }),
        ];
    }
}
