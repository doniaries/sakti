<?php

declare(strict_types=1);

namespace App\Filament\MenuBuilder;

use Datlechin\FilamentMenuBuilder\FilamentMenuBuilderPlugin;
use Datlechin\FilamentMenuBuilder\Models\Menu;
use Datlechin\FilamentMenuBuilder\Resources\MenuResource as BaseMenuResource;
use Datlechin\FilamentMenuBuilder\Support\TranslatableField;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Select;
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
            $status = isset($assigned[$class]) ? " [Di: {$assigned[$class]['location']}]" : '';
            $resourceOptions[$class] = "{$meta['label']}{$status} • {$meta['path']}";
            $resourceDescriptions[$class] = isset($assigned[$class])
                ? "Sudah di menu: {$assigned[$class]['location']} • {$meta['path']}"
                : "Belum ada di menu • {$meta['path']}";
        }

        return $schema
            ->columns(1)
            ->components([
                Select::make('target_resource')
                    ->label('Pilih Resource / Halaman (Jadikan Menu Utama Langsung)')
                    ->helperText('Pilih jika menu ini ditujukan langsung untuk suatu resource/halaman (Menu Utama tanpa folder dropdown). Nama & Ikon akan otomatis terisi.')
                    ->options($resourceOptions)
                    ->searchable()
                    ->placeholder('-- Pilih Resource / Halaman Utama --')
                    ->live()
                    ->afterStateUpdated(function (?string $state, Set $set, Get $get) use ($catalog): void {
                        if ($state && isset($catalog[$state])) {
                            $set('name', $catalog[$state]['label']);
                            if (filled($catalog[$state]['icon'])) {
                                $set('icon', $catalog[$state]['icon']);
                            }
                        }
                    })
                    ->visible(fn (?Menu $record): bool => $record === null)
                    ->dehydrated(false),
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
                    ->label('Atau Pilih Sub-Menu (Untuk Menu Berbentuk Folder / Grup)')
                    ->helperText('Centang jika menu ini berupa Folder/Grup yang memiliki beberapa sub-menu dropdown (seperti Kepegawaian atau Keuangan).')
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
            ->modifyQueryUsing(fn ($query) => $query->where('name', 'not like', '%Menu Builder%'))
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
            'index' => \App\Filament\MenuBuilder\Pages\ListMenus::route('/'),
            'edit' => \App\Filament\MenuBuilder\Pages\EditMenu::route('/{record}/edit'),
        ];
    }

    protected static function describeTakenLocations(?Menu $record): array
    {
        return [];
    }
}

