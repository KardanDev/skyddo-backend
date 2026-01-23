<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Policy extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'client_id',
        'insurer_id',
        'quote_id',
        'policy_number',
        'insurance_type',
        'description',
        'sum_insured',
        'premium',
        'start_date',
        'end_date',
        'status',
        'zoho_id',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'sum_insured' => 'decimal:2',
            'premium' => 'decimal:2',
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function insurer(): BelongsTo
    {
        return $this->belongsTo(Insurer::class);
    }

    public function quote(): BelongsTo
    {
        return $this->belongsTo(Quote::class);
    }

    public function claims(): HasMany
    {
        return $this->hasMany(Claim::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
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

    public function renewals(): HasMany
    {
        return $this->hasMany(PolicyRenewal::class, 'original_policy_id');
    }

    public function renewedFrom(): HasOne
    {
        return $this->hasOne(PolicyRenewal::class, 'renewed_policy_id');
    }

    public function originalPolicy(): ?Policy
    {
        return $this->renewedFrom?->originalPolicy;
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function isExpiringSoon(int $days = 30): bool
    {
        return $this->end_date->between(now(), now()->addDays($days));
    }

    public static function generatePolicyNumber(): string
    {
        $prefix = 'POL';
        $year = date('Y');
        $lastPolicy = self::whereYear('created_at', $year)->latest('id')->first();
        $sequence = $lastPolicy ? ((int) substr($lastPolicy->policy_number, -5)) + 1 : 1;

        return $prefix.$year.str_pad($sequence, 5, '0', STR_PAD_LEFT);
    }
}
