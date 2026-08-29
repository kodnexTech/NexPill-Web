<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class FamilyApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_invitation_can_only_be_accepted_by_the_invited_email(): void
    {
        $owner = User::factory()->create(['email' => 'owner@example.com']);
        Sanctum::actingAs($owner);
        $invite = $this->postJson('/api/v1/family/invitations', ['name' => 'Ravi', 'email' => 'ravi@example.com', 'role' => 'caregiver']);
        $invite->assertCreated();
        $code = $invite->json('data.invitation_code');

        Sanctum::actingAs(User::factory()->create(['email' => 'wrong@example.com']));
        $this->postJson('/api/v1/family/invitations/accept', ['code' => $code])->assertForbidden();

        $ravi = User::factory()->create(['email' => 'ravi@example.com']);
        Sanctum::actingAs($ravi);
        $this->postJson('/api/v1/family/invitations/accept', ['code' => $code])->assertOk();
        $this->assertDatabaseHas('family_connections', ['owner_id' => $owner->id, 'member_id' => $ravi->id, 'status' => 'accepted']);
    }

    public function test_managed_dependent_is_normalized_instead_of_stored_as_medicine_text(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $response = $this->postJson('/api/v1/family/dependents', ['name' => 'Eleanor', 'relationship' => 'Mother']);
        $response->assertCreated()->assertJsonPath('data.name', 'Eleanor');
        $this->assertDatabaseHas('dependents', ['owner_id' => $user->id, 'name' => 'Eleanor']);
        $this->assertDatabaseHas('family_connections', ['owner_id' => $user->id, 'role' => 'owner', 'status' => 'accepted']);
    }
}
