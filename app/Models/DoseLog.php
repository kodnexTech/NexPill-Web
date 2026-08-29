<?php

namespace App\Models;

use App\Enums\DoseStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DoseLog extends Model
{
    use HasUuids;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'scheduled_for' => 'datetime', 'taken_at' => 'datetime', 'snoozed_until' => 'datetime',
            'status' => DoseStatus::class, 'dose_taken' => 'decimal:3', 'symptoms' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function medicine(): BelongsTo
    {
        return $this->belongsTo(Medicine::class);
    }

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(MedicineSchedule::class, 'medicine_schedule_id');
    }
}
