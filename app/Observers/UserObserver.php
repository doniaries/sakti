<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\User;
use App\Services\ActivityLogger;

class UserObserver
{
    public function created(User $user): void
    {
        ActivityLogger::log(
            event: 'create',
            module: 'Kepegawaian',
            description: "Menambahkan pegawai baru: {$user->name} (NIP: " . ($user->nip ?: '-') . ')',
            subject: $user,
            properties: [
                'nip' => $user->nip,
                'email' => $user->email,
                'jabatan_id' => $user->jabatan_id,
                'bidang_id' => $user->bidang_id,
            ]
        );
    }

    public function updated(User $user): void
    {
        if ($user->wasChanged('password')) {
            ActivityLogger::log(
                event: 'update',
                module: 'Kepegawaian',
                description: "Mengubah kata sandi akun pegawai: {$user->name}",
                subject: $user
            );
        } else {
            $changes = array_diff(array_keys($user->getChanges()), ['updated_at', 'remember_token']);
            if (! empty($changes)) {
                ActivityLogger::log(
                    event: 'update',
                    module: 'Kepegawaian',
                    description: "Memperbarui profil / data kepegawaian: {$user->name}",
                    subject: $user,
                    properties: [
                        'kolom_diubah' => $changes,
                    ]
                );
            }
        }
    }

    public function deleted(User $user): void
    {
        ActivityLogger::log(
            event: 'delete',
            module: 'Kepegawaian',
            description: "Menghapus akun pegawai: {$user->name} (NIP: " . ($user->nip ?: '-') . ')',
            subject: $user
        );
    }
}

