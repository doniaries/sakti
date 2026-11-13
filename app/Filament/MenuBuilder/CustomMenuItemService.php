<?php

declare(strict_types=1);

namespace App\Filament\MenuBuilder;

use Datlechin\FilamentMenuBuilder\Models\Menu;
use Datlechin\FilamentMenuBuilder\Services\MenuItemService as BaseMenuItemService;
use Illuminate\Support\Facades\DB;

class CustomMenuItemService extends BaseMenuItemService
{
    public function delete(Menu $menu, int | string $itemId): bool
    {
        return DB::transaction(function () use ($menu, $itemId): bool {
            $hierarchy = $this->hierarchy($menu);

            if (! $hierarchy->contains($itemId)) {
                return false;
            }

            $parentId = $hierarchy->parentOf($itemId);

            foreach (array_reverse($hierarchy->descendantsOf($itemId)) as $descendantId) {
                $menu->items()->reorder()->whereKey($descendantId)->delete();
            }

            $menu->items()->reorder()->whereKey($itemId)->delete();

            $this->writeOrder($menu, array_values(array_diff($hierarchy->childrenOf($parentId), [$itemId])), $parentId);

            return true;
        });
    }
}

