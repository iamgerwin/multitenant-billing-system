<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    protected Organization $organization;

    protected function setUp(): void
    {
        parent::setUp();

        $this->organization = Organization::factory()->create();
    }

    public function test_user_can_login_with_valid_credentials(): void
    {
        $user = User::factory()->create([
            'organization_id' => $this->organization->id,
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'user' => ['id', 'name', 'email', 'role'],
                'token',
                'token_type',
            ]);
    }

    public function test_user_cannot_login_with_invalid_credentials(): void
    {
        User::factory()->create([
            'organization_id' => $this->organization->id,
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'test@example.com',
            'password' => 'wrong-password',
        ]);

        $response->assertUnauthorized();
    }

    public function test_user_cannot_login_with_invalid_email(): void
    {
        $response = $this->postJson('/api/auth/login', [
            'email' => 'nonexistent@example.com',
            'password' => 'password',
        ]);

        $response->assertUnauthorized();
    }

    public function test_inactive_user_cannot_login(): void
    {
        User::factory()->create([
            'organization_id' => $this->organization->id,
            'email' => 'inactive@example.com',
            'password' => bcrypt('password'),
            'is_active' => false,
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'inactive@example.com',
            'password' => 'password',
        ]);

        $response->assertUnauthorized();
    }

    public function test_authenticated_user_can_get_profile(): void
    {
        $user = User::factory()->create([
            'organization_id' => $this->organization->id,
        ]);

        $response = $this->actingAs($user)
            ->getJson('/api/auth/user');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => ['id', 'name', 'email', 'role'],
            ]);
    }

    public function test_authenticated_user_can_logout(): void
    {
        $user = User::factory()->create([
            'organization_id' => $this->organization->id,
        ]);

        $response = $this->actingAs($user)
            ->postJson('/api/auth/logout');

        $response->assertOk();
    }

    public function test_unauthenticated_user_cannot_access_protected_routes(): void
    {
        $response = $this->getJson('/api/auth/user');

        $response->assertUnauthorized();
    }
}
