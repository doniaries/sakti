<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

trait DeletesOldFiles
{
    protected static function bootDeletesOldFiles(): void
    {
        static::updating(function (Model $model): void {
            /** @var self $model */
            foreach ($model->fileAttributes() as $attribute) {
                if (! $model->isDirty($attribute)) {
                    continue;
                }

                $oldPath = $model->getOriginal($attribute);
                $newPath = $model->getAttribute($attribute);

                if (filled($oldPath) && $oldPath !== $newPath) {
                    static::deletePublicFile((string) $oldPath);
                }
            }
        });

        static::deleted(function (Model $model): void {
            /** @var self $model */
            foreach ($model->fileAttributes() as $attribute) {
                $path = $model->getAttribute($attribute);
                if (filled($path)) {
                    static::deletePublicFile((string) $path);
                }
            }
        });
    }

    /**
     * Daftar atribut kolom file yang dikelola pada disk public.
     *
     * @return array<int, string>
     */
    protected function fileAttributes(): array
    {
        return [];
    }

    protected static function deletePublicFile(string $path): void
    {
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return;
        }

        $disk = Storage::disk('public');
        if ($disk->exists($path)) {
            $disk->delete($path);
        }
    }
}

