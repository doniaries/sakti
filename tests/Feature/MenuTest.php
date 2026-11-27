<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class MenuTest extends TestCase
{
    public function test_can_view_admin_dashboard_and_user_pages(): void
    {
        $user = User::where('email', 'superadmin@gmail.com')->first();

        $dashboardResponse = $this->actingAs($user)->get('/admin');
        $dashboardResponse->assertStatus(200);

        $targetUser = User::query()->bukanSuperAdmin()->first();

        $userViewResponse = $this->actingAs($user)->get("/admin/users/{$targetUser->id}");
        $userViewResponse->assertStatus(200);
        $userViewResponse->assertSee('Akun & Password');
        $userViewResponse->assertSee($targetUser->email);

        $userEditResponse = $this->actingAs($user)->get("/admin/users/{$targetUser->id}/edit");
        $userEditResponse->assertStatus(200);
        $userEditResponse->assertSee('Email Login');
        $userEditResponse->assertSee('Password Login');
    }
}
