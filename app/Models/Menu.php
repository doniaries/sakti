<?php

declare(strict_types=1);

namespace App\Models;

use Datlechin\FilamentMenuBuilder\MenuBuilder;
use Datlechin\FilamentMenuBuilder\Models\Menu as BaseMenu;

class Menu extends BaseMenu
{
    protected static function booted(): void
    {
        static::deleting(function (self $menu): void {
            $menu->items()->reorder()->whereNotNull('parent_id')->orderByDesc('id')->delete();
            $menu->items()->reorder()->delete();
        });

        static::creating(function (self $menu): void {
            if (empty($menu->sort)) {
                $menu->sort = ((int) self::query()->max('sort')) + 1;
            }
        });

        static::saved(function (self $menu): void {
            if (! $menu->wasChanged('sort') && ! $menu->wasRecentlyCreated) {
                return;
            }

            $others = self::query()
                ->whereKeyNot($menu->getKey())
                ->orderBy('sort')
                ->orderBy('id')
                ->pluck('id')
                ->all();

            array_splice($others, max(0, min((int) $menu->sort - 1, count($others))), 0, [$menu->getKey()]);

            self::withoutEvents(function () use ($others): void {
                foreach ($others as $index => $id) {
                    self::query()->whereKey($id)->update(['sort' => $index + 1]);
                }
            });
        });

        parent::booted();
    }

    protected function casts(): array
    {
        return [
            ...parent::casts(),
            'sort' => 'int',
        ];
    }

    public function syncLocations(array $locations): void
    {
        $this->locations()->whereNotIn('location', $locations)->get()->each->delete();

        foreach ($locations as $location) {
            MenuBuilder::menuLocationModel()::query()->updateOrCreate(
                ['menu_id' => $this->getKey(), 'location' => $location],
                ['menu_id' => $this->getKey(), 'location' => $location],
            );
        }
    }
}
