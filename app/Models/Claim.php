<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Claim extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'client_id',
        'policy_id',
        'claim_number',
        'description',
        'incident_date',
        'claim_amount',
        'approved_amount',
        'status',
        'notes',
        'zoho_id',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'incident_date' => 'date',
            'claim_amount' => 'decimal:2',
            'approved_amount' => 'decimal:2',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function policy(): BelongsTo
    {
        return $this->belongsTo(Policy::class);
    }

    public function documents(): MorphMany
    {
        return $this->morphMany(Document::class, 'documentable');
    }

    public function statusHistories(): MorphMany
    {
        return $this->morphMany(StatusHistory::class, 'statusable')
            ->where('field_name', 'status')
            ->orderByDesc('created_at');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public static function generateClaimNumber(): string
    {
        $prefix = 'CLM';
        $year = date('Y');
        $lastClaim = self::whereYear('created_at', $year)->latest('id')->first();
        $sequence = $lastClaim ? ((int) substr($lastClaim->claim_number, -5)) + 1 : 1;

        return $prefix.$year.str_pad($sequence, 5, '0', STR_PAD_LEFT);
    }
}
