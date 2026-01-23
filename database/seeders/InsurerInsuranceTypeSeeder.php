<?php

namespace Database\Seeders;

use App\Models\Insurer;
use App\Models\InsuranceType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class InsurerInsuranceTypeSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get insurance types by slug
        $responsabilidadeCivil = InsuranceType::where('slug', 'responsabilidade-civil')->first();
        $autoCompreensivo = InsuranceType::where('slug', 'auto-compreensivo')->first();
        $propriedade = InsuranceType::where('slug', 'propriedade')->first();
        $saude = InsuranceType::where('slug', 'saude')->first();
        $vida = InsuranceType::where('slug', 'vida')->first();
        $viagem = InsuranceType::where('slug', 'viagem')->first();
        $maritimo = InsuranceType::where('slug', 'maritimo')->first();

        // Define mappings: insurer name => [insurance types with turnaround days]
        $mappings = [
            'ABC Insurance Company' => [
                $responsabilidadeCivil->id => ['turnaround_days' => 2],
                $autoCompreensivo->id => ['turnaround_days' => 3],
                $propriedade->id => ['turnaround_days' => 3],
                $saude->id => ['turnaround_days' => 5],
                $vida->id => ['turnaround_days' => 5],
                $viagem->id => ['turnaround_days' => 1],
                $maritimo->id => ['turnaround_days' => 7],
            ],
            'Global Shield Insurance' => [
                $responsabilidadeCivil->id => ['turnaround_days' => 3],
                $autoCompreensivo->id => ['turnaround_days' => 3],
                $propriedade->id => ['turnaround_days' => 4],
                $viagem->id => ['turnaround_days' => 2],
            ],
            'Premier Life Assurance' => [
                $vida->id => ['turnaround_days' => 4],
                $saude->id => ['turnaround_days' => 4],
            ],
            'SafeGuard Insurance Group' => [
                $responsabilidadeCivil->id => ['turnaround_days' => 2],
                $autoCompreensivo->id => ['turnaround_days' => 2],
                $propriedade->id => ['turnaround_days' => 3],
                $saude->id => ['turnaround_days' => 3],
                $vida->id => ['turnaround_days' => 3],
                $viagem->id => ['turnaround_days' => 1],
                $maritimo->id => ['turnaround_days' => 5],
            ],
            'United Health Coverage' => [
                $saude->id => ['turnaround_days' => 3],
                $vida->id => ['turnaround_days' => 4],
            ],
            'Reliable Auto Insurance' => [
                $responsabilidadeCivil->id => ['turnaround_days' => 1],
                $autoCompreensivo->id => ['turnaround_days' => 2],
            ],
            'TravelSafe Insurance Co' => [
                $viagem->id => ['turnaround_days' => 1],
            ],
            'Property Shield Ltd' => [
                $propriedade->id => ['turnaround_days' => 3],
                $maritimo->id => ['turnaround_days' => 6],
            ],
        ];

        foreach ($mappings as $insurerName => $insuranceTypes) {
            $insurer = Insurer::where('name', $insurerName)->first();

            if ($insurer) {
                foreach ($insuranceTypes as $insuranceTypeId => $pivotData) {
                    $insurer->insuranceTypes()->attach($insuranceTypeId, array_merge($pivotData, [
                        'is_active' => true,
                    ]));
                }
            }
        }

        $this->command->info('Insurer-Insurance Type mappings created successfully!');
    }
}
