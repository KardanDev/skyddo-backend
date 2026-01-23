<?php

namespace App\Services;

use App\Models\Client;

class ClientService
{
    public function __construct(
        private ZohoService $zoho
    ) {}

    public function create(array $data): Client
    {
        $client = Client::create($data);

        $this->syncToZoho($client);

        return $client;
    }

    public function update(Client $client, array $data): Client
    {
        $client->update($data);

        $this->syncToZoho($client);

        return $client->fresh();
    }

    private function syncToZoho(Client $client): void
    {
        if (! $this->zoho->isConfigured()) {
            return;
        }

        $contactData = [
            'contact_name' => $client->name,
            'email' => $client->email,
            'phone' => $client->phone,
            'company_name' => $client->company_name,
            'billing_address' => [
                'address' => $client->address,
            ],
        ];

        if ($client->zoho_contact_id) {
            $this->zoho->updateContact($client->zoho_contact_id, $contactData);
        } else {
            $response = $this->zoho->createContact(['contact' => $contactData]);
            if ($response && isset($response['contact']['contact_id'])) {
                $client->update(['zoho_contact_id' => $response['contact']['contact_id']]);
            }
        }
    }
}
