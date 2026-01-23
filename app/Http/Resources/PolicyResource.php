<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PolicyResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'policy_number' => $this->policy_number,
            'client_id' => $this->client_id,
            'client' => new ClientResource($this->whenLoaded('client')),
            'insurer_id' => $this->insurer_id,
            'insurer' => $this->whenLoaded('insurer'),
            'quote_id' => $this->quote_id,
            'insurance_type' => $this->insurance_type,
            'description' => $this->description,
            'sum_insured' => $this->sum_insured,
            'premium' => $this->premium,
            'start_date' => $this->start_date?->format('Y-m-d'),
            'end_date' => $this->end_date?->format('Y-m-d'),
            'status' => $this->status,
            'days_until_expiry' => $this->end_date?->diffInDays(now(), false),
            'is_expiring_soon' => $this->isExpiringSoon(),
            'zoho_id' => $this->zoho_id,
            'documents' => $this->whenLoaded('documents'),
            'claims' => ClaimResource::collection($this->whenLoaded('claims')),
            'invoices' => InvoiceResource::collection($this->whenLoaded('invoices')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
