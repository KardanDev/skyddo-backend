<?php

namespace Database\Seeders;

use App\Models\Claim;
use App\Models\Policy;
use Illuminate\Database\Seeder;

class ClaimSeeder extends Seeder
{
    public function run(): void
    {
        $policies = Policy::with('client')->whereIn('status', ['active', 'expired'])->get();

        if ($policies->isEmpty()) {
            $this->command->warn('Skipping ClaimSeeder: No policies found');

            return;
        }

        $claims = [
            // Motor insurance claims
            [
                'client_id' => $policies[0]->client_id,
                'policy_id' => $policies[0]->id,
                'description' => 'Rear-end collision on Highway 101. Vehicle sustained damage to rear bumper and trunk area.',
                'incident_date' => now()->subDays(15),
                'claim_amount' => 4500.00,
                'approved_amount' => 4200.00,
                'status' => 'approved',
                'notes' => 'Police report filed. Claim approved after inspection.',
            ],
            [
                'client_id' => $policies[0]->client_id,
                'policy_id' => $policies[0]->id,
                'description' => 'Windshield cracked by flying debris on freeway.',
                'incident_date' => now()->subDays(8),
                'claim_amount' => 650.00,
                'approved_amount' => null,
                'status' => 'under_review',
                'notes' => 'Awaiting repair shop estimate verification.',
            ],
            // Property insurance claims
            [
                'client_id' => $policies[1]->client_id,
                'policy_id' => $policies[1]->id,
                'description' => 'Water damage from burst pipe in kitchen. Flooring and cabinets affected.',
                'incident_date' => now()->subDays(22),
                'claim_amount' => 12000.00,
                'approved_amount' => 11500.00,
                'status' => 'settled',
                'notes' => 'Claim settled. Payment processed to contractor.',
            ],
            [
                'client_id' => $policies[1]->client_id,
                'policy_id' => $policies[1]->id,
                'description' => 'Roof damage from fallen tree branch during storm.',
                'incident_date' => now()->subDays(5),
                'claim_amount' => 8500.00,
                'approved_amount' => null,
                'status' => 'docs_requested',
                'notes' => 'Requested photos of damage and contractor quotes.',
            ],
            // Health insurance claims
            [
                'client_id' => $policies[2]->client_id,
                'policy_id' => $policies[2]->id,
                'description' => 'Emergency room visit for broken arm. X-rays and treatment.',
                'incident_date' => now()->subDays(30),
                'claim_amount' => 3200.00,
                'approved_amount' => 2800.00,
                'status' => 'settled',
                'notes' => 'Deductible applied. Claim paid to hospital.',
            ],
            [
                'client_id' => $policies[2]->client_id,
                'policy_id' => $policies[2]->id,
                'description' => 'Annual physical examination and lab work.',
                'incident_date' => now()->subDays(12),
                'claim_amount' => 850.00,
                'approved_amount' => 850.00,
                'status' => 'approved',
                'notes' => 'Routine preventive care - fully covered.',
            ],
            // Liability claims
            [
                'client_id' => $policies[6]->client_id,
                'policy_id' => $policies[6]->id,
                'description' => 'Customer slip and fall incident at retail location. Medical expenses claimed.',
                'incident_date' => now()->subDays(45),
                'claim_amount' => 18000.00,
                'approved_amount' => null,
                'status' => 'forwarded',
                'notes' => 'Case forwarded to legal team for review. Investigation ongoing.',
            ],
            // Marine insurance claim
            [
                'client_id' => $policies[3]->client_id,
                'policy_id' => $policies[3]->id,
                'description' => 'Cargo container damaged during loading. Electronics goods affected.',
                'incident_date' => now()->subDays(18),
                'claim_amount' => 25000.00,
                'approved_amount' => 22000.00,
                'status' => 'approved',
                'notes' => 'Surveyor report received. Depreciation applied.',
            ],
            // Rejected claim
            [
                'client_id' => $policies[4]->client_id,
                'policy_id' => $policies[4]->id,
                'description' => 'Pre-existing condition not covered under policy terms.',
                'incident_date' => now()->subDays(25),
                'claim_amount' => 5500.00,
                'approved_amount' => null,
                'status' => 'rejected',
                'notes' => 'Claim denied - condition pre-dates policy start date per medical records.',
            ],
            // Submitted claims (recently filed)
            [
                'client_id' => $policies[5]->client_id,
                'policy_id' => $policies[5]->id,
                'description' => 'Hail damage to vehicle exterior. Multiple dents on hood and roof.',
                'incident_date' => now()->subDays(3),
                'claim_amount' => 3800.00,
                'approved_amount' => null,
                'status' => 'submitted',
                'notes' => 'Initial claim filed. Awaiting adjuster assignment.',
            ],
            [
                'client_id' => $policies[7]->client_id,
                'policy_id' => $policies[7]->id,
                'description' => 'Smoke damage to property contents from kitchen fire.',
                'incident_date' => now()->subDays(6),
                'claim_amount' => 9200.00,
                'approved_amount' => null,
                'status' => 'under_review',
                'notes' => 'Fire department report on file. Assessing extent of damage.',
            ],
            [
                'client_id' => $policies[8]->client_id,
                'policy_id' => $policies[8]->id,
                'description' => 'Employee workplace injury - back strain while lifting equipment.',
                'incident_date' => now()->subDays(10),
                'claim_amount' => 6500.00,
                'approved_amount' => null,
                'status' => 'docs_requested',
                'notes' => 'Requested medical records and incident report from employer.',
            ],
        ];

        foreach ($claims as $claim) {
            Claim::create(array_merge($claim, [
                'claim_number' => Claim::generateClaimNumber(),
            ]));
        }

        $this->command->info('Claims created successfully! (12 claims with various statuses)');
    }
}
