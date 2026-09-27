<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_login_with_correct_credentials(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt('correct-password'),
        ]);

        $response = $this->withHeader('Referer', 'http://localhost:3100')
            ->postJson('/api/login', [
                'email' => $user->email,
                'password' => 'correct-password',
            ]);

        $response->assertOk();
        $this->assertAuthenticatedAs($user);
    }

    public function test_user_cannot_login_with_incorrect_password(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt('correct-password'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $response->assertUnprocessable();
        $this->assertGuest();
    }

    public function test_guest_cannot_access_user_endpoint(): void
    {
        $response = $this->getJson('/api/user');

        $response->assertUnauthorized();
    }

    public function test_authenticated_user_can_fetch_own_profile(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->getJson('/api/user');

        $response->assertOk()->assertJsonPath('id', $user->id);
    }
}
