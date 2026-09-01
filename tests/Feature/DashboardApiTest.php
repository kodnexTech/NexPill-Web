<?php

namespace Tests\Feature;

use App\Enums\DoseStatus;
use App\Models\DoseLog;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class DashboardApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_history_returns_real_monthly_doses_and_summary(): void
    {
        $user = User::factory()->create(['timezone' => 'Asia/Kolkata']);
        $medicine = $user->medicines()->create(['name' => 'Metformin', 'form' => 'tablet']);
        $localDay = CarbonImmutable::now('Asia/Kolkata')->startOfMonth()->addDay()->setTime(8, 0);
        DoseLog::create([
            'user_id' => $user->id,
            'medicine_id' => $medicine->id,
            'scheduled_for' => $localDay->utc(),
            'taken_at' => $localDay->addMinutes(4)->utc(),
            'status' => DoseStatus::Taken,
        ]);
        DoseLog::create([
            'user_id' => $user->id,
            'medicine_id' => $medicine->id,
            'scheduled_for' => $localDay->addDay()->utc(),
            'status' => DoseStatus::Missed,
        ]);

        Sanctum::actingAs($user, ['mobile']);
        $this->getJson('/api/v1/history?year='.$localDay->year.'&month='.$localDay->month)
            ->assertOk()
            ->assertJsonPath('data.days.'.$localDay->toDateString(), DoseStatus::Taken->value)
            ->assertJsonPath('data.days.'.$localDay->addDay()->toDateString(), DoseStatus::Missed->value)
            ->assertJsonPath('data.taken', 1)
            ->assertJsonPath('data.total', 2)
            ->assertJsonPath('data.adherence_percent', 50);
    }

    public function test_dashboard_can_filter_the_account_owner_and_dependents(): void
    {
        $user = User::factory()->create(['timezone' => 'UTC']);
        $dependent = $user->dependents()->create(['name' => 'Mom', 'relationship' => 'Mother']);
        $ownMedicine = $user->medicines()->create(['name' => 'Own medicine', 'form' => 'tablet']);
        $dependentMedicine = $user->medicines()->create([
            'dependent_id' => $dependent->id,
            'name' => 'Dependent medicine',
            'form' => 'tablet',
        ]);
        foreach ([$ownMedicine, $dependentMedicine] as $index => $medicine) {
            DoseLog::create([
                'user_id' => $user->id,
                'medicine_id' => $medicine->id,
                'scheduled_for' => now()->startOfDay()->addHours(8 + $index),
                'status' => DoseStatus::Scheduled,
            ]);
        }

        Sanctum::actingAs($user, ['mobile']);
        $this->getJson('/api/v1/dashboard?dependent_id=')
            ->assertOk()
            ->assertJsonCount(1, 'data.doses')
            ->assertJsonPath('data.doses.0.medicine.name', 'Own medicine');
        $this->getJson('/api/v1/dashboard?dependent_id='.$dependent->id)
            ->assertOk()
            ->assertJsonCount(1, 'data.doses')
            ->assertJsonPath('data.doses.0.medicine.name', 'Dependent medicine');
    }
}
