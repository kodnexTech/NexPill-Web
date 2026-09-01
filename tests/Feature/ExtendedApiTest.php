<?php

namespace Tests\Feature;

use App\Enums\DoseStatus;
use App\Models\Appointment;
use App\Models\AppNotification;
use App\Models\DoseLog;
use App\Models\Medicine;
use App\Models\Plan;
use App\Models\SideEffectLog;
use App\Models\SupportTicket;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ExtendedApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_appointments_crud_lifecycle(): void
    {
        $user = User::factory()->create(['timezone' => 'Asia/Kolkata']);
        Sanctum::actingAs($user, ['mobile']);

        // Create appointment
        $createResponse = $this->postJson('/api/v1/appointments', [
            'doctor_name' => 'Dr. Sharma',
            'specialty' => 'Cardiology',
            'location' => 'Apollo Hospital',
            'appointment_at' => now()->addDays(2)->toIso8601String(),
            'timezone' => 'Asia/Kolkata',
            'reminder_enabled' => true,
            'reminder_offsets' => [24, 2],
            'notes' => 'Bring previous blood reports',
        ]);
        $createResponse->assertCreated()->assertJsonPath('data.doctor_name', 'Dr. Sharma');
        $appointmentId = $createResponse->json('data.id');

        // List appointments
        $this->getJson('/api/v1/appointments')->assertOk()->assertJsonCount(1, 'data.data');
        $this->getJson('/api/v1/appointments?scope=upcoming')->assertOk()->assertJsonCount(1, 'data.data');
        $this->getJson('/api/v1/appointments?scope=past')->assertOk()->assertJsonCount(0, 'data.data');

        // Show single appointment
        $this->getJson("/api/v1/appointments/{$appointmentId}")
            ->assertOk()
            ->assertJsonPath('data.doctor_name', 'Dr. Sharma');

        // Update appointment
        $this->putJson("/api/v1/appointments/{$appointmentId}", [
            'doctor_name' => 'Dr. A. Sharma',
            'location' => 'Apollo Hospital, Clinic 4',
        ])->assertOk()->assertJsonPath('data.doctor_name', 'Dr. A. Sharma');

        // Delete appointment
        $this->deleteJson("/api/v1/appointments/{$appointmentId}")->assertOk();
        $this->assertSoftDeleted('appointments', ['id' => $appointmentId]);
    }

    public function test_notifications_inbox_and_read_actions(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user, ['mobile']);

        $notif1 = AppNotification::create([
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'user_id' => $user->id,
            'idempotency_key' => 'test-key-1',
            'type' => 'system',
            'title' => 'Welcome to NexPill',
            'message' => 'Your account is ready.',
        ]);

        $notif2 = AppNotification::create([
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'user_id' => $user->id,
            'idempotency_key' => 'test-key-2',
            'type' => 'system',
            'title' => 'Refill Reminder',
            'message' => 'Time to order refills.',
        ]);

        // Unread count
        $this->getJson('/api/v1/notifications/unread-count')
            ->assertOk()
            ->assertJsonPath('data.count', 2);

        // Mark one as read
        $this->putJson("/api/v1/notifications/{$notif1->id}/read")
            ->assertOk()
            ->assertJsonPath('data.read_at', fn ($readAt) => ! empty($readAt));

        $this->getJson('/api/v1/notifications/unread-count')
            ->assertOk()
            ->assertJsonPath('data.count', 1);

        // Mark all read
        $this->putJson('/api/v1/notifications/read-all')
            ->assertOk()
            ->assertJsonPath('data.updated', 1);

        $this->getJson('/api/v1/notifications/unread-count')
            ->assertOk()
            ->assertJsonPath('data.count', 0);
    }

    public function test_side_effects_logging_and_deletion(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user, ['mobile']);

        $medicine = $user->medicines()->create(['name' => 'Amoxicillin', 'form' => 'capsule']);

        $createResponse = $this->postJson('/api/v1/side-effects', [
            'medicine_id' => $medicine->id,
            'symptoms' => ['Mild rash', 'Nausea'],
            'severity' => 'mild',
            'experienced_at' => now()->toIso8601String(),
            'notes' => 'Felt dizzy 30 mins after taking the dose.',
        ]);
        $createResponse->assertCreated()->assertJsonPath('data.severity', 'mild');
        $sideEffectId = $createResponse->json('data.id');

        $this->getJson('/api/v1/side-effects')->assertOk()->assertJsonCount(1, 'data.data');

        $this->deleteJson("/api/v1/side-effects/{$sideEffectId}")->assertOk();
        $this->assertDatabaseMissing('side_effect_logs', ['id' => $sideEffectId]);
    }

    public function test_support_tickets_and_replies(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user, ['mobile']);

        $createResponse = $this->postJson('/api/v1/support/tickets', [
            'subject' => 'Question about dose reminder alerts',
            'category' => 'technical',
            'message' => 'How can I change the snooze interval?',
        ]);
        $createResponse->assertCreated()->assertJsonPath('data.subject', 'Question about dose reminder alerts');
        $ticketId = $createResponse->json('data.id');

        // List user tickets
        $this->getJson('/api/v1/support/tickets')->assertOk()->assertJsonCount(1, 'data.data');

        // Reply to ticket
        $this->postJson("/api/v1/support/tickets/{$ticketId}/replies", [
            'message' => 'Also wondering if alarms can vibrate.',
        ])->assertCreated();

        // Cannot reply to closed ticket
        SupportTicket::whereKey($ticketId)->update(['status' => 'closed']);
        $this->postJson("/api/v1/support/tickets/{$ticketId}/replies", [
            'message' => 'Trying to message closed ticket.',
        ])->assertStatus(409);
    }

    public function test_plans_and_subscription_endpoints(): void
    {
        Plan::create([
            'slug' => 'free',
            'name' => 'Free',
            'price_minor' => 0,
            'currency' => 'INR',
            'billing_period' => 'month',
            'features' => ['medicine_limit' => 5],
            'is_active' => true,
        ]);

        $user = User::factory()->create();
        Sanctum::actingAs($user, ['mobile']);

        $this->getJson('/api/v1/plans')->assertOk()->assertJsonCount(1, 'data');
        $this->getJson('/api/v1/subscription')->assertOk()->assertJsonPath('data', null);
    }

    public function test_scheduled_commands_execute_cleanly(): void
    {
        $user = User::factory()->create(['timezone' => 'Asia/Kolkata']);
        $medicine = $user->medicines()->create([
            'name' => 'Atorvastatin',
            'form' => 'tablet',
            'inventory_total' => 2,
            'inventory_remaining' => 2,
            'refill_threshold' => 5,
            'reminder_enabled' => true,
            'is_paused' => false,
        ]);
        $schedule = $medicine->schedules()->create([
            'type' => 'daily',
            'timezone' => 'Asia/Kolkata',
            'times' => ['09:00'],
            'starts_on' => now()->toDateString(),
            'is_active' => true,
        ]);

        // Run schedule materialization
        $this->artisan('nexpill:materialize-schedules --days=7')->assertSuccessful();

        // Run low stock check
        $this->artisan('nexpill:check-low-stock')->assertSuccessful();

        // Run medication reminders
        $this->artisan('nexpill:process-dose-reminders')->assertSuccessful();

        // Run missed dose finalizer
        $this->artisan('nexpill:finalize-missed-doses')->assertSuccessful();

        // Run appointment reminders
        $appointment = Appointment::create([
            'user_id' => $user->id,
            'doctor_name' => 'Dr. Mehta',
            'appointment_at' => now()->addHours(1),
            'timezone' => 'Asia/Kolkata',
            'reminder_enabled' => true,
            'reminder_offsets' => [2],
            'reminders_sent' => [],
        ]);
        $this->artisan('nexpill:process-appointment-reminders')->assertSuccessful();
    }
}
