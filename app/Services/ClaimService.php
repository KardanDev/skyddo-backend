<?php

namespace App\Services;

use App\Models\Claim;

class ClaimService
{
    public function __construct(
        private ZohoService $zoho
    ) {}

    public function create(array $data): Claim
    {
        $data['claim_number'] = Claim::generateClaimNumber();
        $data['status'] = 'submitted';

        $claim = Claim::create($data);

        // Sync to Zoho CRM
        $this->syncToZoho($claim);

        return $claim->load(['client', 'policy.insurer']);
    }

    public function update(Claim $claim, array $data): Claim
    {
        $claim->update($data);

        // Sync to Zoho CRM
        $this->syncToZoho($claim);

        return $claim->fresh(['client', 'policy.insurer']);
    }

    public function updateStatus(Claim $claim, string $status, ?string $notes = null): Claim
    {
        $updateData = ['status' => $status];

        if ($notes) {
            $updateData['notes'] = $claim->notes
                ? $claim->notes."\n\n".now()->format('Y-m-d H:i').': '.$notes
                : now()->format('Y-m-d H:i').': '.$notes;
        }

        return $this->update($claim, $updateData);
    }

    public function forwardToInsurer(Claim $claim): Claim
    {
        return $this->updateStatus($claim, 'forwarded', 'Forwarded to insurer for processing');
    }

    public function approve(Claim $claim, float $approvedAmount): Claim
    {
        return $this->update($claim, [
            'status' => 'approved',
            'approved_amount' => $approvedAmount,
        ]);
    }

    public function settle(Claim $claim): Claim
    {
        return $this->updateStatus($claim, 'settled', 'Claim has been settled');
    }

    private function syncToZoho(Claim $claim): void
    {
        if (! $this->zoho->isConfigured()) {
            return;
        }

        $taskData = [
            'Subject' => "Claim #{$claim->claim_number}",
            'Status' => $this->mapStatusToTaskStatus($claim->status),
            'Description' => $claim->description ?? "Insurance claim for policy {$claim->policy->policy_number}",
            'Due_Date' => now()->addDays(7)->format('Y-m-d'),
            'Priority' => 'High',
        ];

        // Link to policy deal if zoho_id exists
        if ($claim->policy->zoho_id) {
            $taskData['What_Id'] = $claim->policy->zoho_id;
        }

        try {
            if ($claim->zoho_id) {
                $this->zoho->updateTask($claim->zoho_id, $taskData);
            } else {
                $response = $this->zoho->createTask($taskData);
                if ($response && isset($response['data'][0]['details']['id'])) {
                    $claim->update(['zoho_id' => $response['data'][0]['details']['id']]);
                }
            }
        } catch (\Exception $e) {
            \Log::error('Failed to sync claim to Zoho', [
                'claim_id' => $claim->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function mapStatusToTaskStatus(string $status): string
    {
        return match ($status) {
            'submitted' => 'Not Started',
            'under_review' => 'In Progress',
            'docs_requested' => 'Waiting for Input',
            'forwarded' => 'In Progress',
            'approved' => 'Completed',
            'rejected' => 'Completed',
            'settled' => 'Completed',
            default => 'Not Started',
        };
    }
}
