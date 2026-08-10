<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_admin_users_cannot_access_admin_dashboard(): void
    {
        $viewer = User::factory()->create(['role' => 'viewer']);
        $ranger = User::factory()->create(['role' => 'ranger']);

        $responseViewer = $this->actingAs($viewer)->get('/admin/dashboard');
        $responseViewer->assertStatus(403);

        $responseRanger = $this->actingAs($ranger)->get('/admin/dashboard');
        $responseRanger->assertStatus(403);
    }

    public function test_admin_can_access_admin_dashboard(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get('/admin/dashboard');

        $response->assertStatus(200)
            ->assertSee('Administrator Dashboard')
            ->assertSee('Manajemen');
    }

    public function test_admin_can_update_user_role(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $targetViewer = User::factory()->create(['role' => 'viewer']);

        $response = $this->actingAs($admin)->post("/admin/users/{$targetViewer->id}/role", [
            'role' => 'ranger',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('users', [
            'id' => $targetViewer->id,
            'role' => 'ranger',
        ]);
    }
}
