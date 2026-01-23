<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePolicyRequest;
use App\Http\Requests\UpdatePolicyRequest;
use App\Models\Client;
use App\Models\Insurer;
use App\Models\Policy;
use App\Services\CsvService;
use App\Services\PolicyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PolicyController extends Controller
{
    public function __construct(
        private PolicyService $policyService,
        private CsvService $csvService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $policies = Policy::with(['client', 'insurer'])
            ->when($request->status, fn ($q, $status) => $q->where('status', $status))
            ->when($request->client_id, fn ($q, $clientId) => $q->where('client_id', $clientId))
            ->when($request->insurer_id, fn ($q, $insurerId) => $q->where('insurer_id', $insurerId))
            ->latest()
            ->paginate(20);

        return response()->json($policies);
    }

    public function store(StorePolicyRequest $request): JsonResponse
    {
        $policy = $this->policyService->create($request->validated());

        return response()->json($policy, 201);
    }

    public function show(Policy $policy): JsonResponse
    {
        $policy->load(['client', 'insurer', 'claims', 'documents', 'invoices']);

        return response()->json($policy);
    }

    public function update(UpdatePolicyRequest $request, Policy $policy): JsonResponse
    {
        $policy = $this->policyService->update($policy, $request->validated());

        return response()->json($policy);
    }

    public function destroy(Policy $policy): JsonResponse
    {
        $policy->delete();

        return response()->json(null, 204);
    }

    public function expiring(Request $request): JsonResponse
    {
        $days = $request->input('days', 30);
        $policies = $this->policyService->getExpiringPolicies($days);

        return response()->json($policies);
    }

    public function renew(Policy $policy): JsonResponse
    {
        $newPolicy = $this->policyService->renew($policy);

        $policy->update(['status' => 'expired']);

        return response()->json($newPolicy, 201);
    }

    /**
     * Download CSV template for policies
     */
    public function downloadTemplate()
    {
        $headers = [
            'client_email',
            'insurer_name',
            'insurance_type',
            'policy_number',
            'sum_insured',
            'premium',
            'start_date',
            'end_date',
            'description',
        ];

        $exampleRow = [
            'john@example.com',
            'ABC Insurance',
            'motor',
            'POL-2024-001',
            '50000',
            '1200',
            '2024-01-01',
            '2024-12-31',
            'Comprehensive motor insurance policy',
        ];

        $path = $this->csvService->generateTemplate($headers, $exampleRow);

        return response()->download($path, 'policies_template.csv')->deleteFileAfterSend();
    }

    /**
     * Import policies from CSV
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
                'policy_number' => 'nullable|string|max:100',
                'sum_insured' => 'required|numeric|min:0',
                'premium' => 'required|numeric|min:0',
                'start_date' => 'required|date',
                'end_date' => 'required|date|after:start_date',
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
                    $policyData = [
                        'client_id' => $client->id,
                        'insurer_id' => $insurer->id,
                        'insurance_type' => $row['insurance_type'],
                        'policy_number' => $row['policy_number'] ?? null,
                        'sum_insured' => $row['sum_insured'],
                        'premium' => $row['premium'],
                        'start_date' => $row['start_date'],
                        'end_date' => $row['end_date'],
                        'description' => $row['description'] ?? null,
                    ];

                    $this->policyService->create($policyData);
                    $imported++;
                }
                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

            $response = [
                'message' => "Successfully imported {$imported} policies",
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
     * Export policies to CSV
     */
    public function export(): JsonResponse
    {
        $policies = Policy::with(['client', 'insurer'])->get();

        $data = $policies->map(function ($policy) {
            return [
                'client_email' => $policy->client->email ?? '',
                'client_name' => $policy->client->name ?? '',
                'insurer_name' => $policy->insurer->name ?? '',
                'insurance_type' => $policy->insurance_type,
                'policy_number' => $policy->policy_number ?? '',
                'sum_insured' => $policy->sum_insured,
                'premium' => $policy->premium,
                'start_date' => $policy->start_date,
                'end_date' => $policy->end_date,
                'description' => $policy->description ?? '',
                'status' => $policy->status,
                'created_at' => $policy->created_at->format('Y-m-d H:i:s'),
            ];
        })->toArray();

        $headers = [
            'client_email',
            'client_name',
            'insurer_name',
            'insurance_type',
            'policy_number',
            'sum_insured',
            'premium',
            'start_date',
            'end_date',
            'description',
            'status',
            'created_at',
        ];

        $filename = 'policies_export_'.now()->format('Y-m-d_His').'.csv';
        $path = $this->csvService->generateCsv($data, $headers, $filename);

        return response()->json([
            'message' => 'Export generated successfully',
            'download_url' => route('policies.download-export', ['filename' => $filename]),
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
