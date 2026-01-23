<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuoteResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'quote_number' => $this->quote_number,
            'client_id' => $this->client_id,
            'client' => new ClientResource($this->whenLoaded('client')),
            'insurer_id' => $this->insurer_id,
            'insurer' => $this->whenLoaded('insurer'),
            'insurance_type' => $this->insurance_type,
            'description' => $this->description,
            'sum_insured' => $this->sum_insured,
            'premium' => $this->premium,
            'status' => $this->status,
            'valid_until' => $this->valid_until?->format('Y-m-d'),
            'comparison_data' => $this->comparison_data,
            'zoho_quote_id' => $this->zoho_quote_id,
            'documents' => $this->whenLoaded('documents'),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
