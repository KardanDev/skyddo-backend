<?php

namespace App\Services;

use App\Models\Policy;
use App\Models\PolicyRenewal;
use App\Models\Quote;
use Illuminate\Support\Facades\Auth;

class PolicyService
{
    public function __construct(
        private ZohoService $zoho
    ) {}

    public function create(array $data): Policy
    {
        $data['policy_number'] = Policy::generatePolicyNumber();
        $data['status'] = 'active';

        $policy = Policy::create($data);

        // If created from a quote, update quote status
        if (isset($data['quote_id'])) {
            Quote::find($data['quote_id'])?->update(['status' => 'approved']);
        }

        // Sync to Zoho CRM
        $this->syncToZoho($policy);

        return $policy->load(['client', 'insurer']);
    }

    public function createFromQuote(Quote $quote): Policy
    {
        return $this->create([
            'client_id' => $quote->client_id,
            'insurer_id' => $quote->insurer_id,
            'quote_id' => $quote->id,
            'insurance_type' => $quote->insurance_type,
            'description' => $quote->description,
            'sum_insured' => $quote->sum_insured,
            'premium' => $quote->premium,
            'start_date' => now(),
            'end_date' => now()->addYear(),
        ]);
    }

    public function update(Policy $policy, array $data): Policy
    {
        $policy->update($data);

        // Sync to Zoho CRM
        $this->syncToZoho($policy);

        return $policy->fresh(['client', 'insurer']);
    }

    public function renew(Policy $policy): Policy
    {
        $newPolicy = $this->create([
            'client_id' => $policy->client_id,
            'insurer_id' => $policy->insurer_id,
            'insurance_type' => $policy->insurance_type,
            'description' => $policy->description,
            'sum_insured' => $policy->sum_insured,
            'premium' => $policy->premium,
            'start_date' => $policy->end_date->addDay(),
            'end_date' => $policy->end_date->addYear()->addDay(),
        ]);

        // Create renewal record
        PolicyRenewal::create([
            'original_policy_id' => $policy->id,
            'renewed_policy_id' => $newPolicy->id,
            'created_by' => Auth::id(),
        ]);

        // Update original policy status
        $policy->update(['status' => 'renewed']);

        return $newPolicy;
    }

    public function getExpiringPolicies(int $days = 30)
    {
        return Policy::where('status', 'active')
            ->whereBetween('end_date', [now(), now()->addDays($days)])
            ->with(['client', 'insurer'])
            ->get();
    }

    public function markExpired(): int
    {
        return Policy::where('status', 'active')
            ->where('end_date', '<', now())
            ->update(['status' => 'expired']);
    }

    private function syncToZoho(Policy $policy): void
    {
        if (! $this->zoho->isConfigured()) {
            return;
        }

        $dealData = [
            'Deal_Name' => "Policy #{$policy->policy_number}",
            'Amount' => (float) $policy->premium,
            'Stage' => $this->mapStatusToStage($policy->status),
            'Closing_Date' => $policy->end_date->format('Y-m-d'),
            'Description' => $policy->description ?? "Insurance policy for {$policy->client->name}",
            'Type' => $policy->insurance_type,
        ];

        try {
            if ($policy->zoho_id) {
                $this->zoho->updateDeal($policy->zoho_id, $dealData);
            } else {
                $response = $this->zoho->createDeal($dealData);
                if ($response && isset($response['data'][0]['details']['id'])) {
                    $policy->update(['zoho_id' => $response['data'][0]['details']['id']]);
                }
            }
        } catch (\Exception $e) {
            \Log::error('Failed to sync policy to Zoho', [
                'policy_id' => $policy->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function mapStatusToStage(string $status): string
    {
        return match ($status) {
            'active' => 'Closed Won',
            'pending_renewal' => 'Negotiation/Review',
            'expired' => 'Closed Lost',
            'cancelled' => 'Closed Lost',
            'renewed' => 'Closed Won',
            default => 'Qualification',
        };
    }
}
