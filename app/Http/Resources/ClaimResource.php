<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClaimResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'claim_number' => $this->claim_number,
            'client_id' => $this->client_id,
            'client' => new ClientResource($this->whenLoaded('client')),
            'policy_id' => $this->policy_id,
            'policy' => new PolicyResource($this->whenLoaded('policy')),
            'description' => $this->description,
            'incident_date' => $this->incident_date?->format('Y-m-d'),
            'claim_amount' => $this->claim_amount,
            'approved_amount' => $this->approved_amount,
            'status' => $this->status,
            'notes' => $this->notes,
            'zoho_id' => $this->zoho_id,
            'documents' => $this->whenLoaded('documents'),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
