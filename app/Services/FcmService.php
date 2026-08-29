<?php

namespace App\Services;

use App\Models\AppNotification;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FcmService
{
    public function send(AppNotification $notification): int
    {
        $credentials = $this->credentials();
        $projectId = config('services.fcm.project_id') ?: ($credentials['project_id'] ?? null);
        if (! $credentials || ! $projectId) {
            return 0;
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
                    'apns' => ['headers' => ['apns-priority' => '10'], 'payload' => ['aps' => ['sound' => 'default']]],
                ]],
            );
            if ($response->successful()) {
                $sent++;
            } else {
                Log::warning('FCM delivery failed', ['device_id' => $device->device_id, 'status' => $response->status(), 'body' => $response->json()]);
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

        return is_array($decoded) ? $decoded : null;
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
            openssl_sign($unsigned, $signature, $credentials['private_key'], OPENSSL_ALGO_SHA256);
            $jwt = $unsigned.'.'.$this->base64Url($signature);

            return Http::asForm()->timeout(15)->post($credentials['token_uri'] ?? 'https://oauth2.googleapis.com/token', [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer', 'assertion' => $jwt,
            ])->throw()->json('access_token');
        });
    }

    private function base64Url(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }
}
