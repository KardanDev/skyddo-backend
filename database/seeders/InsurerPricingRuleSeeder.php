<?php

namespace Database\Seeders;

use App\Models\Insurer;
use App\Models\InsuranceType;
use App\Models\PricingRule;
use App\Models\VehicleType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class InsurerPricingRuleSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get insurance types
        $responsabilidadeCivil = InsuranceType::where('slug', 'responsabilidade-civil')->first();
        $autoCompreensivo = InsuranceType::where('slug', 'auto-compreensivo')->first();
        $propriedade = InsuranceType::where('slug', 'propriedade')->first();
        $saude = InsuranceType::where('slug', 'saude')->first();
        $vida = InsuranceType::where('slug', 'vida')->first();
        $viagem = InsuranceType::where('slug', 'viagem')->first();

        // Get insurers
        $abcInsurance = Insurer::where('name', 'ABC Insurance Company')->first();
        $globalShield = Insurer::where('name', 'Global Shield Insurance')->first();
        $safeguard = Insurer::where('name', 'SafeGuard Insurance Group')->first();
        $reliableAuto = Insurer::where('name', 'Reliable Auto Insurance')->first();

        // Get vehicle types (if they exist)
        $vehicleLigeiro = VehicleType::where('name', 'Ligeiro')->first();

        // Insurer-specific pricing rules
        $pricingRules = [
            // ABC Insurance - slightly higher rates but premium service
            [
                'insurance_type_id' => $responsabilidadeCivil->id,
                'vehicle_type_id' => $vehicleLigeiro?->id,
                'insurer_id' => $abcInsurance->id,
                'calculation_type' => 'percentage',
                'rate' => 0.0280, // 2.8% (higher than base)
                'price_multiplier' => 1.10, // 10% markup
                'minimum_amount' => 5000,
                'maximum_amount' => 50000,
                'is_active' => true,
                'priority' => 1,
            ],
            [
                'insurance_type_id' => $autoCompreensivo->id,
                'vehicle_type_id' => $vehicleLigeiro?->id,
                'insurer_id' => $abcInsurance->id,
                'calculation_type' => 'percentage',
                'rate' => 0.0450, // 4.5%
                'price_multiplier' => 1.08,
                'minimum_amount' => 12000,
                'maximum_amount' => 150000,
                'is_active' => true,
                'priority' => 1,
            ],

            // Global Shield - competitive pricing
            [
                'insurance_type_id' => $responsabilidadeCivil->id,
                'vehicle_type_id' => $vehicleLigeiro?->id,
                'insurer_id' => $globalShield->id,
                'calculation_type' => 'percentage',
                'rate' => 0.0240, // 2.4% (competitive)
                'price_multiplier' => 1.00, // No markup
                'minimum_amount' => 4500,
                'maximum_amount' => 45000,
                'is_active' => true,
                'priority' => 1,
            ],
            [
                'insurance_type_id' => $autoCompreensivo->id,
                'vehicle_type_id' => $vehicleLigeiro?->id,
                'insurer_id' => $globalShield->id,
                'calculation_type' => 'percentage',
                'rate' => 0.0420, // 4.2%
                'price_multiplier' => 1.00,
                'minimum_amount' => 11000,
                'maximum_amount' => 140000,
                'is_active' => true,
                'priority' => 1,
            ],
            [
                'insurance_type_id' => $propriedade->id,
                'vehicle_type_id' => null,
                'insurer_id' => $globalShield->id,
                'calculation_type' => 'percentage',
                'rate' => 0.0035, // 0.35%
                'price_multiplier' => 1.05,
                'minimum_amount' => 8000,
                'maximum_amount' => 200000,
                'is_active' => true,
                'priority' => 1,
            ],

            // SafeGuard - budget-friendly option
            [
                'insurance_type_id' => $responsabilidadeCivil->id,
                'vehicle_type_id' => $vehicleLigeiro?->id,
                'insurer_id' => $safeguard->id,
                'calculation_type' => 'percentage',
                'rate' => 0.0220, // 2.2% (lowest)
                'price_multiplier' => 0.95, // 5% discount
                'minimum_amount' => 4000,
                'maximum_amount' => 40000,
                'is_active' => true,
                'priority' => 1,
            ],
            [
                'insurance_type_id' => $autoCompreensivo->id,
                'vehicle_type_id' => $vehicleLigeiro?->id,
                'insurer_id' => $safeguard->id,
                'calculation_type' => 'percentage',
                'rate' => 0.0400, // 4.0%
                'price_multiplier' => 0.98, // 2% discount
                'minimum_amount' => 10000,
                'maximum_amount' => 130000,
                'is_active' => true,
                'priority' => 1,
            ],
            [
                'insurance_type_id' => $vida->id,
                'vehicle_type_id' => null,
                'insurer_id' => $safeguard->id,
                'calculation_type' => 'percentage',
                'rate' => 0.0150, // 1.5%
                'price_multiplier' => 1.00,
                'minimum_amount' => 5000,
                'maximum_amount' => 100000,
                'is_active' => true,
                'priority' => 1,
            ],

            // Reliable Auto - specialized in auto insurance
            [
                'insurance_type_id' => $responsabilidadeCivil->id,
                'vehicle_type_id' => $vehicleLigeiro?->id,
                'insurer_id' => $reliableAuto->id,
                'calculation_type' => 'percentage',
                'rate' => 0.0230, // 2.3%
                'price_multiplier' => 0.97, // 3% discount (auto specialist)
                'minimum_amount' => 4200,
                'maximum_amount' => 42000,
                'is_active' => true,
                'priority' => 1,
            ],
            [
                'insurance_type_id' => $autoCompreensivo->id,
                'vehicle_type_id' => $vehicleLigeiro?->id,
                'insurer_id' => $reliableAuto->id,
                'calculation_type' => 'percentage',
                'rate' => 0.0390, // 3.9% (best rate for auto)
                'price_multiplier' => 0.95, // 5% discount
                'minimum_amount' => 9500,
                'maximum_amount' => 125000,
                'is_active' => true,
                'priority' => 1,
            ],
        ];

        foreach ($pricingRules as $rule) {
            PricingRule::create($rule);
        }

        $this->command->info('Insurer-specific pricing rules created successfully!');
    }
}
