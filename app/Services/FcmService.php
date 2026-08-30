<?php

namespace App\Services;

use App\Models\AppNotification;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class FcmService
{
    public function send(AppNotification $notification): int
    {
        $credentials = $this->credentials();
        $projectId = config('services.fcm.project_id') ?: ($credentials['project_id'] ?? null);
        if (! $credentials || ! $projectId) {
            throw new RuntimeException('FCM credentials are not configured.');
        }

        $accessToken = $this->accessToken($credentials);
        $sent = 0;
        foreach ($notification->user->deviceTokens as $device) {
            $response = Http::withToken($accessToken)->timeout(15)->post(
                "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send",
                ['message' => [
                    'token' => $device->token,
                    'notification' => ['title' => $notification->title, 'body' => $notification->message],
                    'data' => collect($notification->data ?? [])->map(fn ($value) => is_scalar($value) ? (string) $value : json_encode($value))->all(),
                    'android' => ['priority' => 'high'],
                    'apns' => [
                        'headers' => ['apns-priority' => '10', 'apns-push-type' => 'alert'],
                        'payload' => ['aps' => ['sound' => 'default']],
                    ],
                ]],
            );
            if ($response->successful()) {
                $sent++;
            } else {
                $errorCode = $response->json('error.details.0.errorCode');
                if ($errorCode === 'UNREGISTERED') {
                    $device->delete();
                }
                Log::warning('FCM delivery failed', [
                    'device_id' => $device->device_id,
                    'status' => $response->status(),
                    'error_code' => $errorCode,
                    'message' => $response->json('error.message'),
                ]);
                if ($response->status() === 429 || $response->serverError() || in_array($response->status(), [401, 403], true)) {
                    throw new RuntimeException("FCM delivery failed with HTTP {$response->status()}.");
                }
            }
        }

        return $sent;
    }

    /** @return array<string, mixed>|null */
    private function credentials(): ?array
    {
        $json = config('services.fcm.service_account_json');
        $path = config('services.fcm.service_account_path');
        if (! $json && $path && is_readable($path)) {
            $json = file_get_contents($path);
        }
        if (! $json) {
            return null;
        }
        $decoded = json_decode($json, true);

        if (! is_array($decoded)) {
            return null;
        }

        foreach (['client_email', 'private_key'] as $requiredKey) {
            if (! isset($decoded[$requiredKey]) || ! is_string($decoded[$requiredKey]) || $decoded[$requiredKey] === '') {
                throw new RuntimeException("FCM service account is missing {$requiredKey}.");
            }
        }

        return $decoded;
    }

    /** @param array<string, mixed> $credentials */
    private function accessToken(array $credentials): string
    {
        return Cache::remember('fcm.oauth.access_token', now()->addMinutes(50), function () use ($credentials): string {
            $now = time();
            $header = $this->base64Url(json_encode(['alg' => 'RS256', 'typ' => 'JWT']));
            $claims = $this->base64Url(json_encode([
                'iss' => $credentials['client_email'], 'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
                'aud' => $credentials['token_uri'] ?? 'https://oauth2.googleapis.com/token', 'iat' => $now, 'exp' => $now + 3600,
            ]));
            $unsigned = $header.'.'.$claims;
            if (! openssl_sign($unsigned, $signature, $credentials['private_key'], OPENSSL_ALGO_SHA256)) {
                throw new RuntimeException('Unable to sign the FCM OAuth assertion.');
            }
            $jwt = $unsigned.'.'.$this->base64Url($signature);

            $accessToken = Http::asForm()->timeout(15)->post($credentials['token_uri'] ?? 'https://oauth2.googleapis.com/token', [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer', 'assertion' => $jwt,
            ])->throw()->json('access_token');

            if (! is_string($accessToken) || $accessToken === '') {
                throw new RuntimeException('Firebase did not return an OAuth access token.');
            }

            return $accessToken;
        });
    }

    private function base64Url(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }
}
