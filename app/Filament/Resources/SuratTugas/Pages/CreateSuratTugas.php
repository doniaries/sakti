<?php

declare(strict_types=1);

namespace App\Filament\Resources\SuratTugas\Pages;

use App\Filament\Resources\SuratTugas\SuratTugasResource;
use App\Models\Instansi;
use App\Models\SuratTugas;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class CreateSuratTugas extends CreateRecord
{
    protected static string $resource = SuratTugasResource::class;

    protected function getFormActions(): array
    {
        return [
            ...parent::getFormActions(),
            $this->getPreviewAction(),
        ];
    }

    protected function getPreviewAction(): Action
    {
        return Action::make('preview')
            ->label('Pratinjau Surat')
            ->icon('heroicon-o-eye')
            ->color('info')
            ->modalHeading('Pratinjau Surat Tugas')
            ->modalWidth('5xl')
            ->modalSubmitAction(false)
            ->modalCancelActionLabel('Tutup')
            ->modalContent(function (): View {
                    $data = $this->form->getRawState();

                    $surat = new SuratTugas([
                        'nomor_surat' => $data['nomor_surat'] ?? SuratTugas::generateNomorSurat(),
                        'nama_kegiatan' => $data['perihal_surat_undangan'] ?? ($data['nama_kegiatan'] ?? 'Nama Kegiatan Belum Diisi'),
                        'dasar_surat' => SuratTugas::defaultDasarSurat(),
                        'dasar_surat_undangan' => $data['dasar_surat_undangan'] ?? null,
                        'asal_surat_undangan' => $data['asal_surat_undangan'] ?? null,
                        'tanggal_surat_undangan' => filled($data['tanggal_surat_undangan'] ?? null)
                            ? Carbon::parse($data['tanggal_surat_undangan'])
                            : null,
                        'perihal_surat_undangan' => $data['perihal_surat_undangan'] ?? null,
                        'ringkasan_isi_undangan' => $data['ringkasan_isi_undangan'] ?? null,
                        'tanggal_mulai' => filled($data['tanggal_mulai'] ?? null)
                            ? Carbon::parse($data['tanggal_mulai'])
                            : now(),
                        'tanggal_selesai' => filled($data['tanggal_selesai'] ?? null)
                            ? Carbon::parse($data['tanggal_selesai'])
                            : now(),
                        'jam_acara' => $data['jam_acara'] ?? null,
                        'lokasi' => $data['lokasi'] ?? null,
                        'penanggung_jawab_ids' => $data['penanggung_jawab_ids'] ?? [],
                        'koordinator_acara_ids' => $data['koordinator_acara_ids'] ?? [],
                        'keterangan' => $data['keterangan'] ?? null,
                        'penandatangan_id' => $data['penandatangan_id'] ?? null,
                        'status' => 'draft',
                    ]);

                    $surat->created_at = filled($data['created_at'] ?? null)
                        ? Carbon::parse($data['created_at'])
                        : now();

                    $pegawaiIds = array_filter((array) ($data['pegawais'] ?? []));
                    $surat->setRelation(
                        'pegawais',
                        $pegawaiIds !== []
                            ? User::with('jabatan')->whereIn('id', $pegawaiIds)->orderBy('name')->get()
                            : collect()
                    );

                    $surat->setRelation(
                        'penandatangan',
                        filled($data['penandatangan_id'] ?? null)
                            ? User::with('jabatan')->find($data['penandatangan_id'])
                            : null
                    );

                    $html = view('pdf.surat-tugas', [
                        'surat' => $surat,
                        'instansi' => Instansi::query()->first(),
                    ])->render();

                    return view('filament.surat-tugas.preview-modal', ['html' => $html]);
                });
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = Auth::id();
        $data['nama_kegiatan'] = $data['perihal_surat_undangan'] ?? ($data['nama_kegiatan'] ?? 'Kegiatan Tim Kreatif');

        return $data;
    }

    protected function afterCreate(): void
    {
        if ($this->record->status === 'terbit') {
            $this->record->kirimNotifikasiKeTim();
        } else {
            $this->record->kirimNotifikasiPengajuan();
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
