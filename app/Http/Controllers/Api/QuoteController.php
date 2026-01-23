<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreQuoteRequest;
use App\Http\Requests\UpdateQuoteRequest;
use App\Models\Client;
use App\Models\Insurer;
use App\Models\Quote;
use App\Services\CsvService;
use App\Services\PolicyService;
use App\Services\QuoteCalculatorService;
use App\Services\QuoteService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QuoteController extends Controller
{
    public function __construct(
        private QuoteService $quoteService,
        private QuoteCalculatorService $calculator,
        private PolicyService $policyService,
        private CsvService $csvService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $quotes = Quote::with(['client', 'insurer', 'insuranceType', 'vehicleType'])
            ->when($request->status, fn ($q, $status) => $q->where('status', $status))
            ->when($request->client_id, fn ($q, $clientId) => $q->where('client_id', $clientId))
            ->latest()
            ->paginate(20);

        return response()->json($quotes);
    }

    public function store(StoreQuoteRequest $request): JsonResponse
    {
        $quote = $this->quoteService->create($request->validated());

        return response()->json($quote, 201);
    }

    public function show(Quote $quote): JsonResponse
    {
        $quote->load(['client', 'insurer', 'insuranceType', 'vehicleType', 'documents']);

        return response()->json($quote);
    }

    public function update(UpdateQuoteRequest $request, Quote $quote): JsonResponse
    {
        $quote = $this->quoteService->update($quote, $request->validated());

        return response()->json($quote);
    }

    public function destroy(Quote $quote): JsonResponse
    {
        $quote->delete();

        return response()->json(null, 204);
    }

    public function sendToInsurer(Quote $quote): JsonResponse
    {
        $quote = $this->quoteService->sendToInsurer($quote);

        return response()->json($quote);
    }

    public function approve(Quote $quote): JsonResponse
    {
        $quote = $this->quoteService->approve($quote);

        return response()->json($quote);
    }

    public function convertToPolicy(Quote $quote): JsonResponse
    {
        if ($quote->status !== 'approved') {
            return response()->json(['message' => 'Only approved quotes can be converted to policies'], 422);
        }

        $policy = $this->policyService->createFromQuote($quote);

        return response()->json($policy, 201);
    }

    /**
     * Calculate multi-insurer comparison before creating a quote
     */
    public function calculateComparison(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'insurance_type_id' => 'required|exists:insurance_types,id',
            'asset_value' => 'required|numeric|min:0',
            'vehicle_type_id' => 'nullable|exists:vehicle_types,id',
            'additional_details' => 'nullable|array',
        ]);

        try {
            $comparison = $this->calculator->calculateMultiInsurer(
                $validated['insurance_type_id'],
                $validated['asset_value'],
                $validated['vehicle_type_id'] ?? null,
                $validated['additional_details'] ?? []
            );

            return response()->json($comparison);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to calculate comparison',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Download CSV template for quotes
     */
    public function downloadTemplate()
    {
        $headers = [
            'client_email',
            'insurer_name',
            'insurance_type',
            'sum_insured',
            'premium',
            'description',
        ];

        $exampleRow = [
            'john@example.com',
            'ABC Insurance',
            'motor',
            '50000',
            '1200',
            'Comprehensive motor insurance quote',
        ];

        $path = $this->csvService->generateTemplate($headers, $exampleRow);

        return response()->download($path, 'quotes_template.csv')->deleteFileAfterSend();
    }

    /**
     * Import quotes from CSV
     */
    public function import(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:10240', // 10MB max
        ]);

        try {
            $data = $this->csvService->parseCsv($request->file('file'));

            $rules = [
                'client_email' => 'required|email|exists:clients,email',
                'insurer_name' => 'required|string',
                'insurance_type' => 'required|in:motor,property,life,health,travel,liability,marine',
                'sum_insured' => 'required|numeric|min:0',
                'premium' => 'required|numeric|min:0',
                'description' => 'nullable|string',
            ];

            $validation = $this->csvService->validateCsvData($data, $rules);

            if ($validation['error_rows'] > 0) {
                return response()->json([
                    'message' => 'Validation errors found in CSV',
                    'errors' => $validation['errors'],
                    'summary' => [
                        'total_rows' => $validation['total_rows'],
                        'valid_rows' => $validation['valid_rows'],
                        'error_rows' => $validation['error_rows'],
                    ],
                ], 422);
            }

            // Import valid data
            $imported = 0;
            $errors = [];

            DB::beginTransaction();
            try {
                foreach ($validation['valid_data'] as $row) {
                    // Lookup client by email
                    $client = Client::where('email', $row['client_email'])->first();
                    if (! $client) {
                        $errors[] = "Client with email {$row['client_email']} not found";

                        continue;
                    }

                    // Lookup insurer by name
                    $insurer = Insurer::where('name', $row['insurer_name'])->first();
                    if (! $insurer) {
                        $errors[] = "Insurer with name {$row['insurer_name']} not found";

                        continue;
                    }

                    // Prepare data for creation
                    $quoteData = [
                        'client_id' => $client->id,
                        'insurer_id' => $insurer->id,
                        'insurance_type' => $row['insurance_type'],
                        'sum_insured' => $row['sum_insured'],
                        'premium' => $row['premium'],
                        'description' => $row['description'] ?? null,
                    ];

                    $this->quoteService->create($quoteData);
                    $imported++;
                }
                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

            $response = [
                'message' => "Successfully imported {$imported} quotes",
                'imported' => $imported,
                'total_rows' => $validation['total_rows'],
            ];

            if (count($errors) > 0) {
                $response['warnings'] = $errors;
            }

            return response()->json($response);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to import CSV',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Export quotes to CSV
     */
    public function export(): JsonResponse
    {
        $quotes = Quote::with(['client', 'insurer', 'insuranceType', 'vehicleType'])->get();

        $data = $quotes->map(function ($quote) {
            return [
                'client_email' => $quote->client->email ?? '',
                'client_name' => $quote->client->name ?? '',
                'insurer_name' => $quote->insurer->name ?? '',
                'insurance_type' => $quote->insurance_type,
                'sum_insured' => $quote->sum_insured,
                'premium' => $quote->premium,
                'description' => $quote->description ?? '',
                'status' => $quote->status,
                'created_at' => $quote->created_at->format('Y-m-d H:i:s'),
            ];
        })->toArray();

        $headers = [
            'client_email',
            'client_name',
            'insurer_name',
            'insurance_type',
            'sum_insured',
            'premium',
            'description',
            'status',
            'created_at',
        ];

        $filename = 'quotes_export_'.now()->format('Y-m-d_His').'.csv';
        $path = $this->csvService->generateCsv($data, $headers, $filename);

        return response()->json([
            'message' => 'Export generated successfully',
            'download_url' => route('quotes.download-export', ['filename' => $filename]),
        ]);
    }

    /**
     * Download exported CSV file
     */
    public function downloadExport(string $filename)
    {
        $path = storage_path("app/exports/{$filename}");

        if (! file_exists($path)) {
            abort(404, 'Export file not found');
        }

        return response()->download($path)->deleteFileAfterSend();
    }
}
