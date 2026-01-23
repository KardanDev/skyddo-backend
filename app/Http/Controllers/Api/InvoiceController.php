<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreInvoiceRequest;
use App\Http\Requests\UpdateInvoiceRequest;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\Policy;
use App\Services\CsvService;
use App\Services\InvoiceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InvoiceController extends Controller
{
    public function __construct(
        private InvoiceService $invoiceService,
        private CsvService $csvService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $invoices = Invoice::with(['client', 'policy'])
            ->when($request->status, fn ($q, $status) => $q->where('status', $status))
            ->when($request->client_id, fn ($q, $clientId) => $q->where('client_id', $clientId))
            ->latest()
            ->paginate(20);

        return response()->json($invoices);
    }

    public function store(StoreInvoiceRequest $request): JsonResponse
    {
        $invoice = $this->invoiceService->create($request->validated());

        return response()->json($invoice, 201);
    }

    public function show(Invoice $invoice): JsonResponse
    {
        $invoice->load(['client', 'policy']);

        return response()->json($invoice);
    }

    public function update(UpdateInvoiceRequest $request, Invoice $invoice): JsonResponse
    {
        $invoice = $this->invoiceService->update($invoice, $request->validated());

        return response()->json($invoice);
    }

    public function destroy(Invoice $invoice): JsonResponse
    {
        $invoice->delete();

        return response()->json(null, 204);
    }

    public function send(Invoice $invoice): JsonResponse
    {
        $invoice = $this->invoiceService->send($invoice);

        return response()->json($invoice);
    }

    public function recordPayment(Request $request, Invoice $invoice): JsonResponse
    {
        $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
        ]);

        $invoice = $this->invoiceService->recordPayment($invoice, $request->amount);

        return response()->json($invoice);
    }

    public function overdue(): JsonResponse
    {
        $invoices = Invoice::with(['client', 'policy'])
            ->where('status', 'overdue')
            ->orWhere(fn ($q) => $q->whereIn('status', ['sent', 'partial'])->where('due_date', '<', now()))
            ->latest()
            ->paginate(20);

        return response()->json($invoices);
    }

    /**
     * Download CSV template for invoices
     */
    public function downloadTemplate()
    {
        $headers = [
            'client_email',
            'policy_number',
            'amount',
            'due_date',
            'description',
        ];

        $exampleRow = [
            'john@example.com',
            'POL-2024-001',
            '1200',
            '2024-12-31',
            'Annual premium payment',
        ];

        $path = $this->csvService->generateTemplate($headers, $exampleRow);

        return response()->download($path, 'invoices_template.csv')->deleteFileAfterSend();
    }

    /**
     * Import invoices from CSV
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
                'policy_number' => 'nullable|string',
                'amount' => 'required|numeric|min:0',
                'due_date' => 'required|date',
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

                    // Prepare data for creation
                    $invoiceData = [
                        'client_id' => $client->id,
                        'amount' => $row['amount'],
                        'due_date' => $row['due_date'],
                        'description' => $row['description'] ?? null,
                    ];

                    // Lookup policy by policy_number if provided
                    if (! empty($row['policy_number'])) {
                        $policy = Policy::where('policy_number', $row['policy_number'])->first();
                        if (! $policy) {
                            $errors[] = "Policy with number {$row['policy_number']} not found";

                            continue;
                        }

                        // Verify policy belongs to client
                        if ($policy->client_id !== $client->id) {
                            $errors[] = "Policy {$row['policy_number']} does not belong to client {$row['client_email']}";

                            continue;
                        }

                        $invoiceData['policy_id'] = $policy->id;
                    }

                    $this->invoiceService->create($invoiceData);
                    $imported++;
                }
                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

            $response = [
                'message' => "Successfully imported {$imported} invoices",
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
     * Export invoices to CSV
     */
    public function export(): JsonResponse
    {
        $invoices = Invoice::with(['client', 'policy'])->get();

        $data = $invoices->map(function ($invoice) {
            return [
                'client_email' => $invoice->client->email ?? '',
                'client_name' => $invoice->client->name ?? '',
                'policy_number' => $invoice->policy->policy_number ?? '',
                'amount' => $invoice->amount,
                'paid_amount' => $invoice->paid_amount ?? '0',
                'due_date' => $invoice->due_date,
                'description' => $invoice->description ?? '',
                'status' => $invoice->status,
                'created_at' => $invoice->created_at->format('Y-m-d H:i:s'),
            ];
        })->toArray();

        $headers = [
            'client_email',
            'client_name',
            'policy_number',
            'amount',
            'paid_amount',
            'due_date',
            'description',
            'status',
            'created_at',
        ];

        $filename = 'invoices_export_'.now()->format('Y-m-d_His').'.csv';
        $path = $this->csvService->generateCsv($data, $headers, $filename);

        return response()->json([
            'message' => 'Export generated successfully',
            'download_url' => route('invoices.download-export', ['filename' => $filename]),
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
