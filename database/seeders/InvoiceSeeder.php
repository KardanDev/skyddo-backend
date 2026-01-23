<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\Policy;
use Illuminate\Database\Seeder;

class InvoiceSeeder extends Seeder
{
    public function run(): void
    {
        $clients = Client::all();
        $policies = Policy::all();

        if ($clients->isEmpty()) {
            $this->command->warn('Skipping InvoiceSeeder: No clients found');

            return;
        }

        $invoices = [
            // Paid invoices (policy premiums)
            [
                'client_id' => $clients[0]->id,
                'policy_id' => $policies->count() > 0 ? $policies[0]->id : null,
                'amount' => 1100.00,
                'paid_amount' => 1100.00,
                'due_date' => now()->subMonths(6),
                'paid_at' => now()->subMonths(6)->addDays(5),
                'status' => 'paid',
                'notes' => 'Annual motor insurance premium payment',
            ],
            [
                'client_id' => $clients[1]->id,
                'policy_id' => $policies->count() > 1 ? $policies[1]->id : null,
                'amount' => 3200.00,
                'paid_amount' => 3200.00,
                'due_date' => now()->subMonths(4),
                'paid_at' => now()->subMonths(4)->addDays(3),
                'status' => 'paid',
                'notes' => 'Annual property insurance premium',
            ],
            [
                'client_id' => $clients[2]->id,
                'policy_id' => $policies->count() > 2 ? $policies[2]->id : null,
                'amount' => 950.00,
                'paid_amount' => 950.00,
                'due_date' => now()->subMonths(2),
                'paid_at' => now()->subMonths(2)->addDays(1),
                'status' => 'paid',
                'notes' => 'Life insurance annual premium',
            ],
            [
                'client_id' => $clients[3]->id,
                'policy_id' => $policies->count() > 3 ? $policies[3]->id : null,
                'amount' => 3500.00,
                'paid_amount' => 3500.00,
                'due_date' => now()->subDays(60),
                'paid_at' => now()->subDays(58),
                'status' => 'paid',
                'notes' => 'Family health insurance premium',
            ],
            // Partial payment invoices
            [
                'client_id' => $clients[4]->id,
                'policy_id' => $policies->count() > 4 ? $policies[4]->id : null,
                'amount' => 2800.00,
                'paid_amount' => 1400.00,
                'due_date' => now()->addDays(15),
                'paid_at' => null,
                'status' => 'partial',
                'notes' => 'Property insurance - 50% paid, balance due',
            ],
            [
                'client_id' => $clients[5]->id,
                'policy_id' => $policies->count() > 5 ? $policies[5]->id : null,
                'amount' => 1350.00,
                'paid_amount' => 675.00,
                'due_date' => now()->addDays(20),
                'paid_at' => null,
                'status' => 'partial',
                'notes' => 'Motor insurance - installment 1 of 2 paid',
            ],
            // Sent invoices (awaiting payment)
            [
                'client_id' => $clients[6]->id,
                'policy_id' => $policies->count() > 6 ? $policies[6]->id : null,
                'amount' => 4200.00,
                'paid_amount' => 0.00,
                'due_date' => now()->addDays(30),
                'paid_at' => null,
                'status' => 'sent',
                'notes' => 'Professional liability insurance premium',
            ],
            [
                'client_id' => $clients[7]->id,
                'policy_id' => $policies->count() > 7 ? $policies[7]->id : null,
                'amount' => 12000.00,
                'paid_amount' => 0.00,
                'due_date' => now()->addDays(25),
                'paid_at' => null,
                'status' => 'sent',
                'notes' => 'Commercial property insurance annual premium',
            ],
            [
                'client_id' => $clients[8]->id,
                'policy_id' => $policies->count() > 8 ? $policies[8]->id : null,
                'amount' => 2500.00,
                'paid_amount' => 0.00,
                'due_date' => now()->addDays(35),
                'paid_at' => null,
                'status' => 'sent',
                'notes' => 'Marine cargo insurance premium',
            ],
            // Overdue invoices
            [
                'client_id' => $clients[9]->id,
                'policy_id' => $policies->count() > 9 ? $policies[9]->id : null,
                'amount' => 1200.00,
                'paid_amount' => 0.00,
                'due_date' => now()->subDays(10),
                'paid_at' => null,
                'status' => 'overdue',
                'notes' => 'Health insurance premium - OVERDUE',
            ],
            [
                'client_id' => $clients[10]->id,
                'policy_id' => $policies->count() > 10 ? $policies[10]->id : null,
                'amount' => 18000.00,
                'paid_amount' => 0.00,
                'due_date' => now()->subDays(5),
                'paid_at' => null,
                'status' => 'overdue',
                'notes' => 'Warehouse property insurance - payment overdue',
            ],
            [
                'client_id' => $clients[11]->id,
                'policy_id' => $policies->count() > 11 ? $policies[11]->id : null,
                'amount' => 8500.00,
                'paid_amount' => 0.00,
                'due_date' => now()->subDays(15),
                'paid_at' => null,
                'status' => 'overdue',
                'notes' => 'Cyber liability insurance - urgent payment required',
            ],
            // Draft invoices (not sent yet)
            [
                'client_id' => $clients[12]->id,
                'policy_id' => $policies->count() > 12 ? $policies[12]->id : null,
                'amount' => 75000.00,
                'paid_amount' => 0.00,
                'due_date' => now()->addDays(45),
                'paid_at' => null,
                'status' => 'draft',
                'notes' => 'Group health insurance - 50 employees (draft)',
            ],
            [
                'client_id' => $clients[13]->id,
                'policy_id' => $policies->count() > 13 ? $policies[13]->id : null,
                'amount' => 9800.00,
                'paid_amount' => 0.00,
                'due_date' => now()->addDays(40),
                'paid_at' => null,
                'status' => 'draft',
                'notes' => "Workers' compensation insurance (pending approval)",
            ],
            // Additional invoices for non-policy services
            [
                'client_id' => $clients[14]->id,
                'policy_id' => null,
                'amount' => 500.00,
                'paid_amount' => 500.00,
                'due_date' => now()->subDays(30),
                'paid_at' => now()->subDays(28),
                'status' => 'paid',
                'notes' => 'Insurance consultation and advisory services',
            ],
            [
                'client_id' => $clients[0]->id,
                'policy_id' => null,
                'amount' => 350.00,
                'paid_amount' => 350.00,
                'due_date' => now()->subDays(45),
                'paid_at' => now()->subDays(43),
                'status' => 'paid',
                'notes' => 'Risk assessment report',
            ],
            [
                'client_id' => $clients[1]->id,
                'policy_id' => null,
                'amount' => 750.00,
                'paid_amount' => 0.00,
                'due_date' => now()->addDays(10),
                'paid_at' => null,
                'status' => 'sent',
                'notes' => 'Claims assistance and documentation services',
            ],
            // Cancelled invoice
            [
                'client_id' => $clients[2]->id,
                'policy_id' => null,
                'amount' => 1200.00,
                'paid_amount' => 0.00,
                'due_date' => now()->subDays(20),
                'paid_at' => null,
                'status' => 'cancelled',
                'notes' => 'Policy quote - customer decided not to proceed',
            ],
        ];

        foreach ($invoices as $invoice) {
            Invoice::create(array_merge($invoice, [
                'invoice_number' => Invoice::generateInvoiceNumber(),
            ]));
        }

        $this->command->info('Invoices created successfully! (18 invoices: 6 paid, 2 partial, 3 sent, 3 overdue, 2 draft, 1 cancelled, 1 misc)');
    }
}
