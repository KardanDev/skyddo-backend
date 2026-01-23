<x-mail::message>
# Policy Renewal Reminder

Dear {{ $client->name }},

Your insurance policy **{{ $policy->policy_number }}** is expiring in **{{ $daysUntilExpiry }} days**.

**Policy Details:**
- **Type:** {{ $policy->insurance_type }}
- **Insurer:** {{ $policy->insurer->name }}
- **Expiry Date:** {{ $policy->end_date->format('d M Y') }}
- **Premium:** {{ number_format($policy->premium, 2) }}

To ensure continuous coverage, please contact us to renew your policy before the expiry date.

<x-mail::button :url="config('app.url')">
Contact Us
</x-mail::button>

Thank you for choosing Skyydo Insurance.

Regards,<br>
{{ config('app.name') }}
</x-mail::message>

