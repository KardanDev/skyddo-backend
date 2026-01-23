<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Quote extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'client_id',
        'client_name',
        'client_email',
        'client_phone',
        'insurer_id',
        'insurance_type_id',
        'vehicle_type_id',
        'quote_number',
        'insurance_type',
        'description',
        'asset_value',
        'calculated_cost',
        'sum_insured',
        'premium',
        'status',
        'complexity_level',
        'complexity_factors',
        'requires_agent_review',
        'agent_assigned_at',
        'valid_until',
        'zoho_quote_id',
        'comparison_data',
        'additional_details',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'asset_value' => 'decimal:2',
            'calculated_cost' => 'decimal:2',
            'sum_insured' => 'decimal:2',
            'premium' => 'decimal:2',
            'valid_until' => 'date',
            'comparison_data' => 'array',
            'additional_details' => 'array',
            'complexity_factors' => 'array',
            'requires_agent_review' => 'boolean',
            'agent_assigned_at' => 'datetime',
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

    public function insuranceType(): BelongsTo
    {
        return $this->belongsTo(InsuranceType::class);
    }

    public function vehicleType(): BelongsTo
    {
        return $this->belongsTo(VehicleType::class);
    }

    public function policy(): HasOne
    {
        return $this->hasOne(Policy::class);
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

    public static function generateQuoteNumber(): string
    {
        $prefix = 'QT';
        $year = date('Y');
        $lastQuote = self::whereYear('created_at', $year)->latest('id')->first();
        $sequence = $lastQuote ? ((int) substr($lastQuote->quote_number, -5)) + 1 : 1;

        return $prefix.$year.str_pad($sequence, 5, '0', STR_PAD_LEFT);
    }
}
