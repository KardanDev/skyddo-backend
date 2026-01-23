<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'invoice_number' => $this->invoice_number,
            'client_id' => $this->client_id,
            'client' => new ClientResource($this->whenLoaded('client')),
            'policy_id' => $this->policy_id,
            'policy' => new PolicyResource($this->whenLoaded('policy')),
            'amount' => $this->amount,
            'paid_amount' => $this->paid_amount,
            'balance' => $this->balance,
            'due_date' => $this->due_date?->format('Y-m-d'),
            'paid_at' => $this->paid_at?->format('Y-m-d'),
            'status' => $this->status,
            'is_overdue' => $this->isOverdue(),
            'notes' => $this->notes,
            'zoho_invoice_id' => $this->zoho_invoice_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
