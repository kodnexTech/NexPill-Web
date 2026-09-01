<?php

namespace App\Console\Commands;

use App\Enums\DoseStatus;
use App\Enums\NotificationType;
use App\Models\DoseLog;
use App\Services\NotificationDispatcher;
use Illuminate\Console\Command;

class ProcessMedicationReminders extends Command
{
    protected $signature = 'nexpill:process-dose-reminders';

    protected $description = 'Advance due-dose state and queue patient reminders';

    public function handle(NotificationDispatcher $notifications): int
    {
        $processed = 0;
        DoseLog::whereIn('status', [DoseStatus::Scheduled, DoseStatus::Due, DoseStatus::Overdue, DoseStatus::Snoozed])
            ->whereHas('medicine', fn ($query) => $query->where('is_paused', false)->where('reminder_enabled', true))
            ->where(fn ($q) => $q->where('scheduled_for', '<=', now()->addMinutes(5))->orWhere('snoozed_until', '<=', now()))
            ->with(['medicine', 'user'])->cursor()->each(function (DoseLog $log) use ($notifications, &$processed): void {
                $prefs = $log->user->notification_preferences ?? [];
                $effectiveAt = $log->status === DoseStatus::Snoozed && $log->snoozed_until ? $log->snoozed_until : $log->scheduled_for;
                if ($effectiveAt->isFuture()) {
                    return;
                }
                $log->update(['status' => $effectiveAt->lt(now()->subMinutes(30)) ? DoseStatus::Overdue : DoseStatus::Due]);
                if (($prefs['dose_reminders'] ?? true) === false) {
                    return;
                }
                if ($log->snooze_count > 0 && ($prefs['smart_snooze'] ?? true) === false) {
                    return;
                }
                $notifications->dispatch(
                    'dose:'.$log->id.':'.$log->snooze_count,
                    $log->user_id, NotificationType::DoseReminder, 'Time for '.$log->medicine->name,
                    'Your scheduled dose is ready. Mark it taken, snooze, or skip.',
                    ['medicine_id' => $log->medicine_id, 'dose_log_id' => $log->id, 'data' => ['route' => '/reminder/'.$log->id]],
                );
                $processed++;
            });
        $this->info("Processed {$processed} reminders.");

        return self::SUCCESS;
    }
}
