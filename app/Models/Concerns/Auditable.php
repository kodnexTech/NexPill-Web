<?php

namespace App\Models\Concerns;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

trait Auditable
{
    public static function bootAuditable(): void
    {
        static::created(fn (Model $model) => self::writeAudit('created', $model, null, $model->getAttributes()));
        static::updated(fn (Model $model) => self::writeAudit('updated', $model, $model->getOriginal(), $model->getChanges()));
        static::deleted(fn (Model $model) => self::writeAudit('deleted', $model, $model->getOriginal(), null));
    }

    /** @param array<string, mixed>|null $before @param array<string, mixed>|null $after */
    private static function writeAudit(string $action, Model $subject, ?array $before, ?array $after): void
    {
        if (! app()->bound('db') || ! Schema::hasTable('audit_logs')) {
            return;
        }
        $request = app()->runningInConsole() ? null : request();
        AuditLog::create([
            'actor_id' => auth()->id(), 'action' => class_basename($subject).'.'.$action,
            'subject_type' => $subject->getMorphClass(), 'subject_id' => $subject->getKey(),
            'before' => self::sanitizeAudit($before), 'after' => self::sanitizeAudit($after),
            'ip_address' => $request?->ip(), 'user_agent' => $request?->userAgent(),
        ]);
    }

    /** @param array<string, mixed>|null $values @return array<string, mixed>|null */
    private static function sanitizeAudit(?array $values): ?array
    {
        if ($values === null) {
            return null;
        }
        foreach (['password', 'remember_token', 'token', 'invitation_code_hash'] as $sensitive) {
            unset($values[$sensitive]);
        }

        return $values;
    }
}
