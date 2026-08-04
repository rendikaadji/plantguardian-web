<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleAccessProtectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_without_role_is_assigned_viewer_and_redirected_to_home_on_login(): void
    {
        $user = User::factory()->create([
            'email' => 'norole@plantguardian.id',
            'password' => bcrypt('password123'),
            'role' => null,
        ]);

        $response = $this->post('/login', [
            'email' => 'norole@plantguardian.id',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('home'));
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'role' => 'viewer',
        ]);
    }

    public function test_user_registration_automatically_assigns_viewer_role(): void
    {
        $response = $this->post('/register', [
            'name' => 'Pengguna Baru',
            'email' => 'penggunabaru@plantguardian.id',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('onboarding.tutorial-viewer'));

        $this->assertDatabaseHas('users', [
            'email' => 'penggunabaru@plantguardian.id',
            'role' => 'viewer',
        ]);
    }

    public function test_user_with_ranger_role_visiting_pilih_role_is_redirected_to_ranger_dashboard(): void
    {
        $ranger = User::factory()->create(['role' => 'ranger']);

        $response = $this->actingAs($ranger)->get(route('onboarding.pilih-role'));

        $response->assertRedirect(route('ranger.dashboard'));
    }

    public function test_user_with_viewer_role_visiting_pilih_role_is_redirected_to_home(): void
    {
        $viewer = User::factory()->create(['role' => 'viewer']);

        $response = $this->actingAs($viewer)->get(route('onboarding.pilih-role'));

        $response->assertRedirect(route('home'));
    }

    public function test_ranger_user_can_access_unified_web_routes(): void
    {
        $ranger = User::factory()->create(['role' => 'ranger']);

        $response = $this->actingAs($ranger)->get('/');
        $response->assertStatus(200);

        $responsePeta = $this->actingAs($ranger)->get('/peta');
        $responsePeta->assertStatus(200);

        $responseProfile = $this->actingAs($ranger)->get('/profile');
        $responseProfile->assertStatus(200);
    }

    public function test_ranger_user_can_access_unified_gallery_api(): void
    {
        $ranger = User::factory()->create(['role' => 'ranger']);

        $response = $this->actingAs($ranger, 'sanctum')->getJson('/api/gallery');
        $response->assertStatus(200)
            ->assertJsonPath('role', 'ranger');
    }

    public function test_viewer_user_cannot_access_ranger_admin_routes_and_is_redirected(): void
    {
        $viewer = User::factory()->create(['role' => 'viewer']);

        $response = $this->actingAs($viewer)->get('/ranger/dashboard');
        $response->assertRedirect(route('home'));
    }

    public function test_viewer_user_cannot_access_ranger_api_routes(): void
    {
        $viewer = User::factory()->create(['role' => 'viewer']);

        $response = $this->actingAs($viewer, 'sanctum')->getJson('/api/ranger/species');
        $response->assertStatus(403);
        $response->assertJsonFragment([
            'message' => 'Akses ditolak. Halaman atau endpoint ini khusus untuk pengguna ber-role Ranger.',
        ]);
    }

    public function test_login_redirects_authenticated_users_to_home(): void
    {
        $ranger = User::factory()->create([
            'email' => 'ranger@plantguardian.id',
            'password' => bcrypt('password123'),
            'role' => 'ranger',
        ]);

        $response = $this->post('/login', [
            'email' => 'ranger@plantguardian.id',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('home'));
    }
}
