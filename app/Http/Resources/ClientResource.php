<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClientResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => $this->address,
            'id_number' => $this->id_number,
            'company_name' => $this->company_name,
            'zoho_contact_id' => $this->zoho_contact_id,
            'quotes_count' => $this->whenCounted('quotes'),
            'policies_count' => $this->whenCounted('policies'),
            'claims_count' => $this->whenCounted('claims'),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
