<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Claim;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\Policy;
use App\Models\Quote;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

class DashboardController extends Controller
{
    public function index(): JsonResponse
    {
        Gate::authorize('access-dashboard');

        return response()->json([
            'stats' => $this->getStats(),
            'recent_quotes' => $this->getRecentQuotes(),
            'expiring_policies' => $this->getExpiringPolicies(),
            'pending_claims' => $this->getPendingClaims(),
            'overdue_invoices' => $this->getOverdueInvoices(),
        ]);
    }

    public function stats(): JsonResponse
    {
        Gate::authorize('access-dashboard');

        return response()->json($this->getStats());
    }

    private function getStats(): array
    {
        return [
            'total_clients' => Client::count(),
            'active_policies' => Policy::where('status', 'active')->count(),
            'pending_quotes' => Quote::whereIn('status', ['pending', 'sent_to_insurer'])->count(),
            'open_claims' => Claim::whereNotIn('status', ['settled', 'rejected'])->count(),
            'overdue_invoices' => Invoice::where('status', 'overdue')
                ->orWhere(fn ($q) => $q->whereIn('status', ['sent', 'partial'])->where('due_date', '<', now()))
                ->count(),
            'total_premium_this_month' => Policy::where('status', 'active')
                ->whereMonth('created_at', now()->month)
                ->sum('premium'),
            'policies_expiring_soon' => Policy::where('status', 'active')
                ->whereBetween('end_date', [now(), now()->addDays(30)])
                ->count(),
        ];
    }

    private function getRecentQuotes()
    {
        return Quote::with(['client:id,name', 'insurer:id,name'])
            ->latest()
            ->limit(5)
            ->get(['id', 'quote_number', 'client_id', 'insurer_id', 'insurance_type', 'status', 'premium', 'created_at']);
    }

    private function getExpiringPolicies()
    {
        return Policy::with(['client:id,name', 'insurer:id,name'])
            ->where('status', 'active')
            ->whereBetween('end_date', [now(), now()->addDays(30)])
            ->orderBy('end_date')
            ->limit(5)
            ->get(['id', 'policy_number', 'client_id', 'insurer_id', 'insurance_type', 'end_date', 'premium']);
    }

    private function getPendingClaims()
    {
        return Claim::with(['client:id,name', 'policy:id,policy_number,insurer_id', 'policy.insurer:id,name'])
            ->whereNotIn('status', ['settled', 'rejected'])
            ->latest()
            ->limit(5)
            ->get(['id', 'claim_number', 'client_id', 'policy_id', 'status', 'claim_amount', 'created_at']);
    }

    private function getOverdueInvoices()
    {
        return Invoice::with(['client:id,name'])
            ->where(fn ($q) => $q->where('status', 'overdue')
                ->orWhere(fn ($q) => $q->whereIn('status', ['sent', 'partial'])->where('due_date', '<', now())))
            ->orderBy('due_date')
            ->limit(5)
            ->get(['id', 'invoice_number', 'client_id', 'amount', 'paid_amount', 'due_date', 'status']);
    }
}
