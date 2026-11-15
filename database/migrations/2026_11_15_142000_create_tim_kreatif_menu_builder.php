<?php

use App\Filament\Pages\TimKreatif;
use App\Filament\Resources\SuratTugas\SuratTugasResource;
use App\Models\Menu;
use Datlechin\FilamentMenuBuilder\MenuBuilder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('menus') || ! Schema::hasTable('menu_items')) {
            return;
        }

        $itemModel = MenuBuilder::menuItemModel();

        Menu::withoutEvents(function () use ($itemModel): void {
            $persuratan = Menu::query()->where('name', 'Persuratan')->first();
            $timKreatifMenu = Menu::query()->where('name', 'Tim Kreatif')->first();

            if (! $timKreatifMenu && $persuratan) {
                $persuratan->update([
                    'name' => 'Tim Kreatif',
                    'icon' => 'heroicon-o-sparkles',
                    'is_visible' => true,
                ]);
                $timKreatifMenu = $persuratan;
            } elseif (! $timKreatifMenu) {
                $timKreatifMenu = Menu::query()->create([
                    'name' => 'Tim Kreatif',
                    'icon' => 'heroicon-o-sparkles',
                    'sort' => ((int) Menu::query()->max('sort')) + 1,
                    'is_visible' => true,
                ]);
            } elseif ($persuratan && $persuratan->getKey() !== $timKreatifMenu->getKey()) {
                $itemModel::query()->where('menu_id', $persuratan->getKey())->delete();
                $persuratan->delete();
            }

            $timKreatifMenu->syncLocations(['sidebar']);

            $itemModel::query()->where('menu_id', $timKreatifMenu->getKey())->delete();

            $itemModel::query()->create([
                'menu_id' => $timKreatifMenu->getKey(),
                'parent_id' => null,
                'title' => 'Tim Kreatif',
                'type' => 'resource',
                'url' => '/admin/tim-kreatif',
                'icon' => 'heroicon-o-sparkles',
                'target' => '_self',
                'order' => 1,
                'is_visible' => true,
                'data' => ['target_class' => TimKreatif::class],
            ]);

            $itemModel::query()->create([
                'menu_id' => $timKreatifMenu->getKey(),
                'parent_id' => null,
                'title' => 'Surat Tugas',
                'type' => 'resource',
                'url' => '/admin/surat-tugas',
                'icon' => 'heroicon-o-document-text',
                'target' => '_self',
                'order' => 2,
                'is_visible' => true,
                'data' => ['target_class' => SuratTugasResource::class],
            ]);
        });
    }

    public function down(): void
    {
        // Tidak menghapus menu saat rollback agar struktur menu admin tetap aman.
    }
};
