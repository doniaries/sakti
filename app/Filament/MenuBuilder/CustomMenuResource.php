<?php

declare(strict_types=1);

namespace App\Filament\MenuBuilder;

use Datlechin\FilamentMenuBuilder\FilamentMenuBuilderPlugin;
use Datlechin\FilamentMenuBuilder\Models\Menu;
use Datlechin\FilamentMenuBuilder\Resources\MenuResource as BaseMenuResource;
use Datlechin\FilamentMenuBuilder\Support\TranslatableField;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class CustomMenuResource extends BaseMenuResource
{
    protected static ?string $slug = 'menus';

    public static function canAccess(): bool
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        return $user !== null && $user->hasAnyRole(['super_admin', 'admin_kepegawaian']);
    }

    public static function form(Schema $schema): Schema
    {
        $locations = FilamentMenuBuilderPlugin::get()->getLocations();
        $catalog = ResourceItemType::getCatalog();
        $assigned = ResourceItemType::getAssignedMap();

        $resourceOptions = [];
        $resourceDescriptions = [];

        foreach ($catalog as $class => $meta) {
            $resourceOptions[$class] = $meta['label'];
            $resourceDescriptions[$class] = isset($assigned[$class])
                ? "Sudah di menu: {$assigned[$class]['location']} • {$meta['path']}"
                : "Belum ada di menu • {$meta['path']}";
        }

        return $schema
            ->columns(1)
            ->components([
                TranslatableField::make('menu', 'name', fn (string $statePath): TextInput => TextInput::make($statePath)
                    ->label(__('filament-menu-builder::menu-builder.resource.name'))
                    ->required()
                    ->maxLength(255)),
                CheckboxList::make('locations')
                    ->label(__('filament-menu-builder::menu-builder.resource.locations'))
                    ->options($locations)
                    ->descriptions(fn (?Menu $record): array => static::describeTakenLocations($record))
                    ->visible($locations !== [])
                    ->afterStateHydrated(fn (CheckboxList $component, ?Menu $record) => $component->state($record ? $record->locations->pluck('location')->all() : ['sidebar']))
                    ->saveRelationshipsUsing(fn (Menu $record, array $state) => $record->syncLocations($state))
                    ->dehydrated(false),
                Toggle::make('is_visible')
                    ->label(__('filament-menu-builder::menu-builder.resource.is_visible'))
                    ->default(true),
                ...FilamentMenuBuilderPlugin::get()->getMenuFields(),
                CheckboxList::make('initial_resources')
                    ->label('Ambil Langsung Resource / Halaman')
                    ->helperText('Centang resource atau halaman yang ingin langsung dimasukkan ke dalam menu baru ini.')
                    ->options($resourceOptions)
                    ->descriptions($resourceDescriptions)
                    ->searchable()
                    ->bulkToggleable()
                    ->live()
                    ->afterStateUpdated(function (?array $state, Get $get, Set $set) use ($catalog): void {
                        if (empty($state)) {
                            return;
                        }

                        $first = $state[0] ?? null;
                        if (! $first || ! isset($catalog[$first])) {
                            return;
                        }

                        if (blank($get('name'))) {
                            $set('name', $catalog[$first]['label']);
                        }

                        if (filled($catalog[$first]['icon']) && ($get('icon') === 'heroicon-o-folder' || blank($get('icon')))) {
                            $set('icon', $catalog[$first]['icon']);
                        }
                    })
                    ->visible(fn (?Menu $record): bool => $record === null)
                    ->saveRelationshipsUsing(function (Menu $record, ?array $state) use ($catalog): void {
                        if (empty($state)) {
                            return;
                        }

                        $order = 1;
                        foreach ($state as $class) {
                            $meta = $catalog[$class] ?? null;
                            if (! $meta) {
                                continue;
                            }

                            $record->items()->create([
                                'type' => 'resource',
                                'title' => $meta['label'],
                                'url' => $meta['path'],
                                'icon' => $meta['icon'],
                                'data' => ['target_class' => $class],
                                'is_visible' => true,
                                'order' => $order++,
                            ]);
                        }
                    })
                    ->dehydrated(false),
            ]);
    }

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

