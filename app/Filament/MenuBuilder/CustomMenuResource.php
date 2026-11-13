<?php

declare(strict_types=1);

namespace App\Filament\MenuBuilder;

use Datlechin\FilamentMenuBuilder\Models\Menu;
use Datlechin\FilamentMenuBuilder\Resources\MenuResource as BaseMenuResource;
use Filament\Tables\Table;

class CustomMenuResource extends BaseMenuResource
{
    protected static ?string $slug = 'menus';

    public static function table(Table $table): Table
    {
        return parent::table($table)
            ->reorderable('sort')
            ->defaultSort('sort');
    }

    protected static function describeTakenLocations(?Menu $record): array
    {
        return [];
    }
}

