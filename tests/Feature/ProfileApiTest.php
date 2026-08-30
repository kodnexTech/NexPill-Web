<?php

namespace Tests\Feature;

use App\Models\DeviceToken;
use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProfileApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_inactive_user_cannot_use_an_existing_api_session(): void
    {
        $user = User::factory()->create(['is_active' => false]);
        Sanctum::actingAs($user, ['mobile']);

        $this->getJson('/api/v1/profile')
            ->assertForbidden()
            ->assertJsonPath('success', false);
    }

    public function test_device_token_is_reassigned_to_the_current_account(): void
    {
        $oldUser = User::factory()->create();
        $newUser = User::factory()->create();
        DeviceToken::create([
            'user_id' => $oldUser->id,
            'device_id' => 'old-device',
            'token' => 'same-fcm-token',
            'platform' => 'android',
        ]);

        Sanctum::actingAs($newUser, ['mobile']);
        $this->postJson('/api/v1/profile/devices', [
            'device_id' => 'new-device',
            'token' => 'same-fcm-token',
            'platform' => 'android',
        ])->assertOk();

        $this->assertDatabaseMissing('device_tokens', ['user_id' => $oldUser->id, 'token' => 'same-fcm-token']);
        $this->assertDatabaseHas('device_tokens', ['user_id' => $newUser->id, 'token' => 'same-fcm-token']);
    }

    public function test_account_deletion_removes_health_data_support_data_and_files(): void
    {
        Storage::fake('local');
        $user = User::factory()->create();
        $path = 'prescriptions/'.$user->id.'/rx.pdf';
        Storage::disk('local')->put($path, 'private prescription');
        $medicine = $user->medicines()->create([
            'name' => 'Private medicine',
            'form' => 'tablet',
            'prescription_path' => $path,
        ]);
        $ticket = SupportTicket::create([
            'user_id' => $user->id,
            'email' => $user->email,
            'subject' => 'Private support request',
            'category' => 'account',
            'status' => 'open',
        ]);
        $ticket->messages()->create(['sender_id' => $user->id, 'message' => 'Private details']);

        Sanctum::actingAs($user, ['mobile']);
        $this->deleteJson('/api/v1/profile')->assertOk();

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
        $this->assertDatabaseMissing('medicines', ['id' => $medicine->id]);
        $this->assertDatabaseMissing('support_tickets', ['id' => $ticket->id]);
        $this->assertDatabaseMissing('support_messages', ['support_ticket_id' => $ticket->id]);
        Storage::disk('local')->assertMissing($path);
    }
}
