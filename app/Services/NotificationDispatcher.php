<?php

namespace App\Services;

use App\Enums\NotificationType;
use App\Jobs\SendPushNotification;
use App\Models\AppNotification;

class NotificationDispatcher
{
    /** @param array<string, mixed> $attributes */
    public function dispatch(string $deliveryKey, string $userId, NotificationType $type, string $title, string $message, array $attributes = []): AppNotification
    {
        $notification = AppNotification::firstOrCreate(
            ['delivery_key' => $deliveryKey],
            [
                'user_id' => $userId, 'type' => $type, 'title' => $title, 'message' => $message,
                'medicine_id' => $attributes['medicine_id'] ?? null, 'dose_log_id' => $attributes['dose_log_id'] ?? null,
                'appointment_id' => $attributes['appointment_id'] ?? null, 'data' => $attributes['data'] ?? [],
            ],
        );
        if ($notification->wasRecentlyCreated) {
            SendPushNotification::dispatch($notification->id)->afterCommit();
        }

        return $notification;
    }
}
