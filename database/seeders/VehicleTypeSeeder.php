<?php

namespace Database\Seeders;

use App\Models\VehicleType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class VehicleTypeSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $vehicleTypes = [
            [
                'name' => 'Motocicleta',
                'slug' => 'motocicleta',
                'description' => 'Motocicletas e scooters',
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Automóvel Ligeiro',
                'slug' => 'automovel-ligeiro',
                'description' => 'Carros particulares e veículos ligeiros',
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Automóvel Pesado',
                'slug' => 'automovel-pesado',
                'description' => 'Camiões e veículos pesados',
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'name' => 'Autocarro/Minibus',
                'slug' => 'autocarro-minibus',
                'description' => 'Autocarros de transporte público e minibus',
                'is_active' => true,
                'sort_order' => 4,
            ],
            [
                'name' => 'Trator',
                'slug' => 'trator',
                'description' => 'Tratores e máquinas agrícolas',
                'is_active' => true,
                'sort_order' => 5,
            ],
            [
                'name' => 'Reboque',
                'slug' => 'reboque',
                'description' => 'Reboques e atrelados',
                'is_active' => true,
                'sort_order' => 6,
            ],
        ];

        foreach ($vehicleTypes as $type) {
            VehicleType::create($type);
        }

        $this->command->info('Vehicle types seeded successfully!');
    }
}
