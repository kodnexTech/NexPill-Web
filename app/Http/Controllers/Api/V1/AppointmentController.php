<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Appointment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AppointmentController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $query = Appointment::where('user_id', $request->user()->id)->orderBy('appointment_at');
        if ($request->string('scope')->toString() === 'upcoming') {
            $query->where('appointment_at', '>=', now());
        }
        if ($request->string('scope')->toString() === 'past') {
            $query->where('appointment_at', '<', now());
        }

        return $this->ok($query->paginate(min(100, max(1, $request->integer('per_page', 20)))));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->validated($request);
        $data['user_id'] = $request->user()->id;
        $data['reminder_offsets'] ??= [24, 2];
        $data['reminders_sent'] = [];

        return $this->ok(Appointment::create($data), 'Appointment created', 201);
    }

    public function show(Request $request, string $appointment): JsonResponse
    {
        return $this->ok($this->owned($request, $appointment));
    }

    public function update(Request $request, string $appointment): JsonResponse
    {
        $model = $this->owned($request, $appointment);
        $data = $this->validated($request, true);
        if (isset($data['appointment_at']) && $data['appointment_at'] !== $model->appointment_at?->toIso8601String()) {
            $data['reminders_sent'] = [];
        }
        $model->update($data);

        return $this->ok($model->fresh(), 'Appointment updated');
    }

    public function destroy(Request $request, string $appointment): JsonResponse
    {
        $this->owned($request, $appointment)->delete();

        return $this->ok(null, 'Appointment deleted');
    }

    /** @return array<string, mixed> */
    private function validated(Request $request, bool $updating = false): array
    {
        $required = $updating ? 'sometimes' : 'required';

        return $request->validate([
            'doctor_name' => [$required, 'string', 'max:191'], 'specialty' => ['nullable', 'string', 'max:120'],
            'location' => ['nullable', 'string', 'max:300'], 'appointment_at' => [$required, 'date'],
            'timezone' => [$required, 'timezone'], 'fasting_required' => ['sometimes', 'boolean'],
            'reminder_enabled' => ['sometimes', 'boolean'], 'reminder_offsets' => ['nullable', 'array', 'max:10'],
            'reminder_offsets.*' => ['integer', 'between:1,720'], 'notes' => ['nullable', 'string', 'max:3000'],
        ]);
    }

    private function owned(Request $request, string $id): Appointment
    {
        return Appointment::where('user_id', $request->user()->id)->findOrFail($id);
    }
}
