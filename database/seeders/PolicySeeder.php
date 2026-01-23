<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Insurer;
use App\Models\Policy;
use App\Models\Quote;
use Illuminate\Database\Seeder;

class PolicySeeder extends Seeder
{
    public function run(): void
    {
        $clients = Client::all();
        $insurers = Insurer::where('is_active', true)->get();
        $quotes = Quote::where('status', 'approved')->get();

        if ($clients->isEmpty() || $insurers->isEmpty()) {
            $this->command->warn('Skipping PolicySeeder: No clients or insurers found');

            return;
        }

        $policies = [
            // Policies converted from approved quotes
            [
                'client_id' => $clients[3]->id,
                'insurer_id' => $insurers[3]->id,
                'quote_id' => $quotes->count() > 0 ? $quotes[0]->id : null,
                'insurance_type' => 'health',
                'description' => 'Family health insurance - 4 members',
                'sum_insured' => 100000.00,
                'premium' => 3500.00,
                'start_date' => now()->subDays(60),
                'end_date' => now()->addMonths(11)->subDays(60),
                'status' => 'active',
            ],
            [
                'client_id' => $clients[8]->id,
                'insurer_id' => $insurers[2]->id,
                'quote_id' => $quotes->count() > 1 ? $quotes[1]->id : null,
                'insurance_type' => 'marine',
                'description' => 'Cargo insurance - Container shipment',
                'sum_insured' => 150000.00,
                'premium' => 2500.00,
                'start_date' => now()->subDays(30),
                'end_date' => now()->addMonths(11)->subDays(30),
                'status' => 'active',
            ],
            [
                'client_id' => $clients[10]->id,
                'insurer_id' => $insurers[3]->id,
                'quote_id' => $quotes->count() > 2 ? $quotes[2]->id : null,
                'insurance_type' => 'property',
                'description' => 'Commercial property - Warehouse facility',
                'sum_insured' => 3500000.00,
                'premium' => 18000.00,
                'start_date' => now()->subDays(90),
                'end_date' => now()->addMonths(9)->subDays(90),
                'status' => 'active',
            ],
            // Additional active policies (not from quotes)
            [
                'client_id' => $clients[0]->id,
                'insurer_id' => $insurers[0]->id,
                'quote_id' => null,
                'insurance_type' => 'motor',
                'description' => '2020 Honda Civic - Comprehensive coverage',
                'sum_insured' => 28000.00,
                'premium' => 1100.00,
                'start_date' => now()->subMonths(6),
                'end_date' => now()->addMonths(6),
                'status' => 'active',
            ],
            [
                'client_id' => $clients[1]->id,
                'insurer_id' => $insurers[1]->id,
                'quote_id' => null,
                'insurance_type' => 'property',
                'description' => 'Home insurance - Single family residence',
                'sum_insured' => 525000.00,
                'premium' => 3200.00,
                'start_date' => now()->subMonths(4),
                'end_date' => now()->addMonths(8),
                'status' => 'active',
            ],
            [
                'client_id' => $clients[2]->id,
                'insurer_id' => $insurers[2]->id,
                'quote_id' => null,
                'insurance_type' => 'life',
                'description' => 'Term life insurance - 25 year term',
                'sum_insured' => 600000.00,
                'premium' => 950.00,
                'start_date' => now()->subMonths(2),
                'end_date' => now()->addMonths(10),
                'status' => 'active',
            ],
            [
                'client_id' => $clients[5]->id,
                'insurer_id' => $insurers[4]->id,
                'quote_id' => null,
                'insurance_type' => 'motor',
                'description' => '2021 Toyota RAV4 - Comprehensive coverage',
                'sum_insured' => 38000.00,
                'premium' => 1350.00,
                'start_date' => now()->subMonths(3),
                'end_date' => now()->addMonths(9),
                'status' => 'active',
            ],
            // Expired policies
            [
                'client_id' => $clients[4]->id,
                'insurer_id' => $insurers[3]->id,
                'quote_id' => null,
                'insurance_type' => 'travel',
                'description' => 'Annual travel insurance',
                'sum_insured' => 60000.00,
                'premium' => 420.00,
                'start_date' => now()->subMonths(14),
                'end_date' => now()->subMonths(2),
                'status' => 'expired',
            ],
            [
                'client_id' => $clients[6]->id,
                'insurer_id' => $insurers[5]->id,
                'quote_id' => null,
                'insurance_type' => 'liability',
                'description' => 'Professional liability insurance',
                'sum_insured' => 1000000.00,
                'premium' => 4200.00,
                'start_date' => now()->subMonths(13),
                'end_date' => now()->subMonth(),
                'status' => 'expired',
            ],
            // Cancelled policy
            [
                'client_id' => $clients[7]->id,
                'insurer_id' => $insurers[0]->id,
                'quote_id' => null,
                'insurance_type' => 'property',
                'description' => 'Commercial property insurance',
                'sum_insured' => 1800000.00,
                'premium' => 11000.00,
                'start_date' => now()->subMonths(8),
                'end_date' => now()->addMonths(4),
                'status' => 'cancelled',
            ],
            // Policies pending renewal (expiring soon)
            [
                'client_id' => $clients[9]->id,
                'insurer_id' => $insurers[1]->id,
                'quote_id' => null,
                'insurance_type' => 'health',
                'description' => 'Individual health insurance',
                'sum_insured' => 50000.00,
                'premium' => 1200.00,
                'start_date' => now()->subMonths(11)->addDays(15),
                'end_date' => now()->addDays(15),
                'status' => 'pending_renewal',
            ],
            [
                'client_id' => $clients[11]->id,
                'insurer_id' => $insurers[2]->id,
                'quote_id' => null,
                'insurance_type' => 'liability',
                'description' => 'General liability insurance',
                'sum_insured' => 2000000.00,
                'premium' => 8500.00,
                'start_date' => now()->subMonths(11)->addDays(20),
                'end_date' => now()->addDays(20),
                'status' => 'pending_renewal',
            ],
            // Corporate client policies
            [
                'client_id' => $clients[12]->id,
                'insurer_id' => $insurers[4]->id,
                'quote_id' => null,
                'insurance_type' => 'health',
                'description' => 'Group health insurance - 50 employees',
                'sum_insured' => 5000000.00,
                'premium' => 75000.00,
                'start_date' => now()->subMonths(5),
                'end_date' => now()->addMonths(7),
                'status' => 'active',
            ],
            [
                'client_id' => $clients[13]->id,
                'insurer_id' => $insurers[5]->id,
                'quote_id' => null,
                'insurance_type' => 'liability',
                'description' => "Workers' compensation insurance",
                'sum_insured' => 1500000.00,
                'premium' => 9800.00,
                'start_date' => now()->subMonths(7),
                'end_date' => now()->addMonths(5),
                'status' => 'active',
            ],
            [
                'client_id' => $clients[14]->id,
                'insurer_id' => $insurers[0]->id,
                'quote_id' => null,
                'insurance_type' => 'marine',
                'description' => 'Hull insurance - Commercial vessel',
                'sum_insured' => 5000000.00,
                'premium' => 35000.00,
                'start_date' => now()->subMonths(3),
                'end_date' => now()->addMonths(9),
                'status' => 'active',
            ],
        ];

        foreach ($policies as $policy) {
            Policy::create(array_merge($policy, [
                'policy_number' => Policy::generatePolicyNumber(),
            ]));
        }

        $this->command->info('Policies created successfully! (15 policies: 10 active, 2 expired, 1 cancelled, 2 pending renewal)');
    }
}
