<?php

namespace App\Enums;

enum NotificationType: string
{
    case DoseReminder = 'dose_reminder';
    case MissedDose = 'missed_dose';
    case FamilyMissed = 'family_missed';
    case Nudge = 'nudge';
    case FamilyInvite = 'family_invite';
    case AppointmentReminder = 'appointment_reminder';
    case RefillLow = 'refill_low';
    case System = 'system';
}
