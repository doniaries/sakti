<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\PengaturanAplikasi;
use App\Services\ActivityLogger;

class PengaturanAplikasiObserver
{
    public function updated(PengaturanAplikasi $setting): void
    {
        $changes = array_diff(array_keys($setting->getChanges()), ['updated_at']);

        if (! empty($changes)) {
            ActivityLogger::log(
                event: 'update',
                module: 'Pengaturan',
                description: 'Memperbarui konfigurasi sistem aplikasi SAKTI',
                subject: $setting,
                properties: [
                    'parameter_diubah' => $changes,
                ]
            );
        }
    }
}

