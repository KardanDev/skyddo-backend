<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PolicyRenewal extends Model
{
    protected $fillable = [
        'original_policy_id',
        'renewed_policy_id',
        'created_by',
        'notes',
    ];

    public function originalPolicy(): BelongsTo
    {
        return $this->belongsTo(Policy::class, 'original_policy_id');
    }

    public function renewedPolicy(): BelongsTo
    {
        return $this->belongsTo(Policy::class, 'renewed_policy_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
