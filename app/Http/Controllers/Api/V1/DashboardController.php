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
        $timezone = $request->user()->timezone ?: 'UTC';
        $start = CarbonImmutable::now($timezone)->startOfDay()->utc();
        $end = CarbonImmutable::now($timezone)->endOfDay()->utc();
        $doses = DoseLog::where('user_id', $request->user()->id)->whereBetween('scheduled_for', [$start, $end])->with('medicine')->orderBy('scheduled_for')->get();

        return $this->ok([
            'date' => CarbonImmutable::now($timezone)->toDateString(),
            'summary' => [
                'total' => $doses->count(), 'taken' => $doses->where('status', DoseStatus::Taken)->count(),
                'missed' => $doses->where('status', DoseStatus::Missed)->count(),
                'remaining' => $doses->whereNotIn('status', [DoseStatus::Taken, DoseStatus::Skipped, DoseStatus::Missed])->count(),
            ],
            'doses' => $doses,
            'adherence_30_days' => $adherence->forRange($request->user(), now()->subDays(29)->startOfDay(), now()->endOfDay()),
            'low_stock_count' => Medicine::where('user_id', $request->user()->id)->whereNotNull('refill_threshold')->whereColumn('inventory_remaining', '<=', 'refill_threshold')->count(),
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
}
