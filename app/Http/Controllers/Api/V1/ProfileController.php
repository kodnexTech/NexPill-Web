<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\DeviceToken;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ProfileController extends ApiController
{
    public function show(Request $request): JsonResponse
    {
        return $this->ok($request->user()->load(['subscriptions.plan']));
    }

    public function update(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:120'],
            'phone' => ['nullable', 'string', 'max:32', Rule::unique('users', 'phone')->ignore($request->user()->id)],
            'timezone' => ['sometimes', 'timezone'], 'locale' => ['sometimes', 'string', 'max:10'],
        ]);
        $request->user()->update($data);

        return $this->ok($request->user()->fresh(), 'Profile updated');
    }

    public function updatePreferences(Request $request): JsonResponse
    {
        $data = $request->validate([
            'dose_reminders' => ['sometimes', 'boolean'], 'smart_snooze' => ['sometimes', 'boolean'],
            'family_missed_alerts' => ['sometimes', 'boolean'], 'member_joined' => ['sometimes', 'boolean'],
            'appointment_reminders' => ['sometimes', 'boolean'], 'low_stock' => ['sometimes', 'boolean'],
        ]);
        $preferences = array_replace($request->user()->notification_preferences ?? [], $data);
        $request->user()->update(['notification_preferences' => $preferences]);

        return $this->ok($preferences, 'Notification preferences updated');
    }

    public function registerDevice(Request $request): JsonResponse
    {
        $data = $request->validate([
            'device_id' => ['required', 'string', 'max:191'], 'token' => ['required', 'string', 'max:4096'],
            'platform' => ['required', 'in:android,ios,web'], 'app_version' => ['nullable', 'string', 'max:32'],
        ]);
        $device = DeviceToken::updateOrCreate(
            ['user_id' => $request->user()->id, 'device_id' => $data['device_id']],
            [...$data, 'last_seen_at' => now()],
        );

        return $this->ok($device, 'Device registered');
    }

    public function removeDevice(Request $request, string $deviceId): JsonResponse
    {
        DeviceToken::where('user_id', $request->user()->id)->where('device_id', $deviceId)->delete();

        return $this->ok(null, 'Device removed');
    }

    public function export(Request $request): JsonResponse
    {
        $user = $request->user();

        return $this->ok([
            'generated_at' => now()->toIso8601String(), 'profile' => $user,
            'medicines' => $user->medicines()->with(['schedules', 'doseLogs', 'refillEvents'])->get(),
            'appointments' => $user->appointments()->withTrashed()->get(),
            'family_connections' => DB::table('family_connections')->where('owner_id', $user->id)->orWhere('member_id', $user->id)->get(),
            'notifications' => $user->appNotifications()->get(),
        ]);
    }

    public function destroy(Request $request): JsonResponse
    {
        DB::transaction(function () use ($request): void {
            $request->user()->tokens()->delete();
            $request->user()->deviceTokens()->delete();
            $request->user()->update(['is_active' => false, 'email' => 'deleted+'.$request->user()->id.'@nexpill.invalid', 'phone' => null]);
            $request->user()->delete();
        });

        return $this->ok(null, 'Account deletion completed');
    }
}
