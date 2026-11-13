<?php

declare(strict_types=1);

namespace App\Filament\MenuBuilder\Pages;

use Datlechin\FilamentMenuBuilder\Resources\MenuResource\Pages\EditMenu as BaseEditMenu;

class EditMenu extends BaseEditMenu
{
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}

