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
        return [
            DeleteAction::make(),
        ];
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

        // 1. Update Sekretaris Dinas
        if (array_key_exists('sekretaris_id', $data)) {
            $sekretariat = \App\Models\Bidang::where('slug', 'like', '%sekretariat%')->first();
            if ($sekretariat) {
                $sekretariat->update([
                    'kepala_id' => $data['sekretaris_id'] ?: null,
                ]);

                // Jika terpilih, pastikan bidang_id user tersebut diatur ke sekretariat
                if (!empty($data['sekretaris_id'])) {
                    $sekretarisUser = \App\Models\User::find($data['sekretaris_id']);
                    if ($sekretarisUser && is_null($sekretarisUser->bidang_id)) {
                        $sekretarisUser->update(['bidang_id' => $sekretariat->id]);
                    }
                }
            }
        }

        // 2. Update masing-masing Kepala Bidang
        $bidangs = \App\Models\Bidang::where('slug', 'not like', '%sekretariat%')->get();
        foreach ($bidangs as $bidang) {
            $fieldKey = 'kabid_' . $bidang->id;
            if (array_key_exists($fieldKey, $data)) {
                $bidang->update([
                    'kepala_id' => $data[$fieldKey] ?: null,
                ]);

                // Jika terpilih, pastikan bidang_id user tersebut diatur ke bidang ini
                if (!empty($data[$fieldKey])) {
                    $kabidUser = \App\Models\User::find($data[$fieldKey]);
                    if ($kabidUser && is_null($kabidUser->bidang_id)) {
                        $kabidUser->update(['bidang_id' => $bidang->id]);
                    }
                }
            }
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
