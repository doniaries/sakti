<?php

namespace App\Filament\Resources\Instansis\Pages;

use App\Filament\Resources\Instansis\InstansiResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditInstansi extends EditRecord
{
    protected static string $resource = InstansiResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // 1. Ambil Kepala Bidang Sekretariat (Sekretaris Dinas)
        $sekretariat = \App\Models\Bidang::where('slug', 'like', '%sekretariat%')->first();
        if ($sekretariat) {
            $data['sekretaris_id'] = $sekretariat->kepala_id;
        }

        // 2. Ambil seluruh Kepala Bidang non-sekretariat
        $bidangs = \App\Models\Bidang::where('slug', 'not like', '%sekretariat%')->get();
        foreach ($bidangs as $bidang) {
            $data['kabid_' . $bidang->id] = $bidang->kepala_id;
        }

        return $data;
    }

    protected function afterSave(): void
    {
        $data = $this->data;
        $kadisId = $this->record->kepala_id;

        // 1. Update Kepala Dinas (Kadis)
        if ($kadisId) {
            $kadisUser = \App\Models\User::query()->find($kadisId);
            if ($kadisUser) {
                // Kadis adalah pimpinan tertinggi (atasan_id null)
                $kadisUser->update([
                    'atasan_id' => null,
                ]);
            }
        }

        // 2. Update Sekretaris Dinas
        if (array_key_exists('sekretaris_id', $data)) {
            $sekretariat = \App\Models\Bidang::where('slug', 'like', '%sekretariat%')->first();
            if ($sekretariat) {
                $newSekretarisId = $data['sekretaris_id'] ?: null;

                // Update pimpinan di tabel bidangs
                $sekretariat->update(['kepala_id' => $newSekretarisId]);

                // Jika pejabat sekretaris berubah
                if ($newSekretarisId) {
                    $sekretarisUser = \App\Models\User::query()->find($newSekretarisId);
                    if ($sekretarisUser) {
                        $sekretarisUser->update([
                            'bidang_id' => $sekretariat->id,
                            'atasan_id' => $kadisId ?: null, // Atasan Sekretaris adalah Kepala Dinas
                        ]);
                    }

                    // Sinkronkan seluruh staf di sekretariat agar atasan_id nya mengarah ke Sekretaris baru
                    \App\Models\User::query()
                        ->where('bidang_id', '=', $sekretariat->id)
                        ->where('id', '!=', $newSekretarisId)
                        ->update(['atasan_id' => $newSekretarisId]);
                }
            }
        }

        // 3. Update masing-masing Kepala Bidang (Kabid)
        $bidangs = \App\Models\Bidang::where('slug', 'not like', '%sekretariat%')->get();
        foreach ($bidangs as $bidang) {
            $fieldKey = 'kabid_' . $bidang->id;
            if (array_key_exists($fieldKey, $data)) {
                $newKabidId = $data[$fieldKey] ?: null;

                // Update pimpinan di tabel bidangs
                $bidang->update(['kepala_id' => $newKabidId]);

                if ($newKabidId) {
                    $kabidUser = \App\Models\User::query()->find($newKabidId);
                    if ($kabidUser) {
                        $kabidUser->update([
                            'bidang_id' => $bidang->id,
                            'atasan_id' => $kadisId ?: null, // Atasan Kabid adalah Kepala Dinas
                        ]);
                    }

                    // Sinkronkan seluruh pegawai di bidang ini agar atasan_id nya otomatis mengarah ke Kabid baru
                    \App\Models\User::query()
                        ->where('bidang_id', '=', $bidang->id)
                        ->where('id', '!=', $newKabidId)
                        ->update(['atasan_id' => $newKabidId]);
                }
            }
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
