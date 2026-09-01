<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\NotificationType;
use App\Jobs\SendPushNotification;
use App\Models\AppNotification;
use App\Models\FamilyConnection;
use App\Models\Medicine;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class FamilyController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $connections = FamilyConnection::query()
            ->where(fn ($query) => $query->where('owner_id', $request->user()->id)->orWhere('member_id', $request->user()->id))
            ->with(['owner:id,name,email', 'member:id,name,email', 'dependent'])
            ->latest()->get();

        return $this->ok(['connections' => $connections, 'dependents' => $request->user()->dependents()->latest()->get()]);
    }

    public function invite(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'], 'email' => ['required', 'email:rfc', 'max:191'],
            'role' => ['required', 'in:caregiver,viewer'],
        ]);
        if (Str::lower($data['email']) === Str::lower($request->user()->email)) {
            return $this->fail('You cannot invite yourself.');
        }

        do {
            $code = Str::upper(Str::random(6));
            $hash = hash('sha256', $code);
        } while (FamilyConnection::where('invitation_code_hash', $hash)->exists());

        $connection = FamilyConnection::create([
            'owner_id' => $request->user()->id, 'role' => $data['role'], 'status' => 'pending',
            'display_name' => $data['name'], 'contact_info' => Str::lower($data['email']),
            'invitation_code_hash' => $hash, 'invitation_expires_at' => now()->addDays(7),
        ]);

        return $this->ok(['connection' => $connection, 'invitation_code' => $code, 'expires_at' => $connection->invitation_expires_at], 'Invitation created', 201);
    }

    public function accept(Request $request): JsonResponse
    {
        $data = $request->validate(['code' => ['required', 'string', 'size:6']]);
        $connection = FamilyConnection::where('invitation_code_hash', hash('sha256', Str::upper($data['code'])))
            ->where('status', 'pending')->firstOrFail();
        if (! $connection->invitation_expires_at || $connection->invitation_expires_at->isPast()) {
            return $this->fail('Invitation expired.', 410);
        }
        if ($connection->contact_info && Str::lower($connection->contact_info) !== Str::lower($request->user()->email)) {
            return $this->fail('This invitation belongs to another email.', 403);
        }

        DB::transaction(function () use ($connection, $request): void {
            $connection->update(['member_id' => $request->user()->id, 'status' => 'accepted', 'accepted_at' => now(), 'invitation_code_hash' => null]);
            foreach ([$connection->owner_id, $request->user()->id] as $recipientId) {
                $notification = AppNotification::create([
                    'user_id' => $recipientId, 'type' => NotificationType::FamilyInvite,
                    'title' => 'Family circle updated', 'message' => $request->user()->name.' joined the family circle.',
                    'data' => ['family_connection_id' => $connection->id],
                ]);
                SendPushNotification::dispatch($notification->id)->afterCommit();
            }
        });

        return $this->ok($connection->fresh()->load(['owner:id,name,email', 'member:id,name,email']), 'Invitation accepted');
    }

    public function addDependent(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'], 'relationship' => ['nullable', 'string', 'max:64'],
            'date_of_birth' => ['nullable', 'date', 'before_or_equal:today'], 'notes' => ['nullable', 'string', 'max:3000'],
        ]);
        $dependent = $request->user()->dependents()->create($data);
        FamilyConnection::create(['owner_id' => $request->user()->id, 'dependent_id' => $dependent->id, 'role' => 'owner', 'status' => 'accepted', 'accepted_at' => now(), 'display_name' => $dependent->name]);

        return $this->ok($dependent, 'Dependent added', 201);
    }

    public function nudge(Request $request, string $connection): JsonResponse
    {
        $data = $request->validate(['message' => ['nullable', 'string', 'max:300']]);
        $model = FamilyConnection::where('status', 'accepted')->findOrFail($connection);
        if (! in_array($request->user()->id, [$model->owner_id, $model->member_id], true)) {
            abort(404);
        }
        $recipientId = $request->user()->id === $model->owner_id ? $model->member_id : $model->owner_id;
        if (! $recipientId) {
            return $this->fail('A managed dependent cannot receive push notifications.');
        }
        $rateLimitKey = 'family-nudge:'.$model->id.':'.$request->user()->id;
        if (! RateLimiter::attempt($rateLimitKey, 1, fn () => true, 900)) {
            return $this->fail('Please wait before sending another nudge.', 429, [
                'retry_after_seconds' => RateLimiter::availableIn($rateLimitKey),
            ]);
        }
        $notification = AppNotification::create([
            'user_id' => $recipientId, 'type' => NotificationType::Nudge, 'title' => 'A friendly NexPill nudge',
            'message' => $data['message'] ?? $request->user()->name.' reminded you to check your medicines.',
            'data' => ['family_connection_id' => $model->id, 'sender_id' => $request->user()->id],
        ]);
        SendPushNotification::dispatch($notification->id)->afterCommit();

        return $this->ok($notification, 'Nudge sent', 201);
    }

    public function medicines(Request $request, string $connection): JsonResponse
    {
        $model = FamilyConnection::where('status', 'accepted')->findOrFail($connection);
        if (! in_array($request->user()->id, [$model->owner_id, $model->member_id], true)) {
            abort(404);
        }
        $targetUser = $request->user()->id === $model->owner_id ? $model->member_id : $model->owner_id;
        $query = Medicine::with(['schedules', 'dependent']);
        $model->dependent_id ? $query->where('dependent_id', $model->dependent_id) : $query->where('user_id', $targetUser);

        return $this->ok($query->get());
    }

    public function destroy(Request $request, string $connection): JsonResponse
    {
        $model = FamilyConnection::findOrFail($connection);
        if (! in_array($request->user()->id, [$model->owner_id, $model->member_id], true)) {
            abort(404);
        }
        $model->update(['status' => 'removed']);
        $model->delete();

        return $this->ok(null, 'Family connection removed');
    }
}
