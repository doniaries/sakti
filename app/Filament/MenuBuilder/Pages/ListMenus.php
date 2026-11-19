<?php

declare(strict_types=1);

namespace App\Filament\MenuBuilder\Pages;

use App\Filament\MenuBuilder\ResourceItemType;
use App\Models\Menu;
use Datlechin\FilamentMenuBuilder\FilamentMenuBuilderPlugin;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Model;

class ListMenus extends ListRecords
{
    public static function getResource(): string
    {
        return FilamentMenuBuilderPlugin::get()->getResource();
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->createAnother(false)
                ->using(function (array $data, string $model, CreateAction $action): Model {
                    $rawState = $action->getFormData();
                    $locations = $rawState['locations'] ?? $data['locations'] ?? ['sidebar'];
                    $initialResources = $rawState['initial_resources'] ?? $data['initial_resources'] ?? [];
                    $targetResource = $rawState['target_resource'] ?? $data['target_resource'] ?? null;

                    $isDirectResource = filled($targetResource);

                    if ($targetResource) {
                        if (empty($initialResources)) {
                            $initialResources = [$targetResource];
                        } elseif (! in_array($targetResource, $initialResources, true)) {
                            array_unshift($initialResources, $targetResource);
                        }
                    }

                    unset($data['locations'], $data['initial_resources'], $data['target_resource']);

                    /** @var Menu $record */
                    $record = $model::query()->create($data);

                    if (! empty($locations)) {
                        $record->syncLocations($locations);
                    }

                    if (! empty($initialResources)) {
                        $catalog = ResourceItemType::getCatalog();
                        $order = 1;
                        foreach ($initialResources as $class) {
                            $meta = $catalog[$class] ?? null;
                            if (! $meta) {
                                continue;
                            }

                            $record->items()->create([
                                'type' => 'resource',
                                'title' => $meta['label'],
                                'url' => $meta['path'],
                                'icon' => $meta['icon'],
                                'data' => [
                                    'target_class' => $class,
                                    'is_direct_resource' => $isDirectResource && $class === $targetResource,
                                ],
                                'is_visible' => true,
                                'order' => $order++,
                            ]);
                        }
                    }

                    return $record;
                })
                ->successRedirectUrl(fn (Model $record): string => static::getResource()::getUrl('edit', ['record' => $record])),
        ];
    }
}
