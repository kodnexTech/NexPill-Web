<?php

namespace Tests\Feature;

use App\Enums\DoseStatus;
use App\Models\DoseLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class MedicationApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_medicine_schedule_is_materialized_and_dose_action_is_idempotent(): void
    {
        $user = User::factory()->create(['timezone' => 'Asia/Kolkata']);
        Sanctum::actingAs($user, ['mobile']);

        $medicineResponse = $this->postJson('/api/v1/medicines', [
            'name' => 'Metformin', 'strength' => 500, 'unit' => 'mg', 'form' => 'tablet',
            'dose_amount' => 2, 'dose_unit' => 'tablets', 'food_instruction' => 'after',
            'doctor_notes' => 'Use only as directed.',
            'inventory_total' => 30, 'refill_threshold' => 7,
            'schedule' => ['type' => 'daily', 'timezone' => 'Asia/Kolkata', 'times' => ['08:00', '20:00'], 'starts_on' => now()->toDateString()],
        ]);
        $medicineResponse->assertCreated()->assertJsonPath('data.name', 'Metformin');
        $medicineId = $medicineResponse->json('data.id');
        $this->assertGreaterThan(0, DoseLog::where('medicine_id', $medicineId)->count());

        $log = DoseLog::where('medicine_id', $medicineId)->firstOrFail();
        $requestId = (string) Str::uuid();
        $this->postJson("/api/v1/doses/{$log->id}/actions", ['action' => 'taken', 'client_request_id' => $requestId])
            ->assertOk()->assertJsonPath('data.status', DoseStatus::Taken->value);
        $this->postJson("/api/v1/doses/{$log->id}/actions", ['action' => 'taken', 'client_request_id' => $requestId])->assertOk();

        $this->assertDatabaseHas('medicines', [
            'id' => $medicineId,
            'dose_amount' => 2,
            'dose_unit' => 'tablets',
            'food_instruction' => 'after',
            'inventory_remaining' => 28,
        ]);

        $this->postJson("/api/v1/doses/{$log->id}/actions", [
            'action' => 'undo_taken',
            'client_request_id' => (string) Str::uuid(),
        ])->assertOk();
        $this->assertDatabaseHas('medicines', ['id' => $medicineId, 'inventory_remaining' => 30]);
        $this->assertNull($log->fresh()->taken_at);

        $this->postJson("/api/v1/medicines/{$medicineId}/pause")
            ->assertOk()
            ->assertJsonPath('data.is_paused', true);
        $this->postJson("/api/v1/medicines/{$medicineId}/resume")
            ->assertOk()
            ->assertJsonPath('data.is_paused', false);
    }

    public function test_user_cannot_read_another_users_medicine(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $medicine = $owner->medicines()->create(['name' => 'Private medicine', 'form' => 'tablet']);
        Sanctum::actingAs($other, ['mobile']);
        $this->getJson("/api/v1/medicines/{$medicine->id}")->assertNotFound();
    }

    public function test_prescriptions_are_private_and_owner_scoped(): void
    {
        Storage::fake('local');
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $medicine = $owner->medicines()->create(['name' => 'Prescription medicine', 'form' => 'tablet']);

        Sanctum::actingAs($owner, ['mobile']);
        $this->post("/api/v1/medicines/{$medicine->id}/prescription", [
            'prescription' => UploadedFile::fake()->create('rx.pdf', 120, 'application/pdf'),
        ])->assertOk()->assertJsonPath('data.uploaded', true);

        $path = $medicine->fresh()->prescription_path;
        Storage::disk('local')->assertExists($path);

        Sanctum::actingAs($other, ['mobile']);
        $this->get("/api/v1/medicines/{$medicine->id}/prescription")->assertNotFound();
    }

    public function test_side_effect_cannot_reference_another_users_dose(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $medicine = $owner->medicines()->create(['name' => 'Private medicine', 'form' => 'tablet']);
        $dose = DoseLog::create([
            'user_id' => $owner->id,
            'medicine_id' => $medicine->id,
            'scheduled_for' => now(),
            'status' => DoseStatus::Scheduled,
        ]);

        Sanctum::actingAs($other, ['mobile']);
        $this->postJson('/api/v1/side-effects', [
            'dose_log_id' => $dose->id,
            'symptoms' => ['Nausea'],
            'severity' => 'mild',
            'experienced_at' => now()->toIso8601String(),
        ])->assertUnprocessable();
    }
}
