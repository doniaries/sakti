<?php

declare(strict_types=1);

namespace App\Filament\MenuBuilder;

use Datlechin\FilamentMenuBuilder\ItemTypes\ItemType;
use Filament\Facades\Filament;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Utilities\Set;

class ResourceItemType extends ItemType
{
    public static function make(): static
    {
        return new static;
    }

    public function getName(): string
    {
        return 'resource';
    }

    protected function getDefaultLabel(): string
    {
        return 'Resource / Halaman';
    }

    protected function getDefaultIcon(): string
    {
        return 'heroicon-o-squares-2x2';
    }

    public function getCreateSchema(): array
    {
        $catalog = self::getCatalog();

        $options = [];
        $descriptions = [];

        foreach ($catalog as $class => $meta) {
            $options[$class] = $meta['label'];
            $descriptions[$class] = ($meta['group'] ? "Grup: {$meta['group']} • " : '') . $meta['path'];
        }

        return [
            CheckboxList::make('resources')
                ->label('Pilih Resource / Halaman Aplikasi')
                ->options($options)
                ->descriptions($descriptions)
                ->searchable()
                ->bulkToggleable()
                ->required(),
        ];
    }

    public function getDestinationSchema(): array
    {
        $catalog = self::getCatalog();

        $options = [];
        foreach ($catalog as $class => $meta) {
            $groupSuffix = $meta['group'] ? " ({$meta['group']})" : '';
            $options[$class] = $meta['label'] . $groupSuffix;
        }

        return [
            Select::make('data.target_class')
                ->label('Resource / Halaman Tujuan')
                ->options($options)
                ->searchable()
                ->required()
                ->live()
                ->afterStateUpdated(function (?string $state, Set $set) use ($catalog): void {
                    if ($state && isset($catalog[$state])) {
                        $set('url', $catalog[$state]['path']);
                    }
                }),
            Hidden::make('url'),
        ];
    }

    public function getItemsToCreate(array $data): array
    {
        $catalog = self::getCatalog();
        $selected = $data['resources'] ?? [];
        $items = [];

        foreach ($selected as $class) {
            if (! isset($catalog[$class])) {
                continue;
            }

            $meta = $catalog[$class];

            $items[] = [
                'title' => $meta['label'],
                'url' => $meta['path'],
                'icon' => $meta['icon'],
                'data' => [
                    'target_class' => $class,
                ],
                'is_visible' => true,
            ];
        }

        return $items;
    }

    /**
     * @return array<class-string, array{label: string, group: string, path: string, icon: ?string, sort: int}>
     */
    public static function getCatalog(): array
    {
        $panel = Filament::getPanel('admin');
        $entries = [];

        foreach ($panel->getResources() as $resource) {
            if (! $resource::shouldRegisterNavigation()) {
                continue;
            }

            try {
                $fullUrl = $resource::getUrl();
                $path = parse_url($fullUrl, PHP_URL_PATH) ?: $fullUrl;
                $icon = $resource::getNavigationIcon();

                $entries[$resource] = [
                    'label' => (string) $resource::getNavigationLabel(),
                    'group' => (string) ($resource::getNavigationGroup() ?? 'Umum'),
                    'path' => $path,
                    'icon' => is_string($icon) ? $icon : null,
                    'sort' => (int) ($resource::getNavigationSort() ?? 99),
                ];
            } catch (\Throwable) {
                continue;
            }
        }

        foreach ($panel->getPages() as $page) {
            if ($page === \Filament\Pages\Dashboard::class || ! $page::shouldRegisterNavigation()) {
                continue;
            }

            try {
                $fullUrl = $page::getUrl();
                $path = parse_url($fullUrl, PHP_URL_PATH) ?: $fullUrl;
                $icon = $page::getNavigationIcon();

                $entries[$page] = [
                    'label' => (string) $page::getNavigationLabel(),
                    'group' => (string) ($page::getNavigationGroup() ?? 'Utama'),
                    'path' => $path,
                    'icon' => is_string($icon) ? $icon : null,
                    'sort' => (int) ($page::getNavigationSort() ?? 99),
                ];
            } catch (\Throwable) {
                continue;
            }
        }

        uasort($entries, function (array $a, array $b): int {
            $groupCompare = strcmp($a['group'], $b['group']);

            return $groupCompare !== 0 ? $groupCompare : ($a['sort'] <=> $b['sort']);
        });

        return $entries;
    }
}

