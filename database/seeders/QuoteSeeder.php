<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Insurer;
use App\Models\Quote;
use Illuminate\Database\Seeder;

class QuoteSeeder extends Seeder
{
    public function run(): void
    {
        $clients = Client::all();
        $insurers = Insurer::where('is_active', true)->get();

        if ($clients->isEmpty() || $insurers->isEmpty()) {
            $this->command->warn('Skipping QuoteSeeder: No clients or insurers found');

            return;
        }

        $insuranceTypes = ['motor', 'property', 'life', 'health', 'travel', 'liability', 'marine'];
        $quotes = [
            [
                'client_id' => $clients[0]->id,
                'insurer_id' => $insurers[0]->id,
                'insurance_type' => 'motor',
                'description' => '2022 Toyota Camry - Comprehensive coverage',
                'sum_insured' => 35000.00,
                'premium' => 1250.00,
                'status' => 'pending',
                'valid_until' => now()->addDays(30),
            ],
            [
                'client_id' => $clients[1]->id,
                'insurer_id' => $insurers[1]->id,
                'insurance_type' => 'property',
                'description' => 'Residential home insurance - 3 bedroom house',
                'sum_insured' => 450000.00,
                'premium' => 2800.00,
                'status' => 'sent_to_insurer',
                'valid_until' => now()->addDays(45),
            ],
            [
                'client_id' => $clients[2]->id,
                'insurer_id' => $insurers[2]->id,
                'insurance_type' => 'life',
                'description' => 'Term life insurance - 20 year coverage',
                'sum_insured' => 500000.00,
                'premium' => 850.00,
                'status' => 'received',
                'valid_until' => now()->addDays(60),
            ],
            [
                'client_id' => $clients[3]->id,
                'insurer_id' => $insurers[3]->id,
                'insurance_type' => 'health',
                'description' => 'Family health insurance - 4 members',
                'sum_insured' => 100000.00,
                'premium' => 3500.00,
                'status' => 'approved',
                'valid_until' => now()->addDays(20),
            ],
            [
                'client_id' => $clients[4]->id,
                'insurer_id' => $insurers[4]->id,
                'insurance_type' => 'travel',
                'description' => 'International travel insurance - Europe trip',
                'sum_insured' => 50000.00,
                'premium' => 180.00,
                'status' => 'rejected',
                'valid_until' => now()->addDays(15),
            ],
            [
                'client_id' => $clients[5]->id,
                'insurer_id' => $insurers[5]->id,
                'insurance_type' => 'motor',
                'description' => '2023 Honda Accord - Third party liability',
                'sum_insured' => 30000.00,
                'premium' => 950.00,
                'status' => 'pending',
                'valid_until' => now()->addDays(40),
            ],
            [
                'client_id' => $clients[6]->id,
                'insurer_id' => $insurers[0]->id,
                'insurance_type' => 'liability',
                'description' => 'Professional liability insurance',
                'sum_insured' => 1000000.00,
                'premium' => 4200.00,
                'status' => 'sent_to_insurer',
                'valid_until' => now()->addDays(35),
            ],
            [
                'client_id' => $clients[7]->id,
                'insurer_id' => $insurers[1]->id,
                'insurance_type' => 'property',
                'description' => 'Commercial property insurance - Office building',
                'sum_insured' => 2000000.00,
                'premium' => 12000.00,
                'status' => 'received',
                'valid_until' => now()->addDays(50),
            ],
            [
                'client_id' => $clients[8]->id,
                'insurer_id' => $insurers[2]->id,
                'insurance_type' => 'marine',
                'description' => 'Cargo insurance - Container shipment',
                'sum_insured' => 150000.00,
                'premium' => 2500.00,
                'status' => 'approved',
                'valid_until' => now()->addDays(25),
            ],
            [
                'client_id' => $clients[9]->id,
                'insurer_id' => $insurers[3]->id,
                'insurance_type' => 'health',
                'description' => 'Individual health insurance',
                'sum_insured' => 50000.00,
                'premium' => 1200.00,
                'status' => 'pending',
                'valid_until' => now()->addDays(30),
            ],
            [
                'client_id' => $clients[0]->id,
                'insurer_id' => $insurers[4]->id,
                'insurance_type' => 'motor',
                'description' => '2021 BMW X5 - Comprehensive coverage',
                'sum_insured' => 65000.00,
                'premium' => 2100.00,
                'status' => 'expired',
                'valid_until' => now()->subDays(10),
            ],
            [
                'client_id' => $clients[1]->id,
                'insurer_id' => $insurers[5]->id,
                'insurance_type' => 'travel',
                'description' => 'Annual multi-trip travel insurance',
                'sum_insured' => 75000.00,
                'premium' => 450.00,
                'status' => 'approved',
                'valid_until' => now()->addDays(20),
            ],
            [
                'client_id' => $clients[2]->id,
                'insurer_id' => $insurers[0]->id,
                'insurance_type' => 'life',
                'description' => 'Whole life insurance with investment',
                'sum_insured' => 750000.00,
                'premium' => 5200.00,
                'status' => 'sent_to_insurer',
                'valid_until' => now()->addDays(45),
            ],
            [
                'client_id' => $clients[3]->id,
                'insurer_id' => $insurers[1]->id,
                'insurance_type' => 'property',
                'description' => 'Condo insurance - Downtown apartment',
                'sum_insured' => 350000.00,
                'premium' => 1800.00,
                'status' => 'received',
                'valid_until' => now()->addDays(55),
            ],
            [
                'client_id' => $clients[4]->id,
                'insurer_id' => $insurers[2]->id,
                'insurance_type' => 'liability',
                'description' => 'General liability insurance - Retail store',
                'sum_insured' => 500000.00,
                'premium' => 3200.00,
                'status' => 'pending',
                'valid_until' => now()->addDays(30),
            ],
            [
                'client_id' => $clients[10]->id,
                'insurer_id' => $insurers[3]->id,
                'insurance_type' => 'property',
                'description' => 'Commercial property - Warehouse facility',
                'sum_insured' => 3500000.00,
                'premium' => 18000.00,
                'status' => 'approved',
                'valid_until' => now()->addDays(40),
            ],
            [
                'client_id' => $clients[11]->id,
                'insurer_id' => $insurers[4]->id,
                'insurance_type' => 'liability',
                'description' => 'Cyber liability insurance',
                'sum_insured' => 2000000.00,
                'premium' => 8500.00,
                'status' => 'sent_to_insurer',
                'valid_until' => now()->addDays(35),
            ],
            [
                'client_id' => $clients[12]->id,
                'insurer_id' => $insurers[5]->id,
                'insurance_type' => 'health',
                'description' => 'Group health insurance - 50 employees',
                'sum_insured' => 5000000.00,
                'premium' => 75000.00,
                'status' => 'received',
                'valid_until' => now()->addDays(60),
            ],
            [
                'client_id' => $clients[13]->id,
                'insurer_id' => $insurers[0]->id,
                'insurance_type' => 'liability',
                'description' => "Workers' compensation insurance",
                'sum_insured' => 1500000.00,
                'premium' => 9800.00,
                'status' => 'approved',
                'valid_until' => now()->addDays(25),
            ],
            [
                'client_id' => $clients[14]->id,
                'insurer_id' => $insurers[1]->id,
                'insurance_type' => 'marine',
                'description' => 'Hull insurance - Commercial vessel',
                'sum_insured' => 5000000.00,
                'premium' => 35000.00,
                'status' => 'pending',
                'valid_until' => now()->addDays(50),
            ],
        ];

        foreach ($quotes as $quote) {
            Quote::create(array_merge($quote, [
                'quote_number' => Quote::generateQuoteNumber(),
            ]));
        }

        $this->command->info('Quotes created successfully! (20 quotes with various statuses)');
    }
}
