<?php

namespace App\Console\Commands;

use App\Enums\NotificationType;
use App\Models\Medicine;
use App\Services\NotificationDispatcher;
use Illuminate\Console\Command;

class CheckLowStock extends Command
{
    protected $signature = 'nexpill:check-low-stock';

    protected $description = 'Queue deduplicated low-stock notifications';

    public function handle(NotificationDispatcher $notifications): int
    {
        $count = 0;
        Medicine::where('is_paused', false)->whereNotNull('inventory_remaining')->whereNotNull('refill_threshold')
            ->whereColumn('inventory_remaining', '<=', 'refill_threshold')->with('user')->cursor()->each(function (Medicine $medicine) use ($notifications, &$count): void {
                if (($medicine->user->notification_preferences['low_stock'] ?? true) === false) {
                    return;
                }
                $notifications->dispatch(
                    'refill:'.$medicine->id.':'.$medicine->inventory_remaining, $medicine->user_id, NotificationType::RefillLow,
                    'Low supply: '.$medicine->name, "Only {$medicine->inventory_remaining} doses remain. Time to arrange a refill.",
                    ['medicine_id' => $medicine->id, 'data' => ['route' => '/refill-tracker']],
                );
                $count++;
            });
        $this->info("Processed {$count} low-stock medicines.");

        return self::SUCCESS;
    }
}
