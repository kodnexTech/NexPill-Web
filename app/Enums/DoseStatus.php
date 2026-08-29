<?php

namespace App\Enums;

enum DoseStatus: string
{
    case Scheduled = 'scheduled';
    case Due = 'due';
    case Overdue = 'overdue';
    case Snoozed = 'snoozed';
    case Taken = 'taken';
    case Skipped = 'skipped';
    case Missed = 'missed';

    public function isFinal(): bool
    {
        return in_array($this, [self::Taken, self::Skipped, self::Missed], true);
    }
}
