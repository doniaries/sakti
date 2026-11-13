<?php

declare(strict_types=1);

use App\Models\Menu;
use Datlechin\FilamentMenuBuilder\Models\MenuItem;
use Datlechin\FilamentMenuBuilder\Models\MenuLocation;

return [
    'models' => [
        'menu' => Menu::class,
        'menu_item' => MenuItem::class,
        'menu_location' => MenuLocation::class,
    ],

    'tables' => [
        'menus' => 'menus',
        'menu_items' => 'menu_items',
        'menu_locations' => 'menu_locations',
    ],

    'translations' => [
        'locales' => [],

        'attributes' => [
            'menu' => [],
            'menu_item' => ['title'],
        ],
    ],

    'tenant_model' => null,

    'cache' => [
        'enabled' => true,
        'store' => null,
        'ttl' => 60 * 60 * 24,
    ],
];
