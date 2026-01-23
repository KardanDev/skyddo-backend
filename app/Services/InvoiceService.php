<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\PaymentTransaction;
use Illuminate\Support\Facades\Auth;

class InvoiceService
{
    public function __construct(
        private ZohoService $zoho
    ) {}

    public function create(array $data): Invoice
    {
        $data['invoice_number'] = Invoice::generateInvoiceNumber();
        $data['status'] = 'draft';

        $invoice = Invoice::create($data);

        $this->syncToZoho($invoice);

        return $invoice->load(['client', 'policy']);
    }

    public function update(Invoice $invoice, array $data): Invoice
    {
        $invoice->update($data);

        // Auto-update status based on payment
        if (isset($data['paid_amount'])) {
            $this->updatePaymentStatus($invoice);
        }

        $this->syncToZoho($invoice);

        return $invoice->fresh(['client', 'policy']);
    }

    public function send(Invoice $invoice): Invoice
    {
        $invoice->update(['status' => 'sent']);

        if ($this->zoho->isConfigured() && $invoice->zoho_invoice_id) {
            $this->zoho->sendInvoice($invoice->zoho_invoice_id);
        }

        return $invoice;
    }

    public function recordPayment(Invoice $invoice, float $amount, array $paymentData = []): Invoice
    {
        // Create payment transaction record
        PaymentTransaction::create([
            'invoice_id' => $invoice->id,
            'transaction_number' => PaymentTransaction::generateTransactionNumber(),
            'amount' => $amount,
            'payment_date' => $paymentData['payment_date'] ?? now(),
            'payment_method' => $paymentData['payment_method'] ?? 'bank_transfer',
            'reference_number' => $paymentData['reference_number'] ?? null,
            'notes' => $paymentData['notes'] ?? null,
            'recorded_by' => Auth::id(),
        ]);

        $newPaidAmount = $invoice->paid_amount + $amount;

        return $this->update($invoice, [
            'paid_amount' => $newPaidAmount,
            'paid_at' => $newPaidAmount >= $invoice->amount ? now() : null,
        ]);
    }

    private function updatePaymentStatus(Invoice $invoice): void
    {
        if ($invoice->paid_amount >= $invoice->amount) {
            $invoice->update(['status' => 'paid', 'paid_at' => now()]);
        } elseif ($invoice->paid_amount > 0) {
            $invoice->update(['status' => 'partial']);
        }
    }

    public function markOverdue(): int
    {
        return Invoice::whereIn('status', ['sent', 'partial'])
            ->where('due_date', '<', now())
            ->update(['status' => 'overdue']);
    }

    private function syncToZoho(Invoice $invoice): void
    {
        if (! $this->zoho->isConfigured()) {
            return;
        }

        $client = $invoice->client;

        if (! $client->zoho_contact_id) {
            return;
        }

        $invoiceData = [
            'customer_id' => $client->zoho_contact_id,
            'invoice_number' => $invoice->invoice_number,
            'date' => $invoice->created_at->format('Y-m-d'),
            'due_date' => $invoice->due_date->format('Y-m-d'),
            'line_items' => [
                [
                    'name' => $invoice->policy ? "Policy Premium - {$invoice->policy->policy_number}" : 'Insurance Premium',
                    'quantity' => 1,
                    'rate' => $invoice->amount,
                ],
            ],
        ];

        if ($invoice->zoho_invoice_id) {
            $this->zoho->updateInvoice($invoice->zoho_invoice_id, $invoiceData);
        } else {
            $response = $this->zoho->createInvoice($invoiceData);
            if ($response && isset($response['invoice']['invoice_id'])) {
                $invoice->update(['zoho_invoice_id' => $response['invoice']['invoice_id']]);
            }
        }
    }
}
