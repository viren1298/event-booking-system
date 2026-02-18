<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /**
     * Test user registration
     */
    public function test_user_can_register(): void
    {
        $email = $this->faker->unique()->safeEmail();
        $response = $this->postJson('/api/register', [
            'name' => 'John Doe',
            'email' => $email,
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'phone' => '1234567890',
            'role' => 'customer',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'user' => ['id', 'name', 'email', 'role'],
                    'token'
                ]
            ]);

        $this->assertDatabaseHas('users', [
            'email' => $email,
            'role' => 'customer'
        ]);
    }

    /**
     * Test registration with invalid email
     */
    public function test_registration_fails_with_invalid_email(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'John Doe',
            'email' => 'invalid-email',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'phone' => '1234567890',
            'role' => 'customer',
        ]);

        $response->assertStatus(422);
    }

    /**
     * Test registration with duplicate email
     */
    public function test_registration_fails_with_duplicate_email(): void
    {
        $email = $this->faker->unique()->safeEmail();
        User::factory()->create(['email' => $email]);

        $response = $this->postJson('/api/register', [
            'name' => 'John Doe',
            'email' => $email,
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'phone' => '1234567890',
            'role' => 'customer',
        ]);

        $response->assertStatus(422);
    }

    /**
     * Test user login
     */
    public function test_user_can_login(): void
    {
        $email = $this->faker->unique()->safeEmail();
        $user = User::factory()->create([
            'email' => $email,
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => $email,
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'user' => ['id', 'name', 'email'],
                    'token'
                ]
            ]);
    }

    /**
     * Test login fails with wrong password
     */
    public function test_login_fails_with_wrong_password(): void
    {
        $email = $this->faker->unique()->safeEmail();
        User::factory()->create([
            'email' => $email,
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => $email,
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401);
    }

    /**
     * Test get authenticated user
     */
    public function test_can_get_authenticated_user(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/me');

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ]
            ]);
    }

    /**
     * Test logout
     */
    public function test_user_can_logout(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/logout');

        $response->assertStatus(200)
            ->assertJson([
                'status' => true,
                'message' => 'Logout successful'
            ]);

        // Token should be deleted
        $this->assertCount(0, $user->fresh()->tokens);
    }

    /**
     * Test protected route requires authentication
     */
    public function test_protected_route_requires_authentication(): void
    {
        $response = $this->getJson('/api/me');

        $response->assertStatus(401);
    }
}
