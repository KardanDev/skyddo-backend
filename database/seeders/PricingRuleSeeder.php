<?php

namespace Database\Seeders;

use App\Models\InsuranceType;
use App\Models\PricingRule;
use App\Models\VehicleType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PricingRuleSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // Get insurance types
        $responsabilidadeCivil = InsuranceType::where('slug', 'responsabilidade-civil')->first();
        $autoCompreensivo = InsuranceType::where('slug', 'auto-compreensivo')->first();
        $propriedade = InsuranceType::where('slug', 'propriedade')->first();
        $saude = InsuranceType::where('slug', 'saude')->first();
        $vida = InsuranceType::where('slug', 'vida')->first();
        $viagem = InsuranceType::where('slug', 'viagem')->first();
        $maritimo = InsuranceType::where('slug', 'maritimo')->first();

        // Get vehicle types
        $motocicleta = VehicleType::where('slug', 'motocicleta')->first();
        $automovelLigeiro = VehicleType::where('slug', 'automovel-ligeiro')->first();
        $automovelPesado = VehicleType::where('slug', 'automovel-pesado')->first();
        $autocarro = VehicleType::where('slug', 'autocarro-minibus')->first();
        $trator = VehicleType::where('slug', 'trator')->first();
        $reboque = VehicleType::where('slug', 'reboque')->first();

        $pricingRules = [
            // Responsabilidade Civil - Different rates for different vehicle types
            [
                'insurance_type_id' => $responsabilidadeCivil->id,
                'vehicle_type_id' => $motocicleta->id,
                'calculation_type' => 'percentage',
                'rate' => 0.0150, // 1.5% of vehicle value
                'minimum_amount' => 500.00,
                'maximum_amount' => 5000.00,
                'is_active' => true,
                'priority' => 1,
            ],
            [
                'insurance_type_id' => $responsabilidadeCivil->id,
                'vehicle_type_id' => $automovelLigeiro->id,
                'calculation_type' => 'percentage',
                'rate' => 0.0200, // 2% of vehicle value
                'minimum_amount' => 800.00,
                'maximum_amount' => 15000.00,
                'is_active' => true,
                'priority' => 1,
            ],
            [
                'insurance_type_id' => $responsabilidadeCivil->id,
                'vehicle_type_id' => $automovelPesado->id,
                'calculation_type' => 'percentage',
                'rate' => 0.0250, // 2.5% of vehicle value
                'minimum_amount' => 2000.00,
                'maximum_amount' => 30000.00,
                'is_active' => true,
                'priority' => 1,
            ],
            [
                'insurance_type_id' => $responsabilidadeCivil->id,
                'vehicle_type_id' => $autocarro->id,
                'calculation_type' => 'percentage',
                'rate' => 0.0300, // 3% of vehicle value
                'minimum_amount' => 3000.00,
                'maximum_amount' => 40000.00,
                'is_active' => true,
                'priority' => 1,
            ],
            [
                'insurance_type_id' => $responsabilidadeCivil->id,
                'vehicle_type_id' => $trator->id,
                'calculation_type' => 'percentage',
                'rate' => 0.0180, // 1.8% of vehicle value
                'minimum_amount' => 1200.00,
                'maximum_amount' => 20000.00,
                'is_active' => true,
                'priority' => 1,
            ],
            [
                'insurance_type_id' => $responsabilidadeCivil->id,
                'vehicle_type_id' => $reboque->id,
                'calculation_type' => 'percentage',
                'rate' => 0.0100, // 1% of vehicle value
                'minimum_amount' => 400.00,
                'maximum_amount' => 8000.00,
                'is_active' => true,
                'priority' => 1,
            ],

            // Auto Compreensivo - Higher rates for comprehensive coverage
            [
                'insurance_type_id' => $autoCompreensivo->id,
                'vehicle_type_id' => $motocicleta->id,
                'calculation_type' => 'percentage',
                'rate' => 0.0350, // 3.5% of vehicle value
                'minimum_amount' => 1500.00,
                'maximum_amount' => 10000.00,
                'is_active' => true,
                'priority' => 1,
            ],
            [
                'insurance_type_id' => $autoCompreensivo->id,
                'vehicle_type_id' => $automovelLigeiro->id,
                'calculation_type' => 'percentage',
                'rate' => 0.0450, // 4.5% of vehicle value
                'minimum_amount' => 2500.00,
                'maximum_amount' => 30000.00,
                'is_active' => true,
                'priority' => 1,
            ],
            [
                'insurance_type_id' => $autoCompreensivo->id,
                'vehicle_type_id' => $automovelPesado->id,
                'calculation_type' => 'percentage',
                'rate' => 0.0500, // 5% of vehicle value
                'minimum_amount' => 5000.00,
                'maximum_amount' => 60000.00,
                'is_active' => true,
                'priority' => 1,
            ],

            // Seguro de Propriedade - No vehicle type required
            [
                'insurance_type_id' => $propriedade->id,
                'vehicle_type_id' => null,
                'calculation_type' => 'percentage',
                'rate' => 0.0080, // 0.8% of property value
                'minimum_amount' => 3000.00,
                'maximum_amount' => 100000.00,
                'is_active' => true,
                'priority' => 1,
            ],

            // Seguro de Saúde - Fixed rates based on coverage value
            [
                'insurance_type_id' => $saude->id,
                'vehicle_type_id' => null,
                'calculation_type' => 'tiered',
                'rate' => null,
                'minimum_amount' => 5000.00,
                'maximum_amount' => null,
                'tiered_rates' => [
                    ['min' => 0, 'max' => 100000, 'rate' => 0.0500],
                    ['min' => 100001, 'max' => 500000, 'rate' => 0.0400],
                    ['min' => 500001, 'max' => PHP_FLOAT_MAX, 'rate' => 0.0300],
                ],
                'is_active' => true,
                'priority' => 1,
            ],

            // Seguro de Vida - Percentage based on coverage
            [
                'insurance_type_id' => $vida->id,
                'vehicle_type_id' => null,
                'calculation_type' => 'percentage',
                'rate' => 0.0120, // 1.2% of coverage value
                'minimum_amount' => 2000.00,
                'maximum_amount' => 50000.00,
                'is_active' => true,
                'priority' => 1,
            ],

            // Seguro de Viagem - Percentage of trip value
            [
                'insurance_type_id' => $viagem->id,
                'vehicle_type_id' => null,
                'calculation_type' => 'percentage',
                'rate' => 0.0300, // 3% of trip value
                'minimum_amount' => 300.00,
                'maximum_amount' => 5000.00,
                'is_active' => true,
                'priority' => 1,
            ],

            // Seguro Marítimo - Percentage of cargo/vessel value
            [
                'insurance_type_id' => $maritimo->id,
                'vehicle_type_id' => null,
                'calculation_type' => 'percentage',
                'rate' => 0.0200, // 2% of cargo/vessel value
                'minimum_amount' => 5000.00,
                'maximum_amount' => 200000.00,
                'is_active' => true,
                'priority' => 1,
            ],
        ];

        foreach ($pricingRules as $rule) {
            PricingRule::create($rule);
        }

        $this->command->info('Pricing rules seeded successfully!');
    }
}
