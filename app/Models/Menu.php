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
