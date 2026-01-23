<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ClaimController;
use App\Http\Controllers\Api\ClientController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\InsuranceTypeController;
use App\Http\Controllers\Api\InsurerController;
use App\Http\Controllers\Api\InvitationController;
use App\Http\Controllers\Api\InvoiceController;
use App\Http\Controllers\Api\PricingRuleController;
use App\Http\Controllers\Api\PasswordController;
use App\Http\Controllers\Api\PolicyController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\CompanySettingController;
use App\Http\Controllers\Api\QuoteCalculationController;
use App\Http\Controllers\Api\QuoteController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Health check for external services (chatbot, monitoring)
Route::get('/health', fn () => response()->json(['status' => 'ok', 'timestamp' => now()->toISOString()]));

// Public chatbot routes
Route::prefix('chatbot')->group(function () {
    Route::post('/start-session', [App\Http\Controllers\Api\ChatbotController::class, 'startSession']);
    Route::post('/send-message', [App\Http\Controllers\Api\ChatbotController::class, 'sendMessage']);
    Route::get('/history', [App\Http\Controllers\Api\ChatbotController::class, 'getHistory']);
});

// Public quote calculation routes (for chatbot/n8n integration)
Route::prefix('quote-calculator')->group(function () {
    Route::get('/insurance-types', [QuoteCalculationController::class, 'getInsuranceTypes']);
    Route::get('/vehicle-types', [QuoteCalculationController::class, 'getVehicleTypes']);
    Route::post('/calculate', [QuoteCalculationController::class, 'calculate']);
    Route::post('/quick-calculate', [QuoteCalculationController::class, 'quickCalculate']);
});

/*
|--------------------------------------------------------------------------
| Public Authentication Routes
|--------------------------------------------------------------------------
*/
Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/forgot-password', [PasswordController::class, 'forgot']);
    Route::post('/reset-password', [PasswordController::class, 'reset']);
});

// Public invitation verification and short link redirect
Route::get('/i/{shortCode}', [InvitationController::class, 'redirectShortCode']);
Route::get('/invitations/verify/{token}', [InvitationController::class, 'verify']);

/*
|--------------------------------------------------------------------------
| Protected Routes (requires authentication)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me', [AuthController::class, 'me']);

    // Current user
    Route::get('/user', fn (Request $request) => $request->user());

    // Profile management
    Route::get('/profile', [ProfileController::class, 'show']);
    Route::put('/profile', [ProfileController::class, 'update']);
    Route::post('/profile/photo', [ProfileController::class, 'uploadPhoto']);
    Route::delete('/profile/photo', [ProfileController::class, 'deletePhoto']);
    Route::put('/profile/password', [ProfileController::class, 'updatePassword']);

    // Company settings (admin+ only)
    Route::get('/company-settings', [CompanySettingController::class, 'show']);
    Route::put('/company-settings', [CompanySettingController::class, 'update']);
    Route::post('/company-settings/logo', [CompanySettingController::class, 'uploadLogo']);
    Route::delete('/company-settings/logo', [CompanySettingController::class, 'deleteLogo']);

    // Invitations (admin+ only)
    Route::apiResource('invitations', InvitationController::class)
        ->except(['show', 'update']);

    // Users management (admin+ only)
    Route::apiResource('users', UserController::class)
        ->except(['store']); // Users are created through invitations

    // Dashboard (admin+ only via Gate in controller)
    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::get('/dashboard/stats', [DashboardController::class, 'stats']);

    // Clients
    Route::apiResource('clients', ClientController::class);
    Route::get('/clients/{client}/quotes', [ClientController::class, 'quotes']);
    Route::get('/clients/{client}/policies', [ClientController::class, 'policies']);
    Route::get('/clients/{client}/claims', [ClientController::class, 'claims']);
    Route::get('/clients/{client}/invoices', [ClientController::class, 'invoices']);
    Route::get('/clients-csv-template', [ClientController::class, 'downloadTemplate'])->name('clients.template');
    Route::post('/clients-csv-import', [ClientController::class, 'import'])->name('clients.import');
    Route::get('/clients-csv-export', [ClientController::class, 'export'])->name('clients.export');
    Route::get('/clients-csv-download/{filename}', [ClientController::class, 'downloadExport'])->name('clients.download-export');

    // Insurers
    Route::apiResource('insurers', InsurerController::class);
    Route::get('/insurers-active', [InsurerController::class, 'active']);
    Route::get('/insurers/{insurer}/insurance-types', [InsurerController::class, 'getInsuranceTypes']);
    Route::post('/insurers/{insurer}/insurance-types', [InsurerController::class, 'syncInsuranceTypes']);
    Route::patch('/insurers/{insurer}/toggle-active', [InsurerController::class, 'toggleActive']);
    Route::get('/insurers-csv-template', [InsurerController::class, 'downloadTemplate'])->name('insurers.template');
    Route::post('/insurers-csv-import', [InsurerController::class, 'import'])->name('insurers.import');
    Route::get('/insurers-csv-export', [InsurerController::class, 'export'])->name('insurers.export');
    Route::get('/insurers-csv-download/{filename}', [InsurerController::class, 'downloadExport'])->name('insurers.download-export');

    // Pricing Rules
    Route::apiResource('pricing-rules', PricingRuleController::class);
    Route::patch('/pricing-rules/{pricingRule}/toggle-active', [PricingRuleController::class, 'toggleActive']);
    Route::post('/pricing-rules/{pricingRule}/duplicate', [PricingRuleController::class, 'duplicate']);

    // Insurance Types
    Route::get('/insurance-types', [InsuranceTypeController::class, 'index']);
    Route::get('/insurance-types/{insuranceType}/details', [InsuranceTypeController::class, 'showDetails']);

    // Quotes
    Route::apiResource('quotes', QuoteController::class);
    Route::post('/quotes/calculate-comparison', [QuoteController::class, 'calculateComparison']);
    Route::post('/quotes/{quote}/send-to-insurer', [QuoteController::class, 'sendToInsurer']);
    Route::post('/quotes/{quote}/approve', [QuoteController::class, 'approve']);
    Route::post('/quotes/{quote}/convert-to-policy', [QuoteController::class, 'convertToPolicy']);
    Route::get('/quotes-csv-template', [QuoteController::class, 'downloadTemplate'])->name('quotes.template');
    Route::post('/quotes-csv-import', [QuoteController::class, 'import'])->name('quotes.import');
    Route::get('/quotes-csv-export', [QuoteController::class, 'export'])->name('quotes.export');
    Route::get('/quotes-csv-download/{filename}', [QuoteController::class, 'downloadExport'])->name('quotes.download-export');

    // Policies
    Route::apiResource('policies', PolicyController::class);
    Route::get('/policies-expiring', [PolicyController::class, 'expiring']);
    Route::post('/policies/{policy}/renew', [PolicyController::class, 'renew']);
    Route::get('/policies-csv-template', [PolicyController::class, 'downloadTemplate'])->name('policies.template');
    Route::post('/policies-csv-import', [PolicyController::class, 'import'])->name('policies.import');
    Route::get('/policies-csv-export', [PolicyController::class, 'export'])->name('policies.export');
    Route::get('/policies-csv-download/{filename}', [PolicyController::class, 'downloadExport'])->name('policies.download-export');

    // Claims
    Route::apiResource('claims', ClaimController::class);
    Route::post('/claims/{claim}/status', [ClaimController::class, 'updateStatus']);
    Route::post('/claims/{claim}/forward', [ClaimController::class, 'forwardToInsurer']);
    Route::get('/claims-csv-template', [ClaimController::class, 'downloadTemplate'])->name('claims.template');
    Route::post('/claims-csv-import', [ClaimController::class, 'import'])->name('claims.import');
    Route::get('/claims-csv-export', [ClaimController::class, 'export'])->name('claims.export');
    Route::get('/claims-csv-download/{filename}', [ClaimController::class, 'downloadExport'])->name('claims.download-export');

    // Invoices
    Route::apiResource('invoices', InvoiceController::class);
    Route::get('/invoices-overdue', [InvoiceController::class, 'overdue']);
    Route::post('/invoices/{invoice}/send', [InvoiceController::class, 'send']);
    Route::post('/invoices/{invoice}/payment', [InvoiceController::class, 'recordPayment']);
    Route::get('/invoices-csv-template', [InvoiceController::class, 'downloadTemplate'])->name('invoices.template');
    Route::post('/invoices-csv-import', [InvoiceController::class, 'import'])->name('invoices.import');
    Route::get('/invoices-csv-export', [InvoiceController::class, 'export'])->name('invoices.export');
    Route::get('/invoices-csv-download/{filename}', [InvoiceController::class, 'downloadExport'])->name('invoices.download-export');

    // Documents
    Route::post('/documents', [App\Http\Controllers\Api\DocumentController::class, 'store']);
    Route::get('/documents/{document}', [App\Http\Controllers\Api\DocumentController::class, 'show']);
    Route::get('/documents/{document}/download', [App\Http\Controllers\Api\DocumentController::class, 'download']);
    Route::delete('/documents/{document}', [App\Http\Controllers\Api\DocumentController::class, 'destroy']);
});
