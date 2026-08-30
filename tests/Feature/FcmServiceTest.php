<?php

namespace Tests\Feature;

use App\Enums\NotificationType;
use App\Models\AppNotification;
use App\Models\DeviceToken;
use App\Models\User;
use App\Services\FcmService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Tests\TestCase;

class FcmServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();
        config([
            'services.fcm.project_id' => 'nexpill-app',
            'services.fcm.service_account_json' => json_encode($this->credentials()),
            'services.fcm.service_account_path' => null,
        ]);
    }

    public function test_it_sends_an_http_v1_message_to_a_registered_device(): void
    {
        [$notification] = $this->notificationWithDevice();
        Http::fake([
            'https://oauth2.googleapis.com/token' => Http::response(['access_token' => 'test-access-token']),
            'https://fcm.googleapis.com/*' => Http::response(['name' => 'projects/nexpill-app/messages/1']),
        ]);

        $this->assertSame(1, app(FcmService::class)->send($notification));

        Http::assertSent(fn ($request) => $request->url() === 'https://fcm.googleapis.com/v1/projects/nexpill-app/messages:send'
            && $request->hasHeader('Authorization', 'Bearer test-access-token')
            && data_get($request->data(), 'message.notification.title') === 'Time for your medicine'
            && data_get($request->data(), 'message.data.route') === '/medicines'
            && data_get($request->data(), 'message.apns.headers.apns-push-type') === 'alert'
        );
    }

    public function test_it_removes_an_unregistered_device_token(): void
    {
        [$notification, $device] = $this->notificationWithDevice();
        Http::fake([
            'https://oauth2.googleapis.com/token' => Http::response(['access_token' => 'test-access-token']),
            'https://fcm.googleapis.com/*' => Http::response([
                'error' => [
                    'message' => 'Requested entity was not found.',
                    'details' => [['errorCode' => 'UNREGISTERED']],
                ],
            ], 404),
        ]);

        $this->assertSame(0, app(FcmService::class)->send($notification));
        $this->assertDatabaseMissing('device_tokens', ['id' => $device->id]);
    }

    public function test_it_throws_on_a_transient_firebase_failure_so_the_queue_can_retry(): void
    {
        [$notification] = $this->notificationWithDevice();
        Http::fake([
            'https://oauth2.googleapis.com/token' => Http::response(['access_token' => 'test-access-token']),
            'https://fcm.googleapis.com/*' => Http::response(['error' => ['message' => 'Unavailable']], 503),
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('FCM delivery failed with HTTP 503.');

        app(FcmService::class)->send($notification);
    }

    /** @return array{AppNotification, DeviceToken} */
    private function notificationWithDevice(): array
    {
        $user = User::factory()->create();
        $device = DeviceToken::create([
            'user_id' => $user->id,
            'device_id' => 'device-1',
            'token' => 'fcm-token-1',
            'platform' => 'android',
        ]);
        $notification = AppNotification::create([
            'user_id' => $user->id,
            'type' => NotificationType::DoseReminder,
            'title' => 'Time for your medicine',
            'message' => 'Your scheduled dose is ready.',
            'data' => ['route' => '/medicines'],
        ])->load('user.deviceTokens');

        return [$notification, $device];
    }

    /** @return array<string, string> */
    private function credentials(): array
    {
        $options = ['private_key_bits' => 2048];
        $herdOpenSslConfig = dirname(PHP_BINARY, 3).DIRECTORY_SEPARATOR.'openssl.cnf';
        if (is_file($herdOpenSslConfig)) {
            $options['config'] = $herdOpenSslConfig;
        }
        $key = openssl_pkey_new($options);
        openssl_pkey_export($key, $privateKey, null, $options);

        return [
            'client_email' => 'firebase-adminsdk@nexpill-app.iam.gserviceaccount.com',
            'private_key' => $privateKey,
            'token_uri' => 'https://oauth2.googleapis.com/token',
        ];
    }
}
