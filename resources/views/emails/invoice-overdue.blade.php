<x-mail::message>
# Payment Reminder

Dear {{ $client->name }},

This is a reminder that invoice **{{ $invoice->invoice_number }}** is overdue.

**Invoice Details:**
- **Amount Due:** {{ number_format($balance, 2) }}
- **Original Amount:** {{ number_format($invoice->amount, 2) }}
- **Due Date:** {{ $invoice->due_date->format('d M Y') }}
- **Days Overdue:** {{ $invoice->due_date->diffInDays(now()) }}

Please arrange payment at your earliest convenience to avoid any service interruptions.

If you have already made this payment, please disregard this reminder.

Regards,<br>
{{ config('app.name') }}
</x-mail::message>

