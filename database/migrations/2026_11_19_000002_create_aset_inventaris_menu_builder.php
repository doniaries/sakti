<?php

declare(strict_types=1);

use App\Filament\Resources\BukuInventaris\BukuInventarisResource;
use App\Models\Menu;
use Datlechin\FilamentMenuBuilder\MenuBuilder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('menus') || ! Schema::hasTable('menu_items')) {
            return;
        }

        $itemModel = MenuBuilder::menuItemModel();

        Menu::withoutEvents(function () use ($itemModel): void {
            /** @var Menu $asetMenu */
            $asetMenu = Menu::query()->firstOrCreate(
                ['name' => 'Aset & Inventaris'],
                [
                    'icon' => 'heroicon-o-archive-box',
                    'sort' => 3,
                    'is_visible' => true,
                ]
            );

            $asetMenu->update([
                'icon' => 'heroicon-o-archive-box',
                'sort' => 3,
                'is_visible' => true,
            ]);

            $asetMenu->syncLocations(['sidebar']);

            // Bersihkan item lama jika ada
            $itemModel::query()->where('menu_id', $asetMenu->getKey())->delete();

            // Tambahkan Buku Inventaris
            $itemModel::query()->create([
                'menu_id' => $asetMenu->getKey(),
                'parent_id' => null,
                'title' => 'Buku Inventaris',
                'type' => 'resource',
                'url' => '/admin/buku-inventaris',
                'icon' => 'heroicon-o-clipboard-document-list',
                'target' => '_self',
                'order' => 1,
                'is_visible' => true,
                'data' => ['target_class' => BukuInventarisResource::class],
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('menus')) {
            return;
        }

        $itemModel = MenuBuilder::menuItemModel();
        $asetMenu = Menu::query()->where('name', 'Aset & Inventaris')->first();

        if ($asetMenu) {
            $itemModel::query()->where('menu_id', $asetMenu->getKey())->delete();
            $asetMenu->delete();
        }
    }
};
