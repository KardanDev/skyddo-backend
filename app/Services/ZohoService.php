<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ZohoService
{
    private string $clientId;

    private string $clientSecret;

    // private string $refreshToken;
    private string $accountsUrl = 'https://accounts.zoho.com';

    private string $booksUrl = 'https://www.zohoapis.com/books/v3';

    private string $crmUrl = 'https://www.zohoapis.com/crm/v2';

    public function __construct()
    {
        $this->clientId = config('services.zoho.client_id');
        $this->clientSecret = config('services.zoho.client_secret');
        // $this->refreshToken = config('services.zoho.refresh_token');
    }

    private function getAccessToken(): ?string
    {
        return Cache::remember('zoho_access_token', 3500, function () {
            $response = Http::asForm()->post("{$this->accountsUrl}/oauth/v2/token", [
                // 'refresh_token' => $this->refreshToken,
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
                'grant_type' => 'client_credentials',
            ]);

            if ($response->successful()) {
                return $response->json('access_token');
            }
        });
    }

    private function request(string $method, string $url, array $data = []): ?array
    {
        $token = $this->getAccessToken();

        if (! $token) {
            return null;
        }

        $response = Http::withToken($token, 'Bearer')
            ->acceptJson()
            ->{$method}($url, $data);

        if ($response->successful()) {
            return $response->json();
        }

        Log::error('Zoho API request failed', [
            'url' => $url,
            'method' => $method,
            'response' => $response->json(),
        ]);

        return null;
    }

    // Books API - Contacts
    public function createContact(array $data): ?array
    {
        $organizationId = config('services.zoho.organization_id');

        return $this->request('post', "{$this->booksUrl}/contacts?organization_id={$organizationId}", $data);
    }

    public function updateContact(string $contactId, array $data): ?array
    {
        $organizationId = config('services.zoho.organization_id');

        return $this->request('put', "{$this->booksUrl}/contacts/{$contactId}?organization_id={$organizationId}", $data);
    }

    public function getContact(string $contactId): ?array
    {
        $organizationId = config('services.zoho.organization_id');

        return $this->request('get', "{$this->booksUrl}/contacts/{$contactId}?organization_id={$organizationId}");
    }

    // Books API - Invoices
    public function createInvoice(array $data): ?array
    {
        $organizationId = config('services.zoho.organization_id');

        return $this->request('post', "{$this->booksUrl}/invoices?organization_id={$organizationId}", $data);
    }

    public function updateInvoice(string $invoiceId, array $data): ?array
    {
        $organizationId = config('services.zoho.organization_id');

        return $this->request('put', "{$this->booksUrl}/invoices/{$invoiceId}?organization_id={$organizationId}", $data);
    }

    public function getInvoice(string $invoiceId): ?array
    {
        $organizationId = config('services.zoho.organization_id');

        return $this->request('get', "{$this->booksUrl}/invoices/{$invoiceId}?organization_id={$organizationId}");
    }

    public function sendInvoice(string $invoiceId): ?array
    {
        $organizationId = config('services.zoho.organization_id');

        return $this->request('post', "{$this->booksUrl}/invoices/{$invoiceId}/email?organization_id={$organizationId}");
    }

    // CRM API - Deals (for Quotes)
    public function createDeal(array $data): ?array
    {
        return $this->request('post', "{$this->crmUrl}/Deals", ['data' => [$data]]);
    }

    public function updateDeal(string $dealId, array $data): ?array
    {
        return $this->request('put', "{$this->crmUrl}/Deals/{$dealId}", ['data' => [$data]]);
    }

    public function getDeal(string $dealId): ?array
    {
        return $this->request('get', "{$this->crmUrl}/Deals/{$dealId}");
    }

    // CRM API - Contacts
    public function createCrmContact(array $data): ?array
    {
        return $this->request('post', "{$this->crmUrl}/Contacts", ['data' => [$data]]);
    }

    public function updateCrmContact(string $contactId, array $data): ?array
    {
        return $this->request('put', "{$this->crmUrl}/Contacts/{$contactId}", ['data' => [$data]]);
    }

    // CRM API - Tasks (for Claims)
    public function createTask(array $data): ?array
    {
        return $this->request('post', "{$this->crmUrl}/Tasks", ['data' => [$data]]);
    }

    public function updateTask(string $taskId, array $data): ?array
    {
        return $this->request('put', "{$this->crmUrl}/Tasks/{$taskId}", ['data' => [$data]]);
    }

    // Check if Zoho is configured
    public function isConfigured(): bool
    {
        $organizationId = config('services.zoho.organization_id');

        return ! empty($this->clientId) && ! empty($this->clientSecret) && ! empty($organizationId);
    }
}
