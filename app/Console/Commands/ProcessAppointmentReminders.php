<?php

namespace App\Console\Commands;

use App\Enums\NotificationType;
use App\Models\Appointment;
use App\Services\NotificationDispatcher;
use Illuminate\Console\Command;

class ProcessAppointmentReminders extends Command
{
    protected $signature = 'nexpill:process-appointment-reminders';

    protected $description = 'Queue configured appointment reminders exactly once';

    public function handle(NotificationDispatcher $notifications): int
    {
        $count = 0;
        Appointment::where('reminder_enabled', true)->where('appointment_at', '>', now())->with('user')->cursor()->each(function (Appointment $appointment) use ($notifications, &$count): void {
            if (($appointment->user->notification_preferences['appointment_reminders'] ?? true) === false) {
                return;
            }
            $sent = $appointment->reminders_sent ?? [];
            foreach ($appointment->reminder_offsets ?? [24, 2] as $hours) {
                if (in_array((int) $hours, $sent, true) || now()->lt($appointment->appointment_at->copy()->subHours((int) $hours))) {
                    continue;
                }
                $notifications->dispatch(
                    'appointment:'.$appointment->id.':'.$hours, $appointment->user_id, NotificationType::AppointmentReminder,
                    'Upcoming appointment', "Your appointment with {$appointment->doctor_name} is in about {$hours} hours.",
                    ['appointment_id' => $appointment->id, 'data' => ['route' => '/appointments/'.$appointment->id]],
                );
                $sent[] = (int) $hours;
                $count++;
            }
            $appointment->update(['reminders_sent' => array_values(array_unique($sent))]);
        });
        $this->info("Processed {$count} appointment reminders.");

        return self::SUCCESS;
    }
}
