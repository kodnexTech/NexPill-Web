<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\UserRole;
use App\Models\DeviceToken;
use App\Models\LegalConsent;
use App\Models\OneTimeCode;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class AuthController extends ApiController
{
    public function register(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email:rfc', 'max:191', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:32', 'unique:users,phone'],
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
            'timezone' => ['nullable', 'timezone'],
            'locale' => ['nullable', 'string', 'max:10'],
            'device_name' => ['required', 'string', 'max:120'],
            'legal_document_ids' => ['nullable', 'array'],
            'legal_document_ids.*' => ['uuid', 'exists:legal_documents,id'],
        ]);

        $user = DB::transaction(function () use ($data, $request): User {
            $user = User::create([
                'name' => $data['name'], 'email' => Str::lower($data['email']), 'phone' => $data['phone'] ?? null,
                'password' => $data['password'], 'role' => UserRole::User,
                'timezone' => $data['timezone'] ?? 'UTC', 'locale' => $data['locale'] ?? 'en',
                'notification_preferences' => $this->defaultPreferences(), 'is_active' => true,
            ]);

            foreach ($data['legal_document_ids'] ?? [] as $documentId) {
                LegalConsent::create([
                    'user_id' => $user->id, 'legal_document_id' => $documentId,
                    'ip_address' => $request->ip(), 'user_agent' => $request->userAgent(), 'accepted_at' => now(),
                ]);
            }

            return $user;
        });

        return $this->ok($this->tokenPayload($user, $data['device_name']), 'Account created', 201);
    }

    public function login(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email'], 'password' => ['required', 'string'],
            'device_name' => ['required', 'string', 'max:120'],
        ]);
        $user = User::where('email', Str::lower($data['email']))->first();

        if (! $user || ! $user->password || ! Hash::check($data['password'], $user->password)) {
            return $this->fail('The provided credentials are incorrect.', 422, ['email' => ['Invalid credentials.']]);
        }
        if (! $user->is_active) {
            return $this->fail('This account is inactive. Contact support.', 403);
        }

        $user->update(['last_seen_at' => now()]);

        return $this->ok($this->tokenPayload($user, $data['device_name']), 'Signed in');
    }

    public function requestOtp(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email:rfc', 'max:191'],
            'purpose' => ['required', 'in:login,register'],
        ]);
        $email = Str::lower($data['email']);
        $exists = User::where('email', $email)->exists();

        if ($data['purpose'] === 'login' && ! $exists) {
            return $this->fail('No account found with this email.', 404);
        }
        if ($data['purpose'] === 'register' && $exists) {
            return $this->fail('An account already exists with this email.', 409);
        }

        $code = (string) random_int(100000, 999999);
        OneTimeCode::where('email', $email)->where('purpose', $data['purpose'])->delete();
        OneTimeCode::create([
            'email' => $email, 'purpose' => $data['purpose'],
            'code_hash' => $this->hashCode($code),
            'expires_at' => now()->addMinutes((int) env('OTP_TTL_MINUTES', 10)),
        ]);

        Mail::raw("Your NexPill verification code is {$code}. It expires shortly.", function ($message) use ($email): void {
            $message->to($email)->subject('Your NexPill verification code');
        });

        $response = ['expires_in_seconds' => (int) env('OTP_TTL_MINUTES', 10) * 60];
        if (app()->isLocal() || app()->runningUnitTests()) {
            $response['debug_code'] = $code;
        }

        return $this->ok($response, 'Verification code sent');
    }

    public function verifyOtp(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email:rfc'], 'code' => ['required', 'digits:6'],
            'purpose' => ['required', 'in:login,register'], 'device_name' => ['required', 'string', 'max:120'],
            'name' => ['nullable', 'required_if:purpose,register', 'string', 'max:120'],
            'timezone' => ['nullable', 'timezone'],
        ]);
        $email = Str::lower($data['email']);
        $record = OneTimeCode::where('email', $email)->where('purpose', $data['purpose'])
            ->whereNull('consumed_at')->latest()->first();

        if (! $record || $record->expires_at->isPast()) {
            return $this->fail('The verification code has expired.', 422);
        }
        if ($record->attempts >= (int) env('OTP_MAX_ATTEMPTS', 5)) {
            return $this->fail('Too many attempts. Request a new code.', 429);
        }
        if (! hash_equals($record->code_hash, $this->hashCode($data['code']))) {
            $record->increment('attempts');

            return $this->fail('The verification code is incorrect.', 422);
        }

        $user = DB::transaction(function () use ($record, $email, $data): User {
            $lockedCode = OneTimeCode::whereKey($record->id)->lockForUpdate()->firstOrFail();
            if ($lockedCode->consumed_at || $lockedCode->expires_at->isPast()) {
                abort(422, 'The verification code has already been used or expired.');
            }
            $lockedCode->update(['consumed_at' => now()]);

            if ($data['purpose'] === 'login') {
                $user = User::where('email', $email)->first();
                abort_unless($user, 404, 'No account found with this email.');
            } else {
                abort_if(User::where('email', $email)->exists(), 409, 'An account already exists with this email.');
                $user = User::create([
                    'email' => $email,
                    'name' => $data['name'] ?? Str::before($email, '@'), 'role' => UserRole::User,
                    'email_verified_at' => now(), 'timezone' => $data['timezone'] ?? 'UTC',
                    'notification_preferences' => $this->defaultPreferences(), 'is_active' => true,
                ]);
            }
            $user->update(['email_verified_at' => $user->email_verified_at ?? now(), 'last_seen_at' => now()]);

            return $user;
        });

        if (! $user->is_active) {
            return $this->fail('This account is inactive. Contact support.', 403);
        }

        return $this->ok($this->tokenPayload($user, $data['device_name']), 'Verified');
    }

    public function refresh(Request $request): JsonResponse
    {
        $data = $request->validate(['device_name' => ['required', 'string', 'max:120']]);
        $request->user()->currentAccessToken()?->delete();

        return $this->ok($this->tokenPayload($request->user(), $data['device_name']), 'Token rotated');
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()?->delete();

        return $this->ok(null, 'Signed out');
    }

    public function logoutAll(Request $request): JsonResponse
    {
        $request->user()->tokens()->delete();
        DeviceToken::where('user_id', $request->user()->id)->delete();

        return $this->ok(null, 'Signed out from all devices');
    }

    /** @return array<string, mixed> */
    private function tokenPayload(User $user, string $deviceName): array
    {
        $expiresAt = now()->addMinutes((int) config('sanctum.expiration', 43200));
        $token = $user->createToken($deviceName, ['mobile'], $expiresAt)->plainTextToken;

        return ['user' => $user->fresh(), 'token' => $token, 'token_type' => 'Bearer', 'expires_at' => $expiresAt->toIso8601String()];
    }

    private function hashCode(string $code): string
    {
        return hash_hmac('sha256', $code, (string) config('app.key'));
    }

    /** @return array<string, bool> */
    private function defaultPreferences(): array
    {
        return [
            'dose_reminders' => true, 'smart_snooze' => true, 'family_missed_alerts' => true,
            'member_joined' => true, 'appointment_reminders' => true, 'low_stock' => true,
        ];
    }
}
