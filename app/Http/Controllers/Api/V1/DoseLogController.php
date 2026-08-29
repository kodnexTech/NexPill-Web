<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\DoseStatus;
use App\Models\DoseLog;
use App\Models\Medicine;
use App\Models\SideEffectLog;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class DoseLogController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $data = $request->validate(['from' => ['nullable', 'date'], 'to' => ['nullable', 'date', 'after_or_equal:from'], 'status' => ['nullable', Rule::enum(DoseStatus::class)]]);
        $from = CarbonImmutable::parse($data['from'] ?? '30 days ago')->startOfDay();
        $to = CarbonImmutable::parse($data['to'] ?? 'today')->endOfDay();
        if ($from->diffInDays($to) > 366) {
            return $this->fail('Date range may not exceed 366 days.');
        }

        $query = DoseLog::where('user_id', $request->user()->id)->whereBetween('scheduled_for', [$from, $to])->with('medicine')->orderBy('scheduled_for');
        if (isset($data['status'])) {
            $query->where('status', $data['status']);
        }

        return $this->ok($query->paginate(min(200, max(1, $request->integer('per_page', 50)))));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'medicine_id' => ['required', 'uuid'], 'scheduled_for' => ['required', 'date'],
            'client_request_id' => ['nullable', 'uuid'],
        ]);
        $medicine = Medicine::where('user_id', $request->user()->id)->findOrFail($data['medicine_id']);
        $log = DoseLog::firstOrCreate(
            ['medicine_id' => $medicine->id, 'scheduled_for' => CarbonImmutable::parse($data['scheduled_for'])->utc()],
            ['user_id' => $request->user()->id, 'status' => DoseStatus::Scheduled, 'client_request_id' => $data['client_request_id'] ?? null],
        );

        return $this->ok($log->load('medicine'), $log->wasRecentlyCreated ? 'Dose created' : 'Dose already exists', $log->wasRecentlyCreated ? 201 : 200);
    }

    public function action(Request $request, string $doseLog): JsonResponse
    {
        $data = $request->validate([
            'action' => ['required', 'in:taken,skipped,snoozed'], 'client_request_id' => ['required', 'uuid'],
            'taken_at' => ['nullable', 'date'], 'snooze_minutes' => ['required_if:action,snoozed', 'integer', 'between:5,240'],
            'dose_taken' => ['nullable', 'numeric', 'min:0.001'], 'symptoms' => ['nullable', 'array'],
            'symptoms.*' => ['string', 'max:120'], 'severity' => ['nullable', 'in:mild,moderate,severe'],
            'notes' => ['nullable', 'string', 'max:3000'],
        ]);

        $existing = DoseLog::where('user_id', $request->user()->id)->where('client_request_id', $data['client_request_id'])->first();
        if ($existing) {
            return $this->ok($existing->load('medicine'), 'Action already applied');
        }

        $log = DB::transaction(function () use ($request, $doseLog, $data): DoseLog {
            $log = DoseLog::where('user_id', $request->user()->id)->lockForUpdate()->findOrFail($doseLog);
            if ($log->status->isFinal()) {
                abort(409, 'This dose is already finalized.');
            }
            $previousStatus = $log->status;

            $attributes = [
                'status' => DoseStatus::from($data['action']), 'client_request_id' => $data['client_request_id'],
                'notes' => $data['notes'] ?? $log->notes, 'symptoms' => $data['symptoms'] ?? $log->symptoms,
                'severity' => $data['severity'] ?? $log->severity, 'dose_taken' => $data['dose_taken'] ?? $log->dose_taken,
            ];
            if ($data['action'] === 'taken') {
                $attributes['taken_at'] = $data['taken_at'] ?? now();
            }
            if ($data['action'] === 'snoozed') {
                $attributes['snoozed_until'] = now()->addMinutes((int) $data['snooze_minutes']);
                $attributes['snooze_count'] = $log->snooze_count + 1;
            }
            $log->update($attributes);

            if ($data['action'] === 'taken' && $previousStatus !== DoseStatus::Taken) {
                $medicine = Medicine::lockForUpdate()->findOrFail($log->medicine_id);
                if ($medicine->inventory_remaining !== null) {
                    $medicine->update(['inventory_remaining' => max(0, $medicine->inventory_remaining - 1)]);
                }
            }
            if (! empty($data['symptoms'])) {
                SideEffectLog::create([
                    'user_id' => $request->user()->id, 'medicine_id' => $log->medicine_id, 'dose_log_id' => $log->id,
                    'symptoms' => $data['symptoms'], 'severity' => $data['severity'] ?? 'mild',
                    'experienced_at' => $data['taken_at'] ?? now(), 'notes' => $data['notes'] ?? null,
                ]);
            }

            return $log;
        });

        return $this->ok($log->fresh()->load('medicine'), 'Dose updated');
    }
}
