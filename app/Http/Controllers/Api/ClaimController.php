<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreClaimRequest;
use App\Http\Requests\UpdateClaimRequest;
use App\Models\Claim;
use App\Models\Client;
use App\Models\Policy;
use App\Services\ClaimService;
use App\Services\CsvService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ClaimController extends Controller
{
    public function __construct(
        private ClaimService $claimService,
        private CsvService $csvService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $claims = Claim::with(['client', 'policy.insurer'])
            ->when($request->status, fn ($q, $status) => $q->where('status', $status))
            ->when($request->client_id, fn ($q, $clientId) => $q->where('client_id', $clientId))
            ->when($request->policy_id, fn ($q, $policyId) => $q->where('policy_id', $policyId))
            ->latest()
            ->paginate(20);

        return response()->json($claims);
    }

    public function store(StoreClaimRequest $request): JsonResponse
    {
        $claim = $this->claimService->create($request->validated());

        return response()->json($claim, 201);
    }

    public function show(Claim $claim): JsonResponse
    {
        $claim->load(['client', 'policy.insurer', 'documents']);

        return response()->json($claim);
    }

    public function update(UpdateClaimRequest $request, Claim $claim): JsonResponse
    {
        $claim = $this->claimService->update($claim, $request->validated());

        return response()->json($claim);
    }

    public function destroy(Claim $claim): JsonResponse
    {
        $claim->delete();

        return response()->json(null, 204);
    }

    public function updateStatus(Request $request, Claim $claim): JsonResponse
    {
        $request->validate([
            'status' => ['required', 'in:submitted,under_review,docs_requested,forwarded,approved,rejected,settled'],
            'notes' => ['nullable', 'string'],
            'approved_amount' => ['nullable', 'numeric', 'min:0'],
        ]);

        if ($request->status === 'approved' && $request->approved_amount) {
            $claim = $this->claimService->approve($claim, $request->approved_amount);
        } else {
            $claim = $this->claimService->updateStatus($claim, $request->status, $request->notes);
        }

        return response()->json($claim);
    }

    public function forwardToInsurer(Claim $claim): JsonResponse
    {
        $claim = $this->claimService->forwardToInsurer($claim);

        return response()->json($claim);
    }

    /**
     * Download CSV template for claims
     */
    public function downloadTemplate()
    {
        $headers = [
            'client_email',
            'policy_number',
            'claim_amount',
            'incident_date',
            'description',
        ];

        $exampleRow = [
            'john@example.com',
            'POL-2024-001',
            '5000',
            '2024-06-15',
            'Vehicle accident claim - rear-end collision',
        ];

        $path = $this->csvService->generateTemplate($headers, $exampleRow);

        return response()->download($path, 'claims_template.csv')->deleteFileAfterSend();
    }

    /**
     * Import claims from CSV
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
                'policy_number' => 'required|string',
                'claim_amount' => 'required|numeric|min:0',
                'incident_date' => 'required|date',
                'description' => 'required|string',
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

                    // Lookup policy by policy_number
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

                    // Prepare data for creation
                    $claimData = [
                        'client_id' => $client->id,
                        'policy_id' => $policy->id,
                        'claim_amount' => $row['claim_amount'],
                        'incident_date' => $row['incident_date'],
                        'description' => $row['description'],
                    ];

                    $this->claimService->create($claimData);
                    $imported++;
                }
                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

            $response = [
                'message' => "Successfully imported {$imported} claims",
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
     * Export claims to CSV
     */
    public function export(): JsonResponse
    {
        $claims = Claim::with(['client', 'policy'])->get();

        $data = $claims->map(function ($claim) {
            return [
                'client_email' => $claim->client->email ?? '',
                'client_name' => $claim->client->name ?? '',
                'policy_number' => $claim->policy->policy_number ?? '',
                'claim_amount' => $claim->claim_amount,
                'approved_amount' => $claim->approved_amount ?? '',
                'incident_date' => $claim->incident_date,
                'description' => $claim->description ?? '',
                'status' => $claim->status,
                'created_at' => $claim->created_at->format('Y-m-d H:i:s'),
            ];
        })->toArray();

        $headers = [
            'client_email',
            'client_name',
            'policy_number',
            'claim_amount',
            'approved_amount',
            'incident_date',
            'description',
            'status',
            'created_at',
        ];

        $filename = 'claims_export_'.now()->format('Y-m-d_His').'.csv';
        $path = $this->csvService->generateCsv($data, $headers, $filename);

        return response()->json([
            'message' => 'Export generated successfully',
            'download_url' => route('claims.download-export', ['filename' => $filename]),
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
