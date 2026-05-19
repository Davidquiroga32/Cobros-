<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_is_accessible(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
    }

    public function test_user_can_login(): void
    {
        $user = User::factory()->create([
            'email'    => 'test@example.com',
            'password' => bcrypt('password'),
            'role'     => 'admin',
            'active'   => true,
        ]);

        $response = $this->post('/login', [
            'email'    => 'test@example.com',
            'password' => 'password',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticated();
    }

    public function test_user_cannot_login_with_invalid_credentials(): void
    {
        $user = User::factory()->create([
            'email'    => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $response = $this->post('/login', [
            'email'    => 'test@example.com',
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_admin_can_access_admin_dashboard(): void
    {
        $admin = User::factory()->create([
            'role'   => 'admin',
            'active' => true,
        ]);

        $response = $this->actingAs($admin)->get('/admin');
        $response->assertStatus(200);
    }

    public function test_cobrador_can_access_cobrador_dashboard(): void
    {
        $cobrador = User::factory()->create([
            'role'   => 'cobrador',
            'active' => true,
        ]);

        $response = $this->actingAs($cobrador)->get('/cobrador');
        $response->assertStatus(200);
    }

    public function test_cobrador_cannot_access_admin_routes(): void
    {
        $cobrador = User::factory()->create([
            'role'   => 'cobrador',
            'active' => true,
        ]);

        $response = $this->actingAs($cobrador)->get('/admin');
        $response->assertStatus(403);
    }
}
