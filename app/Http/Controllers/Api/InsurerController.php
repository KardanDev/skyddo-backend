<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreInsurerRequest;
use App\Http\Requests\UpdateInsurerRequest;
use App\Models\Insurer;
use App\Services\CsvService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InsurerController extends Controller
{
    public function __construct(
        private CsvService $csvService
    ) {}

    public function index(): JsonResponse
    {
        $insurers = Insurer::withCount(['quotes', 'policies'])
            ->latest()
            ->paginate(20);

        return response()->json($insurers);
    }

    public function store(StoreInsurerRequest $request): JsonResponse
    {
        $insurer = Insurer::create($request->validated());

        return response()->json($insurer, 201);
    }

    public function show(Insurer $insurer): JsonResponse
    {
        $insurer->loadCount(['quotes', 'policies']);

        return response()->json($insurer);
    }

    public function update(UpdateInsurerRequest $request, Insurer $insurer): JsonResponse
    {
        $insurer->update($request->validated());

        return response()->json($insurer);
    }

    public function destroy(Insurer $insurer): JsonResponse
    {
        $insurer->delete();

        return response()->json(null, 204);
    }

    public function active(): JsonResponse
    {
        $insurers = Insurer::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        return response()->json($insurers);
    }

    /**
     * Download CSV template for insurers
     */
    public function downloadTemplate()
    {
        $headers = [
            'name',
            'email',
            'phone',
            'address',
            'contact_person',
            'is_active',
        ];

        $exampleRow = [
            'ABC Insurance',
            'info@abc.com',
            '+1234567890',
            '123 Insurance St',
            'John Manager',
            'true',
        ];

        $path = $this->csvService->generateTemplate($headers, $exampleRow);

        return response()->download($path, 'insurers_template.csv')->deleteFileAfterSend();
    }

    /**
     * Import insurers from CSV
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
                'email' => 'nullable|email|max:255',
                'phone' => 'nullable|string|max:20',
                'address' => 'nullable|string',
                'contact_person' => 'nullable|string|max:255',
                'is_active' => 'required|boolean',
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
                    Insurer::create($row);
                    $imported++;
                }
                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

            return response()->json([
                'message' => "Successfully imported {$imported} insurers",
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
     * Export insurers to CSV
     */
    public function export(): JsonResponse
    {
        $insurers = Insurer::all();

        $data = $insurers->map(function ($insurer) {
            return [
                'name' => $insurer->name,
                'email' => $insurer->email ?? '',
                'phone' => $insurer->phone ?? '',
                'address' => $insurer->address ?? '',
                'contact_person' => $insurer->contact_person ?? '',
                'is_active' => $insurer->is_active ? 'true' : 'false',
                'created_at' => $insurer->created_at->format('Y-m-d H:i:s'),
            ];
        })->toArray();

        $headers = [
            'name',
            'email',
            'phone',
            'address',
            'contact_person',
            'is_active',
            'created_at',
        ];

        $filename = 'insurers_export_'.now()->format('Y-m-d_His').'.csv';
        $path = $this->csvService->generateCsv($data, $headers, $filename);

        return response()->json([
            'message' => 'Export generated successfully',
            'download_url' => route('insurers.download-export', ['filename' => $filename]),
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

    /**
     * Get insurer with insurance types they offer
     */
    public function getInsuranceTypes(Insurer $insurer): JsonResponse
    {
        $insurer->load(['insuranceTypes' => function ($query) {
            $query->select(['insurance_types.id', 'insurance_types.name', 'insurance_types.slug'])
                ->withPivot('is_active', 'turnaround_days');
        }]);

        return response()->json([
            'insurer' => $insurer,
            'insurance_types' => $insurer->insuranceTypes,
        ]);
    }

    /**
     * Sync insurance types for an insurer
     */
    public function syncInsuranceTypes(Request $request, Insurer $insurer): JsonResponse
    {
        $validated = $request->validate([
            'insurance_types' => 'required|array',
            'insurance_types.*.insurance_type_id' => 'required|exists:insurance_types,id',
            'insurance_types.*.is_active' => 'boolean',
            'insurance_types.*.turnaround_days' => 'required|integer|min:1|max:365',
        ]);

        $syncData = [];
        foreach ($validated['insurance_types'] as $insuranceType) {
            $syncData[$insuranceType['insurance_type_id']] = [
                'is_active' => $insuranceType['is_active'] ?? true,
                'turnaround_days' => $insuranceType['turnaround_days'],
            ];
        }

        $insurer->insuranceTypes()->sync($syncData);

        return response()->json([
            'message' => 'Insurance types synced successfully',
            'insurer' => $insurer->load('insuranceTypes'),
        ]);
    }

    /**
     * Toggle insurer active status
     */
    public function toggleActive(Insurer $insurer): JsonResponse
    {
        $insurer->update([
            'is_active' => !$insurer->is_active,
        ]);

        return response()->json($insurer);
    }
}
