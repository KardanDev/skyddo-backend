<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreClientRequest;
use App\Http\Requests\UpdateClientRequest;
use App\Models\Client;
use App\Services\ClientService;
use App\Services\CsvService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ClientController extends Controller
{
    public function __construct(
        private ClientService $clientService,
        private CsvService $csvService
    ) {
        $this->authorizeResource(Client::class, 'client');
    }

    public function index(): JsonResponse
    {
        $user = auth()->user();
        $query = Client::with(['policies' => fn ($q) => $q->where('status', 'active')])
            ->withCount(['quotes', 'policies', 'claims']);

        // Scope query for members - only show assigned clients
        if ($user->isMember()) {
            $query->whereHas('users', fn ($q) => $q->where('user_id', $user->id));
        }

        $clients = $query->latest()->paginate(20);

        return response()->json($clients);
    }

    public function store(StoreClientRequest $request): JsonResponse
    {
        $client = $this->clientService->create($request->validated());

        return response()->json($client, 201);
    }

    public function show(Client $client): JsonResponse
    {
        $client->load([
            'quotes' => fn ($q) => $q->latest()->limit(5),
            'policies' => fn ($q) => $q->latest()->limit(5),
            'claims' => fn ($q) => $q->latest()->limit(5),
            'invoices' => fn ($q) => $q->latest()->limit(5),
        ]);

        return response()->json($client);
    }

    public function update(UpdateClientRequest $request, Client $client): JsonResponse
    {
        $client = $this->clientService->update($client, $request->validated());

        return response()->json($client);
    }

    public function destroy(Client $client): JsonResponse
    {
        $client->delete();

        return response()->json(null, 204);
    }

    public function quotes(Client $client): JsonResponse
    {
        return response()->json($client->quotes()->with('insurer')->latest()->paginate(20));
    }

    public function policies(Client $client): JsonResponse
    {
        return response()->json($client->policies()->with('insurer')->latest()->paginate(20));
    }

    public function claims(Client $client): JsonResponse
    {
        return response()->json($client->claims()->with('policy.insurer')->latest()->paginate(20));
    }

    public function invoices(Client $client): JsonResponse
    {
        return response()->json($client->invoices()->with('policy')->latest()->paginate(20));
    }

    /**
     * Download CSV template for clients
     */
    public function downloadTemplate()
    {
        $headers = [
            'name',
            'client_type',
            'email',
            'phone',
            'address',
            'id_number',
            'company_name',
        ];

        $exampleRow = [
            'John Doe',
            'individual',
            'john@example.com',
            '+1234567890',
            '123 Main St, City',
            'ID123456',
            '',
        ];

        $path = $this->csvService->generateTemplate($headers, $exampleRow);

        return response()->download($path, 'clients_template.csv')->deleteFileAfterSend();
    }

    /**
     * Import clients from CSV
     */
    public function import(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:10240', // 10MB max
        ]);

        try {
            $data = $this->csvService->parseCsv($request->file('file'));

            $rules = [
                'name' => 'required|string|max:255',
                'client_type' => 'required|in:individual,corporate',
                'email' => 'nullable|email|max:255',
                'phone' => 'nullable|string|max:20',
                'address' => 'nullable|string',
                'id_number' => 'nullable|string|max:100',
                'company_name' => 'nullable|string|max:255',
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
                    $this->clientService->create($row);
                    $imported++;
                }
                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

            return response()->json([
                'message' => "Successfully imported {$imported} clients",
                'imported' => $imported,
                'total_rows' => $validation['total_rows'],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to import CSV',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Export clients to CSV
     */
    public function export(): JsonResponse
    {
        $user = auth()->user();
        $query = Client::query();

        // Scope query for members
        if ($user->isMember()) {
            $query->whereHas('users', fn ($q) => $q->where('user_id', $user->id));
        }

        $clients = $query->get();

        $data = $clients->map(function ($client) {
            return [
                'name' => $client->name,
                'client_type' => $client->client_type,
                'email' => $client->email ?? '',
                'phone' => $client->phone ?? '',
                'address' => $client->address ?? '',
                'id_number' => $client->id_number ?? '',
                'company_name' => $client->company_name ?? '',
                'created_at' => $client->created_at->format('Y-m-d H:i:s'),
            ];
        })->toArray();

        $headers = [
            'name',
            'client_type',
            'email',
            'phone',
            'address',
            'id_number',
            'company_name',
            'created_at',
        ];

        $filename = 'clients_export_'.now()->format('Y-m-d_His').'.csv';
        $path = $this->csvService->generateCsv($data, $headers, $filename);

        return response()->json([
            'message' => 'Export generated successfully',
            'download_url' => route('clients.download-export', ['filename' => $filename]),
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
