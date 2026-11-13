<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Filament\MenuBuilder\CustomMenuItemService;
use Closure;
use Datlechin\FilamentMenuBuilder\FilamentMenuBuilderPlugin;
use Datlechin\FilamentMenuBuilder\ItemTypes\ItemType;
use Datlechin\FilamentMenuBuilder\ItemTypes\UnregisteredItemType;
use Datlechin\FilamentMenuBuilder\Livewire\MenuItems as BaseMenuItems;
use Datlechin\FilamentMenuBuilder\MenuBuilder;
use Datlechin\FilamentMenuBuilder\Models\MenuItem;
use Datlechin\FilamentMenuBuilder\Services\MenuItemService;
use Datlechin\FilamentMenuBuilder\Support\MenuHierarchy;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\CreateAction;
use Filament\Support\Enums\Size;
use Filament\Support\Enums\Width;

class MenuItems extends BaseMenuItems
{
    public function getItemType(array $arguments): ItemType
    {
        $type = $arguments['type'] ?? '';

        if (! $type) {
            return UnregisteredItemType::make('item');
        }

        return FilamentMenuBuilderPlugin::get()->getItemType($type) ?? UnregisteredItemType::make($type);
    }

    public function createItemAction(): CreateAction
    {
        return CreateAction::make('createItem')
            ->model(MenuBuilder::menuItemModel())
            ->label(function (array $arguments): string {
                $type = $this->getItemType($arguments);

                return filled($arguments['parent'] ?? null)
                    ? __('filament-menu-builder::menu-builder.actions.add_child', ['type' => $type->getLabel()])
                    : $type->getLabel();
            })
            ->icon(fn (array $arguments): ?string => $this->getItemType($arguments)->getIcon())
            ->modalHeading(fn (array $arguments): string => __('filament-menu-builder::menu-builder.actions.create.heading', ['type' => $this->getItemType($arguments)->getLabel()]))
            ->slideOver()
            ->modalWidth(Width::Large)
            ->schema(fn (array $arguments): array => $this->getItemType($arguments)->getCreateSchema())
            ->using(function (array $data, array $arguments, CreateAction $action): MenuItem {
                $type = $this->getItemType($arguments);
                $parentId = $arguments['parent'] ?? null;
                $items = $this->getService()->create($this->menu, $type, $data, $parentId);

                if ($items === null) {
                    $this->notifyRejected(fn (MenuHierarchy $hierarchy): bool => $parentId !== null
                        && $hierarchy->getMaxDepth() !== null
                        && $hierarchy->depthOf($parentId) + 1 > $hierarchy->getMaxDepth());

                    $action->halt();
                }

                if ($items === []) {
                    $action->cancel();
                }

                $this->refreshTree();

                return end($items);
            })
            ->visible(fn (array $arguments): bool => $this->getItemType($arguments)->isVisible($this->menu)
                && (blank($arguments['parent'] ?? null) || $this->canAddChildTo($arguments['parent'])))
            ->authorize(fn (): bool => $this->canEdit());
    }

    protected function makeMoveAction(string $name, string $icon, Closure $isPossible, Closure $move): Action
    {
        return parent::makeMoveAction($name, $icon, $isPossible, $move)
            ->iconButton()
            ->color('gray')
            ->size(Size::Small)
            ->tooltip(__("filament-menu-builder::menu-builder.actions.{$name}"));
    }

    public function getItemActions(int | string $itemId): ActionGroup
    {
        $arguments = ['item' => $itemId];

        return ActionGroup::make([
            ActionGroup::make([
                ($this->deleteItemAction)($arguments),
                ($this->duplicateItemAction)($arguments),
            ])->dropdown(false),
            ActionGroup::make($this->getCreateActions($itemId))->dropdown(false),
        ])
            ->dropdownPlacement('bottom-end')
            ->size(Size::Small)
            ->color('gray');
    }

    protected function getService(): MenuItemService
    {
        return CustomMenuItemService::make();
    }
}
