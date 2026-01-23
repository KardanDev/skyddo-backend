<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDocumentRequest;
use App\Models\Claim;
use App\Models\Document;
use App\Models\Policy;
use App\Models\Quote;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    public function store(StoreDocumentRequest $request): JsonResponse
    {
        $modelClass = match ($request->documentable_type) {
            'quote' => Quote::class,
            'policy' => Policy::class,
            'claim' => Claim::class,
        };

        $model = $modelClass::findOrFail($request->documentable_id);

        $file = $request->file('file');
        $path = $file->store("documents/{$request->documentable_type}s", 'local');

        $document = Document::create([
            'documentable_type' => $modelClass,
            'documentable_id' => $model->id,
            'name' => $request->name,
            'type' => $request->type,
            'file_path' => $path,
            'mime_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
            'uploaded_by' => auth()->id(),
        ]);

        return response()->json($document, 201);
    }

    public function show(Document $document): JsonResponse
    {
        return response()->json($document);
    }

    public function download(Document $document)
    {
        if (! Storage::disk('local')->exists($document->file_path)) {
            return response()->json(['message' => 'File not found'], 404);
        }

        return Storage::disk('local')->download($document->file_path, $document->name);
    }

    public function destroy(Document $document): JsonResponse
    {
        Storage::disk('local')->delete($document->file_path);
        $document->delete();

        return response()->json(null, 204);
    }
}
