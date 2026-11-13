<?php

namespace Tests\Feature;

use App\Models\User;
use Datlechin\FilamentMenuBuilder\Models\Menu;
use Tests\TestCase;

class MenuTest extends TestCase
{
    public function test_can_view_menu_list_and_edit_and_three_level_sidebar(): void
    {
        $user = User::where('email', 'superadmin@gmail.com')->first();
        $menu = Menu::first();

        $listResponse = $this->actingAs($user)->get('/admin/menus');
        $listResponse->assertStatus(200);
        $listResponse->assertSee('Keuangan');

        $editResponse = $this->actingAs($user)->get("/admin/menus/{$menu->id}/edit");
        $editResponse->assertStatus(200);
        $editResponse->assertSee('Master Kepegawaian');

        $dashboardResponse = $this->actingAs($user)->get('/admin');
        $dashboardResponse->assertStatus(200);
        $dashboardResponse->assertSee('Master Kepegawaian');
        $dashboardResponse->assertSee('Master Keuangan');
        $dashboardResponse->assertSee('Laporan Keuangan');

        $parent = $menu->items()->create([
            'title' => 'Temp Parent',
            'order' => 99,
        ]);
        $menu->items()->create([
            'parent_id' => $parent->id,
            'title' => 'Temp Child 1',
            'order' => 1,
        ]);
        $menu->items()->create([
            'parent_id' => $parent->id,
            'title' => 'Temp Child 2',
            'order' => 2,
        ]);

        \App\Filament\MenuBuilder\CustomMenuItemService::make()->delete($menu, $parent->id);
        $this->assertDatabaseMissing('menu_items', ['id' => $parent->id]);

        $targetUser = User::query()->bukanSuperAdmin()->first();

        $userViewResponse = $this->actingAs($user)->get("/admin/users/{$targetUser->id}");
        $userViewResponse->assertStatus(200);
        $userViewResponse->assertSee('Akun & Password');
        $userViewResponse->assertSee($targetUser->email);

        $userEditResponse = $this->actingAs($user)->get("/admin/users/{$targetUser->id}/edit");
        $userEditResponse->assertStatus(200);
        $userEditResponse->assertSee('Email Dinas / Login');
        $userEditResponse->assertSee('Password Login');
    }
}
