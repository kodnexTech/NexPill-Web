<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\LegalDocument;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        Plan::query()->updateOrCreate(
            ['slug' => 'free'],
            [
                'name' => 'Free', 'price_minor' => 0, 'currency' => 'INR',
                'billing_period' => 'month', 'features' => ['medicine_limit' => 5, 'family_members' => 1],
                'is_active' => true,
            ],
        );

        Plan::query()->updateOrCreate(
            ['slug' => 'pro'],
            [
                'name' => 'NexPill Pro', 'price_minor' => 19900, 'currency' => 'INR',
                'billing_period' => 'month',
                'features' => ['medicine_limit' => null, 'family_members' => 10, 'pdf_reports' => true, 'priority_support' => true],
                'is_active' => true,
            ],
        );

        foreach (['privacy' => 'Privacy Policy', 'terms' => 'Terms of Service'] as $type => $title) {
            LegalDocument::query()->updateOrCreate(
                ['type' => $type, 'version' => '1.0'],
                ['title' => $title, 'content' => "Current {$title} is published on the NexPill website.", 'published_at' => now()],
            );
        }

        if ($email = env('ADMIN_EMAIL')) {
            User::query()->updateOrCreate(
                ['email' => $email],
                [
                    'name' => env('ADMIN_NAME', 'NexPill Administrator'),
                    'password' => env('ADMIN_PASSWORD'),
                    'role' => UserRole::Admin,
                    'email_verified_at' => now(),
                    'is_active' => true,
                ],
            );
        }
    }
}
