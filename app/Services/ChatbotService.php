<?php

namespace App\Services;

use App\Models\ChatMessage;
use App\Models\Claim;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\Policy;
use App\Models\Quote;

class ChatbotService
{
    /**
     * Process incoming chat message and generate response
     */
    public function processMessage(string $sessionId, string $message, ?int $clientId = null): array
    {
        // Save client message
        ChatMessage::create([
            'session_id' => $sessionId,
            'client_id' => $clientId,
            'sender' => 'client',
            'message' => $message,
        ]);

        // Detect intent and generate response
        $intent = $this->detectIntent($message);
        $response = $this->generateResponse($intent, $message, $clientId);

        // Save bot response
        ChatMessage::create([
            'session_id' => $sessionId,
            'client_id' => $clientId,
            'sender' => 'bot',
            'message' => $response['message'],
            'metadata' => $response['metadata'] ?? null,
        ]);

        return $response;
    }

    /**
     * Get chat history for a session
     */
    public function getHistory(string $sessionId): array
    {
        return ChatMessage::where('session_id', $sessionId)
            ->orderBy('created_at', 'asc')
            ->get()
            ->toArray();
    }

    /**
     * Simple intent detection based on keywords
     */
    private function detectIntent(string $message): string
    {
        $message = strtolower($message);

        // Greetings
        if (preg_match('/\b(hi|hello|hey|good morning|good afternoon)\b/', $message)) {
            return 'greeting';
        }

        // Claim related
        if (preg_match('/\b(claim|file claim|submit claim|claim status)\b/', $message)) {
            if (preg_match('/\bstatus\b/', $message)) {
                return 'claim_status';
            }

            return 'file_claim';
        }

        // Policy related
        if (preg_match('/\b(policy|policies|insurance policy|my policy)\b/', $message)) {
            if (preg_match('/\b(renew|renewal)\b/', $message)) {
                return 'renew_policy';
            }

            return 'view_policies';
        }

        // Quote related
        if (preg_match('/\b(quote|get quote|insurance quote|quotation)\b/', $message)) {
            return 'request_quote';
        }

        // Invoice related
        if (preg_match('/\b(invoice|bill|payment|pay)\b/', $message)) {
            return 'view_invoices';
        }

        // Documents
        if (preg_match('/\b(document|upload|file|attach)\b/', $message)) {
            return 'upload_document';
        }

        // Help
        if (preg_match('/\b(help|what can you do|assist)\b/', $message)) {
            return 'help';
        }

        return 'unknown';
    }

    /**
     * Generate response based on intent
     */
    private function generateResponse(string $intent, string $message, ?int $clientId): array
    {
        return match ($intent) {
            'greeting' => $this->handleGreeting(),
            'file_claim' => $this->handleFileClaim($clientId),
            'claim_status' => $this->handleClaimStatus($clientId),
            'view_policies' => $this->handleViewPolicies($clientId),
            'renew_policy' => $this->handleRenewPolicy($clientId),
            'request_quote' => $this->handleRequestQuote($clientId),
            'view_invoices' => $this->handleViewInvoices($clientId),
            'upload_document' => $this->handleUploadDocument(),
            'help' => $this->handleHelp(),
            default => $this->handleUnknown(),
        };
    }

    private function handleGreeting(): array
    {
        return [
            'message' => "Hello! 👋 Welcome to Skyydo Insurance. I'm here to help you with your insurance needs. You can:\n\n• File a claim\n• Check claim status\n• View your policies\n• Request a quote\n• Check invoices\n• Upload documents\n\nHow can I assist you today?",
            'intent' => 'greeting',
        ];
    }

    private function handleFileClaim(?int $clientId): array
    {
        if (! $clientId) {
            return [
                'message' => 'To file a claim, please provide your client ID or contact information so I can assist you properly.',
                'intent' => 'file_claim',
                'requiresAuth' => true,
            ];
        }

        return [
            'message' => "I can help you file a claim. To proceed, I'll need the following information:\n\n1. Policy number\n2. Date of incident\n3. Description of what happened\n4. Estimated claim amount\n5. Any supporting documents\n\nPlease provide these details, or I can direct you to our claims form.",
            'intent' => 'file_claim',
            'action' => 'show_claim_form',
        ];
    }

    private function handleClaimStatus(?int $clientId): array
    {
        if (! $clientId) {
            return [
                'message' => 'To check your claim status, please provide your client ID.',
                'intent' => 'claim_status',
                'requiresAuth' => true,
            ];
        }

        $client = Client::find($clientId);
        if (! $client) {
            return [
                'message' => 'I couldn\'t find your client record. Please verify your client ID.',
                'intent' => 'claim_status',
            ];
        }

        $claims = $client->claims()->with('policy')->latest()->limit(5)->get();

        if ($claims->isEmpty()) {
            return [
                'message' => "You don't have any claims on file. Would you like to file a new claim?",
                'intent' => 'claim_status',
                'data' => ['claims' => []],
            ];
        }

        $claimsList = $claims->map(function ($claim) {
            return "• Claim #{$claim->claim_number} - Policy #{$claim->policy->policy_number}\n  Status: ".ucfirst($claim->status)."\n  Amount: $".number_format($claim->claim_amount, 2);
        })->join("\n\n");

        return [
            'message' => "Here are your recent claims:\n\n{$claimsList}\n\nWould you like more details about any specific claim?",
            'intent' => 'claim_status',
            'data' => ['claims' => $claims->toArray()],
        ];
    }

    private function handleViewPolicies(?int $clientId): array
    {
        if (! $clientId) {
            return [
                'message' => 'To view your policies, please provide your client ID.',
                'intent' => 'view_policies',
                'requiresAuth' => true,
            ];
        }

        $client = Client::find($clientId);
        if (! $client) {
            return [
                'message' => 'I couldn\'t find your client record. Please verify your client ID.',
                'intent' => 'view_policies',
            ];
        }

        $policies = $client->policies()->with('insurer')->where('status', 'active')->get();

        if ($policies->isEmpty()) {
            return [
                'message' => "You don't have any active policies. Would you like to request a quote for insurance coverage?",
                'intent' => 'view_policies',
                'data' => ['policies' => []],
            ];
        }

        $policiesList = $policies->map(function ($policy) {
            return "• Policy #{$policy->policy_number}\n  Type: {$policy->insurance_type}\n  Insurer: {$policy->insurer->name}\n  Premium: $".number_format($policy->premium, 2)."\n  Valid until: ".$policy->end_date->format('M d, Y');
        })->join("\n\n");

        return [
            'message' => "Here are your active policies:\n\n{$policiesList}\n\nWould you like details about any specific policy?",
            'intent' => 'view_policies',
            'data' => ['policies' => $policies->toArray()],
        ];
    }

    private function handleRenewPolicy(?int $clientId): array
    {
        if (! $clientId) {
            return [
                'message' => 'To renew your policy, please provide your client ID.',
                'intent' => 'renew_policy',
                'requiresAuth' => true,
            ];
        }

        return [
            'message' => 'I can help you renew your policy. Please provide your policy number, and I\'ll check the renewal details for you.',
            'intent' => 'renew_policy',
            'action' => 'show_policy_renewal',
        ];
    }

    private function handleRequestQuote(?int $clientId): array
    {
        return [
            'message' => "I'd be happy to help you get a quote! To provide you with an accurate quote, I'll need:\n\n1. Type of insurance (e.g., Auto, Home, Life, Health)\n2. Coverage amount needed\n3. Any specific requirements\n\nPlease provide these details, or I can connect you with one of our agents for a personalized quote.",
            'intent' => 'request_quote',
            'action' => 'show_quote_form',
        ];
    }

    private function handleViewInvoices(?int $clientId): array
    {
        if (! $clientId) {
            return [
                'message' => 'To view your invoices, please provide your client ID.',
                'intent' => 'view_invoices',
                'requiresAuth' => true,
            ];
        }

        $client = Client::find($clientId);
        if (! $client) {
            return [
                'message' => 'I couldn\'t find your client record. Please verify your client ID.',
                'intent' => 'view_invoices',
            ];
        }

        $invoices = $client->invoices()->latest()->limit(5)->get();

        if ($invoices->isEmpty()) {
            return [
                'message' => 'You don\'t have any invoices on file.',
                'intent' => 'view_invoices',
                'data' => ['invoices' => []],
            ];
        }

        $invoicesList = $invoices->map(function ($invoice) {
            return "• Invoice #{$invoice->invoice_number}\n  Amount: $".number_format($invoice->amount, 2)."\n  Paid: $".number_format($invoice->paid_amount, 2)."\n  Status: ".ucfirst($invoice->status)."\n  Due: ".$invoice->due_date->format('M d, Y');
        })->join("\n\n");

        return [
            'message' => "Here are your recent invoices:\n\n{$invoicesList}\n\nWould you like to make a payment?",
            'intent' => 'view_invoices',
            'data' => ['invoices' => $invoices->toArray()],
        ];
    }

    private function handleUploadDocument(): array
    {
        return [
            'message' => 'I can help you upload documents. Please use the upload button below to attach files related to your claim, policy, or quote.',
            'intent' => 'upload_document',
            'action' => 'show_document_upload',
        ];
    }

    private function handleHelp(): array
    {
        return [
            'message' => "I'm your Skyydo Insurance assistant! Here's what I can help you with:\n\n📋 **Claims**\n• File a new claim\n• Check claim status\n\n📄 **Policies**\n• View your active policies\n• Renew a policy\n\n💰 **Quotes & Invoices**\n• Request insurance quotes\n• View and pay invoices\n\n📎 **Documents**\n• Upload supporting documents\n\nJust tell me what you need, and I'll guide you through the process!",
            'intent' => 'help',
        ];
    }

    private function handleUnknown(): array
    {
        return [
            'message' => "I'm not sure I understand. I can help you with:\n\n• Filing or checking claims\n• Viewing your policies\n• Requesting quotes\n• Checking invoices\n• Uploading documents\n\nCould you please rephrase your question or choose one of these options?",
            'intent' => 'unknown',
        ];
    }
}
