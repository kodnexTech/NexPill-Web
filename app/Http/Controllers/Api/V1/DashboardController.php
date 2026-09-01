<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\DoseStatus;
use App\Models\Appointment;
use App\Models\DoseLog;
use App\Models\Medicine;
use App\Services\AdherenceCalculator;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends ApiController
{
    public function __invoke(Request $request, AdherenceCalculator $adherence): JsonResponse
    {
        $data = $request->validate(['dependent_id' => ['nullable', 'uuid']]);
        $dependentId = $data['dependent_id'] ?? null;
        if ($dependentId && ! $request->user()->dependents()->whereKey($dependentId)->exists()) {
            abort(422, 'Invalid dependent.');
        }

        $timezone = $request->user()->timezone ?: 'UTC';
        $start = CarbonImmutable::now($timezone)->startOfDay()->utc();
        $end = CarbonImmutable::now($timezone)->endOfDay()->utc();
        $doseQuery = DoseLog::where('user_id', $request->user()->id)
            ->whereBetween('scheduled_for', [$start, $end])
            ->with('medicine.dependent')
            ->orderBy('scheduled_for');
        if ($request->query->has('dependent_id')) {
            $doseQuery->whereHas('medicine', fn ($query) => $dependentId
                ? $query->where('dependent_id', $dependentId)
                : $query->whereNull('dependent_id'));
        }
        $doses = $doseQuery->get();
        $lowStockQuery = Medicine::where('user_id', $request->user()->id)
            ->whereNotNull('refill_threshold')
            ->whereColumn('inventory_remaining', '<=', 'refill_threshold');
        if ($request->query->has('dependent_id')) {
            $dependentId
                ? $lowStockQuery->where('dependent_id', $dependentId)
                : $lowStockQuery->whereNull('dependent_id');
        }

        return $this->ok([
            'date' => CarbonImmutable::now($timezone)->toDateString(),
            'summary' => [
                'total' => $doses->count(), 'taken' => $doses->where('status', DoseStatus::Taken)->count(),
                'missed' => $doses->where('status', DoseStatus::Missed)->count(),
                'remaining' => $doses->whereNotIn('status', [DoseStatus::Taken, DoseStatus::Skipped, DoseStatus::Missed])->count(),
            ],
            'doses' => $doses,
            'adherence_30_days' => $adherence->forRange(
                $request->user(),
                now()->subDays(29)->startOfDay(),
                now()->endOfDay(),
                $dependentId,
                $request->query->has('dependent_id'),
            ),
            'low_stock_count' => $lowStockQuery->count(),
            'next_appointment' => Appointment::where('user_id', $request->user()->id)->where('appointment_at', '>', now())->orderBy('appointment_at')->first(),
        ]);
    }

    public function adherence(Request $request, AdherenceCalculator $calculator): JsonResponse
    {
        $data = $request->validate(['from' => ['required', 'date'], 'to' => ['required', 'date', 'after_or_equal:from']]);
        $from = CarbonImmutable::parse($data['from'])->startOfDay();
        $to = CarbonImmutable::parse($data['to'])->endOfDay();
        if ($from->diffInDays($to) > 366) {
            return $this->fail('Date range may not exceed 366 days.');
        }

        return $this->ok($calculator->forRange($request->user(), $from, $to));
    }

    public function history(Request $request, AdherenceCalculator $calculator): JsonResponse
    {
        $data = $request->validate([
            'year' => ['required', 'integer', 'between:2000,2100'],
            'month' => ['required', 'integer', 'between:1,12'],
        ]);
        $timezone = $request->user()->timezone ?: 'UTC';
        $fromLocal = CarbonImmutable::create((int) $data['year'], (int) $data['month'], 1, 0, 0, 0, $timezone)->startOfDay();
        $toLocal = $fromLocal->endOfMonth();
        $from = $fromLocal->utc();
        $to = $toLocal->utc();

        $doses = DoseLog::where('user_id', $request->user()->id)
            ->whereBetween('scheduled_for', [$from, $to])
            ->with('medicine.dependent')
            ->orderBy('scheduled_for')
            ->get();
        $grouped = $doses->groupBy(fn (DoseLog $dose) => $dose->scheduled_for->setTimezone($timezone)->toDateString());
        $days = $grouped->map(function ($items): string {
            if ($items->contains(fn (DoseLog $dose) => $dose->status === DoseStatus::Missed)) {
                return DoseStatus::Missed->value;
            }
            if ($items->contains(fn (DoseLog $dose) => $dose->status === DoseStatus::Skipped)) {
                return DoseStatus::Skipped->value;
            }

            return $items->every(fn (DoseLog $dose) => $dose->status === DoseStatus::Taken)
                ? DoseStatus::Taken->value
                : DoseStatus::Scheduled->value;
        });
        $summary = $calculator->forRange($request->user(), $from, $to);

        return $this->ok([
            'days' => (object) ($days->all()),
            'day_doses' => (object) ($grouped->map(fn ($items) => $items->values())->all()),
            'taken' => $summary['taken'],
            'total' => $summary['taken'] + $summary['missed'] + $summary['skipped'],
            'adherence_percent' => $summary['adherence_percent'],
        ]);
    }
}
