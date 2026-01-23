<x-mail::message>
# Claim Status Update

Dear {{ $client->name }},

Your claim **{{ $claim->claim_number }}** has been updated.

**Current Status:** {{ ucfirst(str_replace('_', ' ', $claim->status)) }}

**Claim Details:**
- **Policy:** {{ $policy->policy_number }}
- **Incident Date:** {{ $claim->incident_date->format('d M Y') }}
- **Claim Amount:** {{ $claim->claim_amount ? number_format($claim->claim_amount, 2) : 'Pending assessment' }}
@if($claim->approved_amount)
- **Approved Amount:** {{ number_format($claim->approved_amount, 2) }}
@endif

@if($claim->notes)
**Notes:**
{{ $claim->notes }}
@endif

If you have any questions about your claim, please contact us.

Regards,<br>
{{ config('app.name') }}
</x-mail::message>

