<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('one_time_codes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('email')->index();
            $table->string('purpose', 32)->default('login');
            $table->string('code_hash', 64);
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->timestamp('expires_at')->index();
            $table->timestamp('consumed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('device_tokens', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->string('device_id', 191);
            $table->text('token');
            $table->string('platform', 16);
            $table->string('app_version', 32)->nullable();
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'device_id']);
        });

        Schema::create('dependents', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('owner_id')->constrained('users')->cascadeOnDelete();
            $table->string('name');
            $table->string('relationship', 64)->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('avatar_path')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('family_connections', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('owner_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('member_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignUuid('dependent_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('role', 24)->default('viewer');
            $table->string('status', 24)->default('pending')->index();
            $table->string('display_name')->nullable();
            $table->string('contact_info')->nullable();
            $table->string('invitation_code_hash', 64)->nullable()->unique();
            $table->timestamp('invitation_expires_at')->nullable();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['owner_id', 'member_id', 'status']);
        });

        Schema::create('medicines', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('dependent_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->decimal('strength', 10, 3)->nullable();
            $table->string('unit', 32)->nullable();
            $table->string('form', 32)->default('tablet');
            $table->string('color', 32)->nullable();
            $table->text('instructions')->nullable();
            $table->text('notes')->nullable();
            $table->string('prescription_path')->nullable();
            $table->unsignedInteger('inventory_total')->nullable();
            $table->unsignedInteger('inventory_remaining')->nullable();
            $table->unsignedInteger('refill_threshold')->nullable();
            $table->boolean('reminder_enabled')->default(true);
            $table->boolean('is_paused')->default(false)->index();
            $table->date('paused_until')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['user_id', 'is_paused']);
        });

        Schema::create('medicine_schedules', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('medicine_id')->constrained()->cascadeOnDelete();
            $table->string('type', 32)->default('daily');
            $table->string('timezone', 64)->default('UTC');
            $table->json('times')->nullable();
            $table->json('weekdays')->nullable();
            $table->unsignedSmallInteger('interval_hours')->nullable();
            $table->unsignedSmallInteger('interval_days')->nullable();
            $table->date('starts_on');
            $table->date('ends_on')->nullable();
            $table->boolean('as_needed')->default(false);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('dose_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('medicine_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('medicine_schedule_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('scheduled_for')->index();
            $table->timestamp('taken_at')->nullable();
            $table->timestamp('snoozed_until')->nullable();
            $table->string('status', 24)->default('scheduled')->index();
            $table->decimal('dose_taken', 10, 3)->nullable();
            $table->unsignedTinyInteger('snooze_count')->default(0);
            $table->json('symptoms')->nullable();
            $table->string('severity', 16)->nullable();
            $table->text('notes')->nullable();
            $table->uuid('client_request_id')->nullable()->unique();
            $table->timestamps();
            $table->unique(['medicine_id', 'scheduled_for']);
            $table->index(['user_id', 'scheduled_for', 'status']);
        });

        Schema::create('refill_events', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('medicine_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('quantity_added');
            $table->unsignedInteger('remaining_after');
            $table->decimal('cost', 10, 2)->nullable();
            $table->string('pharmacy')->nullable();
            $table->timestamp('refilled_at');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('side_effect_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('medicine_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignUuid('dose_log_id')->nullable()->constrained()->nullOnDelete();
            $table->json('symptoms');
            $table->string('severity', 16);
            $table->timestamp('experienced_at');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('appointments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->string('doctor_name');
            $table->string('specialty')->nullable();
            $table->string('location')->nullable();
            $table->timestamp('appointment_at')->index();
            $table->string('timezone', 64)->default('UTC');
            $table->boolean('fasting_required')->default(false);
            $table->boolean('reminder_enabled')->default(true);
            $table->json('reminder_offsets')->nullable();
            $table->json('reminders_sent')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['user_id', 'appointment_at']);
        });

        Schema::create('app_notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('medicine_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignUuid('dose_log_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignUuid('appointment_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type', 32)->index();
            $table->string('delivery_key')->nullable()->unique();
            $table->string('title');
            $table->text('message');
            $table->json('data')->nullable();
            $table->timestamp('read_at')->nullable()->index();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'created_at']);
        });

        Schema::create('support_tickets', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('email');
            $table->string('subject');
            $table->string('category', 32)->default('general');
            $table->string('priority', 16)->default('normal');
            $table->string('status', 24)->default('open')->index();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('support_messages', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('support_ticket_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('sender_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('message');
            $table->json('attachments')->nullable();
            $table->boolean('is_staff_reply')->default(false);
            $table->timestamps();
        });

        Schema::create('plans', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('slug')->unique();
            $table->unsignedInteger('price_minor')->default(0);
            $table->string('currency', 3)->default('INR');
            $table->string('billing_period', 16)->default('month');
            $table->json('features')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('subscriptions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('plan_id')->constrained()->restrictOnDelete();
            $table->string('provider', 32)->nullable();
            $table->string('provider_reference')->nullable()->index();
            $table->string('status', 24)->default('active')->index();
            $table->timestamp('starts_at');
            $table->timestamp('ends_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();
        });

        Schema::create('legal_documents', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type', 24);
            $table->string('version', 32);
            $table->string('title');
            $table->longText('content');
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->unique(['type', 'version']);
        });

        Schema::create('legal_consents', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('legal_document_id')->constrained()->cascadeOnDelete();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('accepted_at');
            $table->timestamps();
            $table->unique(['user_id', 'legal_document_id']);
        });

        Schema::create('audit_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action', 64)->index();
            $table->nullableUuidMorphs('subject');
            $table->json('before')->nullable();
            $table->json('after')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('legal_consents');
        Schema::dropIfExists('legal_documents');
        Schema::dropIfExists('subscriptions');
        Schema::dropIfExists('plans');
        Schema::dropIfExists('support_messages');
        Schema::dropIfExists('support_tickets');
        Schema::dropIfExists('app_notifications');
        Schema::dropIfExists('appointments');
        Schema::dropIfExists('side_effect_logs');
        Schema::dropIfExists('refill_events');
        Schema::dropIfExists('dose_logs');
        Schema::dropIfExists('medicine_schedules');
        Schema::dropIfExists('medicines');
        Schema::dropIfExists('family_connections');
        Schema::dropIfExists('dependents');
        Schema::dropIfExists('device_tokens');
        Schema::dropIfExists('one_time_codes');
    }
};
