<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\AppNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        return $this->ok(AppNotification::where('user_id', $request->user()->id)->latest()->paginate(min(100, max(1, $request->integer('per_page', 30)))));
    }

    public function unreadCount(Request $request): JsonResponse
    {
        return $this->ok(['count' => AppNotification::where('user_id', $request->user()->id)->whereNull('read_at')->count()]);
    }

    public function read(Request $request, string $notification): JsonResponse
    {
        $model = AppNotification::where('user_id', $request->user()->id)->findOrFail($notification);
        $model->update(['read_at' => $model->read_at ?? now()]);

        return $this->ok($model->fresh(), 'Notification marked as read');
    }

    public function readAll(Request $request): JsonResponse
    {
        $count = AppNotification::where('user_id', $request->user()->id)->whereNull('read_at')->update(['read_at' => now()]);

        return $this->ok(['updated' => $count], 'Notifications marked as read');
    }
}
