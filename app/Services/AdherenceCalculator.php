<?php

namespace App\Services;

use App\Enums\DoseStatus;
use App\Models\DoseLog;
use App\Models\User;
use Carbon\CarbonInterface;

class AdherenceCalculator
{
    /** @return array<string, int|float> */
    public function forRange(
        User $user,
        CarbonInterface $from,
        CarbonInterface $to,
        ?string $dependentId = null,
        bool $filterByDependent = false,
    ): array {
        $query = DoseLog::query()
            ->where('user_id', $user->id)
            ->whereBetween('scheduled_for', [$from, $to]);
        if ($filterByDependent) {
            $query->whereHas('medicine', fn ($medicineQuery) => $dependentId
                ? $medicineQuery->where('dependent_id', $dependentId)
                : $medicineQuery->whereNull('dependent_id'));
        }
        $counts = $query
            ->selectRaw('status, COUNT(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        $taken = (int) ($counts[DoseStatus::Taken->value] ?? 0);
        $missed = (int) ($counts[DoseStatus::Missed->value] ?? 0);
        $skipped = (int) ($counts[DoseStatus::Skipped->value] ?? 0);
        $eligible = $taken + $missed + $skipped;

        return [
            'taken' => $taken,
            'missed' => $missed,
            'skipped' => $skipped,
            'scheduled' => array_sum($counts->all()),
            'adherence_percent' => $eligible === 0 ? 100.0 : round(($taken / $eligible) * 100, 1),
        ];
    }
}
