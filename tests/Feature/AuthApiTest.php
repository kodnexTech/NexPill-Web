<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register_and_receive_a_sanctum_token(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Anita Sharma', 'email' => 'anita@example.com',
            'password' => 'NexPill123', 'password_confirmation' => 'NexPill123',
            'timezone' => 'Asia/Kolkata', 'device_name' => 'Anita Android',
        ]);

        $response->assertCreated()->assertJsonPath('success', true)->assertJsonPath('data.user.email', 'anita@example.com')->assertJsonStructure(['data' => ['token', 'expires_at']]);
        $this->assertDatabaseHas('users', ['email' => 'anita@example.com', 'timezone' => 'Asia/Kolkata']);
    }

    public function test_otp_login_is_registration_aware_and_single_use(): void
    {
        User::factory()->create(['email' => 'member@example.com']);
        $request = $this->postJson('/api/v1/auth/otp/request', ['email' => 'member@example.com', 'purpose' => 'login']);
        $request->assertOk()->assertJsonStructure(['data' => ['debug_code']]);
        $code = $request->json('data.debug_code');

        $this->postJson('/api/v1/auth/otp/verify', [
            'email' => 'member@example.com', 'purpose' => 'login', 'code' => $code, 'device_name' => 'Test device',
        ])->assertOk()->assertJsonStructure(['data' => ['token']]);

        $this->postJson('/api/v1/auth/otp/verify', [
            'email' => 'member@example.com', 'purpose' => 'login', 'code' => $code, 'device_name' => 'Test device',
        ])->assertUnprocessable();
    }
}
