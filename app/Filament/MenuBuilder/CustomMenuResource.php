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
            ->pushColumns([
                \Filament\Tables\Columns\TextColumn::make('sort')
                    ->label('Urutan Tampil')
                    ->badge()
                    ->sortable(),
            ])
            ->reorderable('sort')
            ->defaultSort('sort');
    }

    public static function getPages(): array
    {
        return [
            'index' => \Datlechin\FilamentMenuBuilder\Resources\MenuResource\Pages\ListMenus::route('/'),
            'edit' => \App\Filament\MenuBuilder\Pages\EditMenu::route('/{record}/edit'),
        ];
    }

    protected static function describeTakenLocations(?Menu $record): array
    {
        return [];
    }
}

