<?php

namespace Database\Seeders;

use App\Models\InsuranceType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class InsuranceTypeSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $insuranceTypes = [
            [
                'name' => 'Responsabilidade Civil',
                'slug' => 'responsabilidade-civil',
                'description' => 'Seguro de responsabilidade civil obrigatório',
                'requires_vehicle' => true,
                'is_active' => true,
                'sort_order' => 1,
                'requirements' => [
                    'vehicle_make' => ['label' => 'Marca do Veículo', 'field_type' => 'text', 'required' => true],
                    'vehicle_model' => ['label' => 'Modelo do Veículo', 'field_type' => 'text', 'required' => true],
                    'vehicle_year' => ['label' => 'Ano do Veículo', 'field_type' => 'number', 'required' => true],
                    'vehicle_value' => ['label' => 'Valor do Veículo (MZN)', 'field_type' => 'number', 'required' => true],
                ],
            ],
            [
                'name' => 'Seguro Auto (Compreensivo)',
                'slug' => 'auto-compreensivo',
                'description' => 'Seguro automóvel compreensivo com cobertura total',
                'requires_vehicle' => true,
                'is_active' => true,
                'sort_order' => 2,
                'requirements' => [
                    'vehicle_make' => ['label' => 'Marca do Veículo', 'field_type' => 'text', 'required' => true],
                    'vehicle_model' => ['label' => 'Modelo do Veículo', 'field_type' => 'text', 'required' => true],
                    'vehicle_year' => ['label' => 'Ano do Veículo', 'field_type' => 'number', 'required' => true],
                    'vehicle_value' => ['label' => 'Valor do Veículo (MZN)', 'field_type' => 'number', 'required' => true],
                    'driver_age' => ['label' => 'Idade do Condutor', 'field_type' => 'number', 'required' => true],
                    'driver_license_years' => ['label' => 'Anos de Carta de Condução', 'field_type' => 'number', 'required' => true],
                    'coverage_level' => ['label' => 'Nível de Cobertura', 'field_type' => 'select', 'required' => true, 'options' => ['basic', 'standard', 'premium']],
                ],
            ],
            [
                'name' => 'Seguro de Propriedade',
                'slug' => 'propriedade',
                'description' => 'Seguro para propriedades e imóveis',
                'requires_vehicle' => false,
                'is_active' => true,
                'sort_order' => 3,
                'requirements' => [
                    'property_type' => ['label' => 'Tipo de Propriedade', 'field_type' => 'select', 'required' => true, 'options' => ['residential', 'commercial', 'industrial']],
                    'property_value' => ['label' => 'Valor da Propriedade (MZN)', 'field_type' => 'number', 'required' => true],
                    'location' => ['label' => 'Localização', 'field_type' => 'text', 'required' => true],
                    'construction_year' => ['label' => 'Ano de Construção', 'field_type' => 'number', 'required' => true],
                    'building_materials' => ['label' => 'Materiais de Construção', 'field_type' => 'text', 'required' => false],
                ],
            ],
            [
                'name' => 'Seguro de Saúde',
                'slug' => 'saude',
                'description' => 'Seguro de saúde individual ou familiar',
                'requires_vehicle' => false,
                'is_active' => true,
                'sort_order' => 4,
                'requirements' => [
                    'age' => ['label' => 'Idade', 'field_type' => 'number', 'required' => true],
                    'pre_existing_conditions' => ['label' => 'Condições Pré-Existentes', 'field_type' => 'text', 'required' => false],
                    'coverage_type' => ['label' => 'Tipo de Cobertura', 'field_type' => 'select', 'required' => true, 'options' => ['individual', 'family']],
                    'dependents' => ['label' => 'Número de Dependentes', 'field_type' => 'number', 'required' => false],
                ],
            ],
            [
                'name' => 'Seguro de Vida',
                'slug' => 'vida',
                'description' => 'Seguro de vida individual',
                'requires_vehicle' => false,
                'is_active' => true,
                'sort_order' => 5,
                'requirements' => [
                    'age' => ['label' => 'Idade', 'field_type' => 'number', 'required' => true],
                    'health_status' => ['label' => 'Estado de Saúde', 'field_type' => 'select', 'required' => true, 'options' => ['excellent', 'good', 'fair', 'poor']],
                    'coverage_amount' => ['label' => 'Montante de Cobertura (MZN)', 'field_type' => 'number', 'required' => true],
                    'beneficiaries' => ['label' => 'Número de Beneficiários', 'field_type' => 'number', 'required' => true],
                    'smoker' => ['label' => 'Fumador', 'field_type' => 'select', 'required' => true, 'options' => ['yes', 'no']],
                ],
            ],
            [
                'name' => 'Seguro de Viagem',
                'slug' => 'viagem',
                'description' => 'Seguro para viagens nacionais e internacionais',
                'requires_vehicle' => false,
                'is_active' => true,
                'sort_order' => 6,
                'requirements' => [
                    'destination' => ['label' => 'Destino', 'field_type' => 'text', 'required' => true],
                    'duration' => ['label' => 'Duração (dias)', 'field_type' => 'number', 'required' => true],
                    'travelers_count' => ['label' => 'Número de Viajantes', 'field_type' => 'number', 'required' => true],
                    'coverage_type' => ['label' => 'Tipo de Cobertura', 'field_type' => 'select', 'required' => true, 'options' => ['basic', 'comprehensive', 'adventure']],
                    'trip_purpose' => ['label' => 'Propósito da Viagem', 'field_type' => 'select', 'required' => false, 'options' => ['tourism', 'business', 'education']],
                ],
            ],
            [
                'name' => 'Seguro Marítimo',
                'slug' => 'maritimo',
                'description' => 'Seguro para embarcações e carga marítima',
                'requires_vehicle' => false,
                'is_active' => true,
                'sort_order' => 7,
                'requirements' => [
                    'cargo_type' => ['label' => 'Tipo de Carga', 'field_type' => 'text', 'required' => true],
                    'cargo_value' => ['label' => 'Valor da Carga (MZN)', 'field_type' => 'number', 'required' => true],
                    'route' => ['label' => 'Rota', 'field_type' => 'text', 'required' => true],
                    'vessel_type' => ['label' => 'Tipo de Embarcação', 'field_type' => 'text', 'required' => true],
                    'vessel_age' => ['label' => 'Idade da Embarcação (anos)', 'field_type' => 'number', 'required' => false],
                ],
            ],
        ];

        foreach ($insuranceTypes as $type) {
            InsuranceType::create($type);
        }

        $this->command->info('Insurance types seeded successfully!');
    }
}
