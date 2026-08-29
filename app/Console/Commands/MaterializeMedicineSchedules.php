<?php

namespace App\Console\Commands;

use App\Models\MedicineSchedule;
use App\Services\ScheduleMaterializer;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;

class MaterializeMedicineSchedules extends Command
{
    protected $signature = 'nexpill:materialize-schedules {--days=14}';

    protected $description = 'Create idempotent upcoming dose rows from active schedules';

    public function handle(ScheduleMaterializer $materializer): int
    {
        $count = 0;
        MedicineSchedule::where('is_active', true)->with('medicine.user')->cursor()->each(function ($schedule) use ($materializer, &$count): void {
            $count += $materializer->materialize($schedule, CarbonImmutable::now(), CarbonImmutable::now()->addDays((int) $this->option('days')));
        });
        $this->info("Created {$count} dose rows.");

        return self::SUCCESS;
    }
}
