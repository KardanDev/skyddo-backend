<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed data in correct dependency order
        $this->call([
            CompanySettingSeeder::class,  // Create company profile first
            UserSeeder::class,            // Create users (super user, admins, members)
            SuperUserSeeder::class,       // Additional super user for simple testing
            InsuranceTypeSeeder::class,   // Create insurance types
            VehicleTypeSeeder::class,     // Create vehicle types
            PricingRuleSeeder::class,     // Create pricing rules
            InsurerSeeder::class,         // Create insurance companies
            InsurerInsuranceTypeSeeder::class, // Map insurers to insurance types
            InsurerPricingRuleSeeder::class,   // Create insurer-specific pricing rules
            ClientSeeder::class,          // Create clients and assign to team members
            QuoteSeeder::class,           // Create quotes (requires clients and insurers)
            PolicySeeder::class,          // Create policies (requires clients, insurers, quotes)
            ClaimSeeder::class,           // Create claims (requires policies)
            InvoiceSeeder::class,         // Create invoices (requires clients and policies)
        ]);

        $this->command->info('');
        $this->command->info('========================================');
        $this->command->info('Database seeding completed successfully!');
        $this->command->info('========================================');
        $this->command->info('');
    }
}
