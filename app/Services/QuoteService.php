<?php

namespace App\Services;

use App\Mail\QuoteComparisonReady;
use App\Mail\QuoteForwardedToInsurer;
use App\Mail\QuoteReceivedForReview;
use App\Models\InsuranceType;
use App\Models\Insurer;
use App\Models\Quote;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class QuoteService
{
    public function __construct(
        private ZohoService $zoho,
        private QuoteCalculatorService $calculator,
        private ComplexityDetectorService $complexityDetector
    ) {}

    public function create(array $data): Quote
    {
        // Generate quote number
        $data['quote_number'] = Quote::generateQuoteNumber();
        $data['status'] = $data['status'] ?? 'pending';

        // Calculate rates from all insurers
        if (! isset($data['calculated_cost']) && isset($data['insurance_type_id']) && isset($data['asset_value'])) {
            try {
                $multiInsurerCalc = $this->calculator->calculateMultiInsurer(
                    $data['insurance_type_id'],
                    $data['asset_value'],
                    $data['vehicle_type_id'] ?? null,
                    $data['additional_details'] ?? []
                );

                // Store comparison data (all insurers)
                $data['comparison_data'] = $multiInsurerCalc;

                // Set calculated_cost to selected insurer or lowest insurer
                if (isset($data['insurer_id'])) {
                    $selected = collect($multiInsurerCalc['insurers'])
                        ->firstWhere('insurer_id', $data['insurer_id']);
                    $data['calculated_cost'] = $selected['calculated_cost'] ?? 0;
                } else {
                    // No specific insurer selected - use lowest
                    if (! empty($multiInsurerCalc['insurers'])) {
                        $data['calculated_cost'] = $multiInsurerCalc['insurers'][0]['calculated_cost'] ?? 0;
                        $data['insurer_id'] = $multiInsurerCalc['insurers'][0]['insurer_id'] ?? null;
                    }
                }
            } catch (\Exception $e) {
                Log::error('Multi-insurer calculation failed', [
                    'error' => $e->getMessage(),
                    'data' => $data,
                ]);
                // Continue without calculation
            }
        }

        // Detect complexity
        if (isset($data['insurance_type_id']) && isset($data['asset_value'])) {
            $insuranceType = InsuranceType::find($data['insurance_type_id']);

            $complexityAnalysis = $this->complexityDetector->analyze([
                'asset_value' => $data['asset_value'],
                'additional_details' => $data['additional_details'] ?? [],
                'insurance_type_slug' => $insuranceType?->slug,
            ]);

            $data['complexity_level'] = $complexityAnalysis['complexity_level'];
            $data['complexity_factors'] = $complexityAnalysis['complexity_factors'];
            $data['requires_agent_review'] = $complexityAnalysis['requires_agent_review'];
        }

        // Create quote
        $quote = Quote::create($data);

        // Sync to Zoho (deal creation)
        $this->syncToZoho($quote);

        // If complex, create Zoho task for agent
        if ($quote->requires_agent_review) {
            $this->createAgentTask($quote);
        }

        // Send notifications
        $this->sendNotifications($quote);

        return $quote->load(['client', 'insurer', 'insuranceType', 'vehicleType']);
    }

    public function update(Quote $quote, array $data): Quote
    {
        $quote->update($data);

        $this->syncToZoho($quote);

        return $quote->fresh(['client', 'insurer']);
    }

    public function sendToInsurer(Quote $quote): Quote
    {
        $quote->update(['status' => 'sent_to_insurer']);

        // Send email notification to insurer
        if ($quote->insurer && $quote->insurer->email) {
            Mail::to($quote->insurer->email)
                ->send(new QuoteForwardedToInsurer($quote));
        }

        return $quote;
    }

    public function approve(Quote $quote): Quote
    {
        $quote->update(['status' => 'approved']);

        return $quote;
    }

    private function syncToZoho(Quote $quote): void
    {
        if (! $this->zoho->isConfigured()) {
            return;
        }

        $dealData = [
            'Deal_Name' => "Quote #{$quote->quote_number}",
            'Amount' => $quote->calculated_cost ?? $quote->premium, // Use calculated_cost if available, fallback to premium
            'Stage' => $this->mapStatusToStage($quote->status),
            'Description' => $quote->description,
        ];

        if ($quote->zoho_quote_id) {
            $this->zoho->updateDeal($quote->zoho_quote_id, $dealData);
        } else {
            $response = $this->zoho->createDeal($dealData);
            if ($response && isset($response['data'][0]['details']['id'])) {
                $quote->update(['zoho_quote_id' => $response['data'][0]['details']['id']]);
            }
        }
    }

    private function mapStatusToStage(string $status): string
    {
        return match ($status) {
            'pending' => 'Qualification',
            'sent_to_insurer' => 'Needs Analysis',
            'received' => 'Proposal/Price Quote',
            'approved' => 'Closed Won',
            'rejected' => 'Closed Lost',
            'expired' => 'Closed Lost',
            default => 'Qualification',
        };
    }

    /**
     * Create a Zoho task for agent to review complex quote
     */
    private function createAgentTask(Quote $quote): void
    {
        if (! $this->zoho->isConfigured()) {
            return;
        }

        try {
            $this->zoho->createTask([
                'Subject' => "Review Complex Quote: {$quote->quote_number}",
                'Status' => 'Not Started',
                'Priority' => $quote->complexity_level === 'complex' ? 'High' : 'Normal',
                'Due_Date' => now()->addBusinessDays(1)->format('Y-m-d'),
                'Description' => "Quote #{$quote->quote_number} requires agent review.\n\n".
                    "Client: {$quote->client_name}\n".
                    "Insurance Type: {$quote->insuranceType->name}\n".
                    "Asset Value: ".number_format((float) $quote->asset_value, 2)." MZN\n".
                    "Complexity: {$quote->complexity_level}\n".
                    'Factors: '.implode(', ', array_keys($quote->complexity_factors ?? [])),
                'What_Id' => $quote->zoho_quote_id,
            ]);

            $quote->update(['agent_assigned_at' => now()]);
        } catch (\Exception $e) {
            Log::error('Failed to create Zoho task for quote', [
                'quote_id' => $quote->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Send email notifications to client and insurers
     */
    private function sendNotifications(Quote $quote): void
    {
        // Send to client
        $clientEmail = $quote->client_email ?? $quote->client?->email ?? null;

        if ($clientEmail) {
            try {
                if ($quote->requires_agent_review) {
                    Mail::to($clientEmail)->send(new QuoteReceivedForReview($quote));
                } else {
                    Mail::to($clientEmail)->send(new QuoteComparisonReady($quote));
                }
            } catch (\Exception $e) {
                Log::error('Failed to send email to client', [
                    'quote_id' => $quote->id,
                    'client_email' => $clientEmail,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // Send to insurers (only if straightforward and not requiring agent review)
        if (! $quote->requires_agent_review && $quote->comparison_data) {
            foreach ($quote->comparison_data['insurers'] ?? [] as $insurerData) {
                $insurer = Insurer::find($insurerData['insurer_id']);
                if ($insurer && $insurer->email) {
                    try {
                        Mail::to($insurer->email)->send(new QuoteForwardedToInsurer($quote));
                    } catch (\Exception $e) {
                        Log::error('Failed to send email to insurer', [
                            'insurer_id' => $insurer->id,
                            'quote_id' => $quote->id,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
            }
        }
    }
}
