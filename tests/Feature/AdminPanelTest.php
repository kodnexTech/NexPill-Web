<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\LegalDocument;
use App\Models\Plan;
use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class AdminPanelTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_and_regular_user_cannot_access_admin_pages(): void
    {
        $this->get('/admin')->assertRedirect(route('admin.login'));

        $user = User::factory()->create(['role' => UserRole::User]);
        $this->actingAs($user)->get('/admin')->assertRedirect(route('admin.login'));
    }

    public function test_active_admin_can_render_every_primary_admin_screen(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $member = User::factory()->create();

        $paths = [
            '/admin',
            '/admin/users',
            "/admin/users/{$member->id}",
            '/admin/medicines',
            '/admin/dose-logs',
            '/admin/subscriptions',
            '/admin/support',
            '/admin/plans',
            '/admin/plans/create',
            '/admin/notifications',
            '/admin/legal',
            '/admin/legal/create',
            '/admin/audit-logs',
        ];

        foreach ($paths as $path) {
            $this->actingAs($admin)->get($path)->assertOk();
        }

        $this->assertFalse(Route::has('admin.plans.show'));
    }

    public function test_admin_login_requires_an_active_admin_account(): void
    {
        $admin = User::factory()->create([
            'email' => 'active-admin@example.com',
            'role' => UserRole::Admin,
        ]);

        $this->post('/admin/login', [
            'email' => $admin->email,
            'password' => 'password',
        ])->assertRedirect(route('admin.dashboard'));

        $this->post('/admin/logout')->assertRedirect(route('admin.login'));

        $inactiveAdmin = User::factory()->create([
            'email' => 'inactive-admin@example.com',
            'role' => UserRole::Admin,
            'is_active' => false,
        ]);

        $this->post('/admin/login', [
            'email' => $inactiveAdmin->email,
            'password' => 'password',
        ])->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_admin_cannot_lock_out_or_delete_their_own_account(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $this->actingAs($admin)->patch("/admin/users/{$admin->id}", [
            'role' => 'user',
            'is_active' => false,
            'soft_delete' => true,
        ])->assertSessionHas('error');

        $admin->refresh();
        $this->assertSame(UserRole::Admin, $admin->role);
        $this->assertTrue($admin->is_active);
        $this->assertNull($admin->deleted_at);
    }

    public function test_admin_can_manage_plans_legal_documents_and_support_tickets(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $this->actingAs($admin);

        $this->post('/admin/plans', [
            'name' => 'Care Plus',
            'slug' => 'care-plus',
            'price_minor' => 49900,
            'currency' => 'INR',
            'billing_period' => 'year',
            'features' => "Unlimited medicines\nFamily support",
            'is_active' => true,
        ])->assertRedirect(route('admin.plans.index'));

        $plan = Plan::where('slug', 'care-plus')->firstOrFail();
        $this->patch("/admin/plans/{$plan->id}", [
            'name' => 'Care Plus Annual',
            'slug' => 'care-plus',
            'price_minor' => 59900,
            'currency' => 'INR',
            'billing_period' => 'year',
            'features' => 'Unlimited medicines',
            'is_active' => true,
        ])->assertRedirect(route('admin.plans.index'));
        $this->assertDatabaseHas('plans', ['id' => $plan->id, 'name' => 'Care Plus Annual']);
        $this->delete("/admin/plans/{$plan->id}")->assertRedirect(route('admin.plans.index'));
        $this->assertDatabaseMissing('plans', ['id' => $plan->id]);

        $this->post('/admin/legal', [
            'type' => 'privacy',
            'version' => 'v-test',
            'title' => 'Test Privacy Policy',
            'content' => 'A complete test policy.',
        ])->assertRedirect(route('admin.legal.index'));
        $legal = LegalDocument::where('version', 'v-test')->firstOrFail();
        $this->patch("/admin/legal/{$legal->id}/publish")->assertSessionHas('success');
        $this->assertNotNull($legal->fresh()->published_at);
        $this->delete("/admin/legal/{$legal->id}")->assertRedirect(route('admin.legal.index'));
        $this->assertDatabaseMissing('legal_documents', ['id' => $legal->id]);

        $ticket = SupportTicket::create([
            'email' => 'member@example.com',
            'subject' => 'Reminder help',
            'category' => 'technical',
            'status' => 'open',
            'priority' => 'normal',
        ]);
        $this->post("/admin/support/{$ticket->id}/reply", ['message' => 'We are checking this for you.'])
            ->assertSessionHas('success');
        $this->assertDatabaseHas('support_messages', ['support_ticket_id' => $ticket->id, 'is_staff_reply' => true]);
        $this->patch("/admin/support/{$ticket->id}/status", ['status' => 'resolved', 'priority' => 'high'])
            ->assertSessionHas('success');
        $this->assertDatabaseHas('support_tickets', ['id' => $ticket->id, 'status' => 'resolved', 'priority' => 'high']);
    }
}
