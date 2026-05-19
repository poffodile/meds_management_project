<?php

namespace App\Http\Controllers\frontEnd\Roster;

use App\Http\Controllers\Controller;
use App\Models\AIDocumentImport;
use App\Services\AI\AINewClientImportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class AINewClientImportController extends Controller
{
    private AINewClientImportService $service;

    public function __construct(AINewClientImportService $service)
    {
        $this->service = $service;
    }

    private function homeId(): int
    {
        return (int) explode(',', Auth::user()->home_id)[0];
    }

    public function upload(Request $request): JsonResponse
    {
        $request->validate([
            'files' => 'required|array|min:1|max:10',
            'files.*' => 'required|file|max:10240',
        ]);

        $homeId = $this->homeId();
        $userId = Auth::id();

        $storedPaths = [];
        $filenames = [];
        $totalSize = 0;

        $uploadDir = "imports/{$homeId}";

        foreach ($request->file('files') as $file) {
            $mime = $file->getMimeType();
            $originalName = $file->getClientOriginalName();
            $ext = strtolower($file->getClientOriginalExtension());
            if (empty($ext)) {
                $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
            }

            $allowedMimes = [
                'application/pdf',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/msword',
                'application/octet-stream',
                'application/zip',
                'application/x-zip-compressed'
            ];
            $allowedExts = ['pdf', 'docx', 'doc'];

            if (!in_array($mime, $allowedMimes) && !in_array($ext, $allowedExts)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Invalid file type: ' . $file->getClientOriginalName() . '. Only PDF and Word documents are allowed.',
                ], 422);
            }

            $safeName = time() . '_' . bin2hex(random_bytes(8)) . '.' . ($ext ?: 'pdf');
            Storage::disk('local')->putFileAs('private/' . $uploadDir, $file, $safeName);

            $storedPaths[] = $uploadDir . '/' . $safeName;
            $filenames[] = substr($file->getClientOriginalName(), 0, 255);
            $totalSize += $file->getSize();
        }

        $import = AIDocumentImport::create([
            'home_id' => $homeId,
            'client_id' => null,
            'import_type' => 'new_client',
            'uploaded_by' => $userId,
            'original_filename' => json_encode($filenames),
            'stored_path' => json_encode($storedPaths),
            'file_size' => $totalSize,
            'file_mime' => 'multiple',
            'import_status' => 'uploaded',
            'is_deleted' => 0,
        ]);

        return response()->json([
            'status' => true,
            'import_id' => $import->id,
            'files_count' => count($storedPaths),
            'filenames' => $filenames,
            'total_size' => $totalSize,
        ]);
    }

    public function extract(Request $request): JsonResponse
    {
        $request->validate([
            'import_id' => 'required|integer',
        ]);

        $homeId = $this->homeId();
        $userId = Auth::id();

        try {
            $result = $this->service->extractDataWithAI(
                (int) $request->import_id,
                $homeId,
                $userId
            );

            $duplicate = null;
            if (!empty($result['extracted_data']['client']['full_name'])) {
                $duplicate = $this->service->checkDuplicateClient(
                    $result['extracted_data']['client']['full_name'],
                    $result['extracted_data']['client']['date_of_birth'] ?? null,
                    $homeId
                );
            }

            return response()->json([
                'status' => true,
                'extracted_data' => $result['extracted_data'],
                'tokens_used' => $result['tokens_used'],
                'duplicate_warning' => $duplicate,
            ]);

        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function confirm(Request $request): JsonResponse
    {
        $request->validate([
            'import_id' => 'required|integer',
            'selected_categories' => 'present|array',
            'selected_categories.*' => 'string|in:care_history,medications,risk_assessments,body_map,dols',
        ]);

        $homeId = $this->homeId();
        $userId = Auth::id();

        try {
            $result = $this->service->createClientAndImport(
                (int) $request->import_id,
                $request->selected_categories ?? [],
                $homeId,
                $userId
            );

            return response()->json([
                'status' => true,
                'client_id' => $result['client_id'],
                'client_name' => $result['client_name'],
                'summary' => $result['summary'],
                'redirect_url' => url('/roster/client-details/' . $result['client_id']),
            ]);

        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
