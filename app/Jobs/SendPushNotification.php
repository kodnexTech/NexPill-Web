<?php

namespace App\Jobs;

use App\Models\AppNotification;
use App\Services\FcmService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendPushNotification implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    /** @var list<int> */
    public array $backoff = [30, 120, 600];

    public function __construct(public readonly string $notificationId) {}

    public function handle(FcmService $fcm): void
    {
        $notification = AppNotification::with('user.deviceTokens')->find($this->notificationId);
        if (! $notification || $notification->sent_at) {
            return;
        }
        if ($notification->user->deviceTokens->isEmpty()) {
            $notification->update(['sent_at' => now()]);

            return;
        }
        $fcm->send($notification);
        $notification->update(['sent_at' => now()]);
    }
}
