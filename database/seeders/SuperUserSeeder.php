<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class SuperUserSeeder extends Seeder
{
    /**
     * Create the initial super user account.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@skyydo.com'],
            [
                'name' => 'Super Admin',
                'password' => 'password123',
                'role' => User::ROLE_SUPER_USER,
            ]
        );

        $this->command->info('Super user created: admin@skyydo.com / password123');
    }
}
