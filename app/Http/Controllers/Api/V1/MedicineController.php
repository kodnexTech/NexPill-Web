<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\DoseStatus;
use App\Models\Dependent;
use App\Models\Medicine;
use App\Models\RefillEvent;
use App\Services\ScheduleMaterializer;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class MedicineController extends ApiController
{
    public function __construct(private readonly ScheduleMaterializer $materializer) {}

    public function index(Request $request): JsonResponse
    {
        $query = Medicine::query()->where('user_id', $request->user()->id)->with(['schedules', 'dependent']);
        if ($request->filled('dependent_id')) {
            $query->where('dependent_id', $request->string('dependent_id'));
        }
        if ($request->filled('search')) {
            $query->where('name', 'like', '%'.$request->string('search').'%');
        }
        if ($request->boolean('active_only')) {
            $query->where('is_paused', false);
        }

        return $this->ok($query->latest()->paginate(min(100, max(1, $request->integer('per_page', 20)))));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->validatePayload($request);
        $this->assertDependentOwner($request, $data['dependent_id'] ?? null);

        $medicine = DB::transaction(function () use ($request, $data): Medicine {
            $scheduleData = Arr::pull($data, 'schedule');
            $data['user_id'] = $request->user()->id;
            $data['inventory_remaining'] ??= $data['inventory_total'] ?? null;
            $medicine = Medicine::create($data);
            $schedule = $medicine->schedules()->create($scheduleData);
            $this->materializer->materialize($schedule, CarbonImmutable::now(), CarbonImmutable::now()->addDays(30));

            return $medicine;
        });

        return $this->ok($medicine->load(['schedules', 'dependent']), 'Medicine created', 201);
    }

    public function show(Request $request, string $medicine): JsonResponse
    {
        return $this->ok($this->owned($request, $medicine)->load(['schedules', 'dependent', 'refillEvents' => fn ($q) => $q->latest()->limit(10)]));
    }

    public function update(Request $request, string $medicine): JsonResponse
    {
        $model = $this->owned($request, $medicine);
        $data = $this->validatePayload($request, true);
        $this->assertDependentOwner($request, $data['dependent_id'] ?? $model->dependent_id);

        DB::transaction(function () use ($model, $data): void {
            $scheduleData = Arr::pull($data, 'schedule');
            $model->update($data);
            if ($scheduleData) {
                $schedule = $model->schedules()->first();
                $schedule ? $schedule->update($scheduleData) : $schedule = $model->schedules()->create($scheduleData);
                $model->doseLogs()->where('scheduled_for', '>=', now())->whereNotIn('status', [DoseStatus::Taken, DoseStatus::Skipped, DoseStatus::Missed])->delete();
                $this->materializer->materialize($schedule->fresh(), CarbonImmutable::now(), CarbonImmutable::now()->addDays(30));
            }
        });

        return $this->ok($model->fresh()->load(['schedules', 'dependent']), 'Medicine updated');
    }

    public function destroy(Request $request, string $medicine): JsonResponse
    {
        $model = $this->owned($request, $medicine);
        if ($model->prescription_path) {
            Storage::disk('local')->delete($model->prescription_path);
        }
        $model->delete();

        return $this->ok(null, 'Medicine deleted');
    }

    public function pause(Request $request, string $medicine): JsonResponse
    {
        $data = $request->validate(['pause_until' => ['nullable', 'date', 'after_or_equal:today']]);
        $model = $this->owned($request, $medicine);

        DB::transaction(function () use ($model, $data): void {
            $model->update([
                'is_paused' => true,
                'paused_until' => $data['pause_until'] ?? null,
            ]);
            $model->doseLogs()
                ->where('scheduled_for', '>=', now())
                ->whereNotIn('status', [DoseStatus::Taken, DoseStatus::Skipped, DoseStatus::Missed])
                ->delete();
        });

        return $this->ok($model->fresh()->load(['schedules', 'dependent']), 'Medicine paused');
    }

    public function resume(Request $request, string $medicine): JsonResponse
    {
        $model = $this->owned($request, $medicine);

        DB::transaction(function () use ($model): void {
            $model->update(['is_paused' => false, 'paused_until' => null]);
            foreach ($model->schedules()->where('is_active', true)->get() as $schedule) {
                $this->materializer->materialize($schedule, CarbonImmutable::now(), CarbonImmutable::now()->addDays(30));
            }
        });

        return $this->ok($model->fresh()->load(['schedules', 'dependent']), 'Medicine resumed');
    }

    public function refill(Request $request, string $medicine): JsonResponse
    {
        $data = $request->validate([
            'quantity' => ['required', 'integer', 'min:1', 'max:100000'], 'cost' => ['nullable', 'numeric', 'min:0'],
            'pharmacy' => ['nullable', 'string', 'max:191'], 'notes' => ['nullable', 'string', 'max:2000'],
            'refilled_at' => ['nullable', 'date'],
        ]);
        $model = $this->owned($request, $medicine);
        $event = DB::transaction(function () use ($request, $model, $data): RefillEvent {
            $locked = Medicine::lockForUpdate()->findOrFail($model->id);
            $remaining = (int) ($locked->inventory_remaining ?? 0) + (int) $data['quantity'];
            $locked->update(['inventory_remaining' => $remaining, 'inventory_total' => max((int) ($locked->inventory_total ?? 0), $remaining)]);

            return RefillEvent::create([
                'medicine_id' => $locked->id, 'user_id' => $request->user()->id,
                'quantity_added' => $data['quantity'], 'remaining_after' => $remaining,
                'cost' => $data['cost'] ?? null, 'pharmacy' => $data['pharmacy'] ?? null,
                'notes' => $data['notes'] ?? null, 'refilled_at' => $data['refilled_at'] ?? now(),
            ]);
        });

        return $this->ok($event, 'Refill logged', 201);
    }

    public function uploadPrescription(Request $request, string $medicine): JsonResponse
    {
        $model = $this->owned($request, $medicine);
        $request->validate([
            'prescription' => ['required', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:10240'],
        ]);

        $oldPath = $model->prescription_path;
        $path = $request->file('prescription')->store('prescriptions/'.$request->user()->id, 'local');
        abort_unless($path, 500, 'Prescription could not be stored.');

        $model->update(['prescription_path' => $path]);
        if ($oldPath && $oldPath !== $path) {
            Storage::disk('local')->delete($oldPath);
        }

        return $this->ok(['medicine_id' => $model->id, 'uploaded' => true], 'Prescription uploaded');
    }

    public function downloadPrescription(Request $request, string $medicine): BinaryFileResponse
    {
        $model = $this->owned($request, $medicine);
        abort_unless($model->prescription_path && Storage::disk('local')->exists($model->prescription_path), 404);

        return Storage::disk('local')->download($model->prescription_path);
    }

    /** @return array<string, mixed> */
    private function validatePayload(Request $request, bool $updating = false): array
    {
        $required = $updating ? 'sometimes' : 'required';

        return $request->validate([
            'name' => [$required, 'string', 'max:191'], 'dependent_id' => ['nullable', 'uuid'],
            'strength' => ['nullable', 'numeric', 'min:0.001'], 'unit' => ['nullable', 'string', 'max:32'],
            'form' => [$required, 'in:tablet,capsule,liquid,injection,drops,inhaler,cream,powder,other'],
            'color' => ['nullable', 'string', 'max:32'], 'instructions' => ['nullable', 'string', 'max:3000'],
            'notes' => ['nullable', 'string', 'max:3000'], 'doctor_notes' => ['nullable', 'string', 'max:3000'],
            'dose_amount' => ['nullable', 'numeric', 'min:0.001'], 'dose_unit' => ['nullable', 'string', 'max:32'],
            'food_instruction' => ['nullable', 'in:before,with,after,none'],
            'inventory_total' => ['nullable', 'integer', 'min:0'], 'inventory_remaining' => ['nullable', 'integer', 'min:0'],
            'refill_threshold' => ['nullable', 'integer', 'min:0'], 'reminder_enabled' => ['sometimes', 'boolean'],
            'is_paused' => ['sometimes', 'boolean'], 'paused_until' => ['nullable', 'date'],
            'schedule' => [$required, 'array'], 'schedule.type' => [$required, 'in:daily,specific_days,interval,as_needed'],
            'schedule.timezone' => [$required, 'timezone'],
            'schedule.times' => [Rule::requiredIf(fn () => $request->has('schedule') && $request->input('schedule.type') !== 'as_needed'), 'array', 'min:1', 'max:24'],
            'schedule.times.*' => ['date_format:H:i', 'distinct'], 'schedule.weekdays' => ['required_if:schedule.type,specific_days', 'array', 'min:1'],
            'schedule.weekdays.*' => ['integer', 'between:1,7', 'distinct'], 'schedule.interval_days' => ['required_if:schedule.type,interval', 'integer', 'min:1', 'max:365'],
            'schedule.interval_hours' => ['nullable', 'integer', 'min:1', 'max:168'], 'schedule.starts_on' => [$required, 'date'],
            'schedule.ends_on' => ['nullable', 'date', 'after_or_equal:schedule.starts_on'], 'schedule.as_needed' => ['sometimes', 'boolean'],
            'schedule.is_active' => ['sometimes', 'boolean'],
        ]);
    }

    private function assertDependentOwner(Request $request, ?string $dependentId): void
    {
        if ($dependentId && ! Dependent::where('id', $dependentId)->where('owner_id', $request->user()->id)->exists()) {
            abort(422, 'Invalid dependent.');
        }
    }

    private function owned(Request $request, string $id): Medicine
    {
        return Medicine::where('user_id', $request->user()->id)->findOrFail($id);
    }
}
