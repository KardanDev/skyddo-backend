<?php

namespace App\Console\Commands;

use App\Events\InvoiceOverdue;
use App\Mail\InvoiceOverdueReminder;
use App\Models\Invoice;
use App\Services\InvoiceService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class MarkOverdueInvoices extends Command
{
    protected $signature = 'invoices:mark-overdue {--send-reminders : Also send reminder emails}';

    protected $description = 'Mark overdue invoices and optionally send reminder emails';

    public function handle(InvoiceService $invoiceService): int
    {
        $count = $invoiceService->markOverdue();

        $this->info("Marked {$count} invoices as overdue.");

        if ($this->option('send-reminders')) {
            $this->sendReminders();
        }

        return Command::SUCCESS;
    }

    private function sendReminders(): void
    {
        $invoices = Invoice::where('status', 'overdue')
            ->with('client')
            ->get();

        $count = 0;

        foreach ($invoices as $invoice) {
            if (! $invoice->client->email) {
                continue;
            }

            Mail::to($invoice->client->email)
                ->queue(new InvoiceOverdueReminder($invoice));

            InvoiceOverdue::dispatch($invoice);

            $count++;
        }

        $this->info("Sent {$count} overdue invoice reminders.");
    }
}
