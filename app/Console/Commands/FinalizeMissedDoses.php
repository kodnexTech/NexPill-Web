<?php

namespace App\Console\Commands;

use App\Enums\DoseStatus;
use App\Enums\NotificationType;
use App\Models\DoseLog;
use App\Services\NotificationDispatcher;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;

class FinalizeMissedDoses extends Command
{
    protected $signature = 'nexpill:finalize-missed-doses';

    protected $description = 'Finalize open doses from a user\'s previous local day';

    public function handle(NotificationDispatcher $notifications): int
    {
        $count = 0;
        DoseLog::whereIn('status', [DoseStatus::Scheduled, DoseStatus::Due, DoseStatus::Overdue, DoseStatus::Snoozed])
            ->where('scheduled_for', '<', now())->with(['medicine', 'user'])->cursor()->each(function (DoseLog $log) use ($notifications, &$count): void {
                $timezone = $log->user->timezone ?: 'UTC';
                if (! $log->scheduled_for->setTimezone($timezone)->isBefore(CarbonImmutable::now($timezone)->startOfDay())) {
                    return;
                }
                $log->update(['status' => DoseStatus::Missed]);
                $notifications->dispatch(
                    'missed:'.$log->id, $log->user_id, NotificationType::MissedDose,
                    $log->medicine->name.' was missed', 'The scheduled dose was not logged before the day ended.',
                    ['medicine_id' => $log->medicine_id, 'dose_log_id' => $log->id, 'data' => ['route' => '/schedule']],
                );
                $count++;
            });
        $this->info("Finalized {$count} missed doses.");

        return self::SUCCESS;
    }
}
