<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class FamilyConnection extends Model
{
    use Auditable, HasUuids, SoftDeletes;

    protected $guarded = ['id'];

    protected $hidden = ['invitation_code_hash'];

    protected function casts(): array
    {
        return ['invitation_expires_at' => 'datetime', 'accepted_at' => 'datetime'];
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(User::class, 'member_id');
    }

    public function dependent(): BelongsTo
    {
        return $this->belongsTo(Dependent::class);
    }
}
