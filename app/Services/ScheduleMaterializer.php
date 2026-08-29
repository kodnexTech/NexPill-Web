<?php

namespace App\Services;

use App\Enums\DoseStatus;
use App\Models\MedicineSchedule;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

class ScheduleMaterializer
{
    public function materialize(MedicineSchedule $schedule, CarbonImmutable $from, CarbonImmutable $to): int
    {
        $schedule->loadMissing('medicine.user');

        if ($schedule->is_active === false || $schedule->as_needed === true || $schedule->medicine->is_paused === true) {
            return 0;
        }

        $timezone = $schedule->timezone ?: $schedule->medicine->user?->timezone ?: 'UTC';
        $start = $from->setTimezone($timezone)->startOfDay();
        $end = $to->setTimezone($timezone)->endOfDay();
        $created = 0;

        DB::transaction(function () use ($schedule, $start, $end, $timezone, &$created): void {
            for ($date = $start; $date->lte($end); $date = $date->addDay()) {
                if (! $this->runsOn($schedule, $date)) {
                    continue;
                }

                foreach ($schedule->times ?? [] as $time) {
                    $local = CarbonImmutable::createFromFormat('Y-m-d H:i', $date->format('Y-m-d').' '.$time, $timezone);
                    if (! $local) {
                        continue;
                    }

                    $log = $schedule->doseLogs()->firstOrCreate(
                        ['medicine_id' => $schedule->medicine_id, 'scheduled_for' => $local->utc()],
                        ['user_id' => $schedule->medicine->user_id, 'status' => DoseStatus::Scheduled],
                    );
                    $created += $log->wasRecentlyCreated ? 1 : 0;
                }
            }
        });

        return $created;
    }

    private function runsOn(MedicineSchedule $schedule, CarbonImmutable $date): bool
    {
        $start = CarbonImmutable::parse($schedule->starts_on->toDateString(), $date->timezone)->startOfDay();
        $end = $schedule->ends_on
            ? CarbonImmutable::parse($schedule->ends_on->toDateString(), $date->timezone)->endOfDay()
            : null;

        if ($date->lt($start) || ($end && $date->gt($end))) {
            return false;
        }

        return match ($schedule->type) {
            'specific_days' => in_array($date->isoWeekday(), $schedule->weekdays ?? [], true),
            'interval' => $start->diffInDays($date) % max(1, (int) $schedule->interval_days) === 0,
            default => true,
        };
    }
}
