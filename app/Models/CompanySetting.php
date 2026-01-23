<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanySetting extends Model
{
    protected $fillable = [
        'company_name',
        'company_email',
        'company_phone',
        'company_address',
        'website',
        'tax_id',
        'logo_path',
        'description',
    ];

    /**
     * Get the singleton company settings instance
     */
    public static function current(): self
    {
        return self::firstOrCreate(
            ['id' => 1],
            [
                'company_name' => config('app.name', 'Skyddo'),
                'company_email' => 'info@skyddo.com',
            ]
        );
    }

    /**
     * Get the full URL for the company logo
     */
    public function getLogoUrlAttribute(): ?string
    {
        return $this->logo_path ? asset('storage/'.$this->logo_path) : null;
    }
}
