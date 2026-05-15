<?php

namespace App\Http\Controllers\frontEnd\Roster;

use App\Http\Controllers\Controller;
use App\Models\AIDocumentImport;
use App\Services\AI\AIDocumentImportService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class AIDocumentImportController extends Controller
{
    private AIDocumentImportService $importService;

    public function __construct(AIDocumentImportService $importService)
    {
        $this->importService = $importService;
    }

    private function homeId(): int
    {
        return (int) explode(',', Auth::user()->home_id)[0];
    }

    public function upload(Request $request): JsonResponse
    {
        $request->validate([
            'client_id' => 'required|integer',
            'file' => 'required|file|mimes:pdf,docx,doc|max:10240',
        ]);

        $homeId = $this->homeId();
        $clientId = (int) $request->input('client_id');

        $client = DB::table('service_user')
            ->where('id', $clientId)
            ->where('home_id', $homeId)
            ->where('is_deleted', 0)
            ->first();

        if (!$client) {
            return response()->json(['status' => false, 'error' => 'Client not found.'], 404);
        }

        $file = $request->file('file');

        $mime = $file->getMimeType();
        $allowedMimes = [
            'application/pdf',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/msword',
        ];
        if (!in_array($mime, $allowedMimes)) {
            return response()->json(['status' => false, 'error' => 'Invalid file type. Only PDF and Word documents are accepted.'], 422);
        }

        $originalName = $file->getClientOriginalName();
        $ext = strtolower($file->getClientOriginalExtension()) ?: 'pdf';
        $ext = in_array($ext, ['pdf', 'docx', 'doc']) ? $ext : 'pdf';
        $hash = substr(md5($originalName . time()), 0, 8);
        $storedName = time() . '_' . $hash . '.' . $ext;
        $storedPath = 'imports/' . $homeId . '/' . $storedName;

        Storage::disk('local')->putFileAs('private/imports/' . $homeId, $file, $storedName);

        try {
            $text = $this->importService->extractTextFromFile($storedPath);
        } catch (RuntimeException $e) {
            Storage::disk('local')->delete('private/' . $storedPath);
            return response()->json(['status' => false, 'error' => $e->getMessage()], 422);
        }

        $import = AIDocumentImport::create([
            'home_id' => $homeId,
            'client_id' => $clientId,
            'uploaded_by' => Auth::id(),
            'original_filename' => substr($originalName, 0, 255),
            'stored_path' => $storedPath,
            'file_size' => $file->getSize(),
            'file_mime' => $mime,
            'extracted_text_length' => strlen($text),
            'import_status' => 'uploaded',
        ]);

        return response()->json([
            'status' => true,
            'import_id' => $import->id,
            'filename' => $originalName,
            'text_length' => strlen($text),
            'text_preview' => substr($text, 0, 500),
        ]);
    }

    public function extract(Request $request): JsonResponse
    {
        $request->validate([
            'import_id' => 'required|integer',
        ]);

        $homeId = $this->homeId();

        try {
            $result = $this->importService->extractDataWithAI(
                (int) $request->input('import_id'),
                $homeId,
                Auth::id()
            );

            return response()->json($result);
        } catch (RuntimeException $e) {
            return response()->json(['status' => false, 'error' => $e->getMessage()], 422);
        }
    }

    public function confirmImport(Request $request): JsonResponse
    {
        $request->validate([
            'import_id' => 'required|integer',
            'categories' => 'required|array',
            'categories.*' => 'string|in:care_history,medications,risk_assessments,client_profile,body_map,dols',
        ]);

        $homeId = $this->homeId();

        try {
            $result = $this->importService->importToDatabase(
                (int) $request->input('import_id'),
                $request->input('categories'),
                $homeId,
                Auth::id()
            );

            return response()->json($result);
        } catch (RuntimeException $e) {
            return response()->json(['status' => false, 'error' => $e->getMessage()], 422);
        }
    }

    public function list(Request $request): JsonResponse
    {
        $request->validate([
            'client_id' => 'required|integer',
        ]);

        $homeId = $this->homeId();
        $clientId = (int) $request->input('client_id');

        $imports = AIDocumentImport::forHome($homeId)
            ->forClient($clientId)
            ->notDeleted()
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($import) {
                return [
                    'id' => $import->id,
                    'filename' => $import->original_filename,
                    'status' => $import->import_status,
                    'file_size' => $import->file_size,
                    'imported_categories' => $import->imported_categories,
                    'import_summary' => $import->import_summary,
                    'ai_model' => $import->ai_model,
                    'created_at' => $import->created_at ? $import->created_at->format('d M Y H:i') : null,
                ];
            });

        return response()->json(['status' => true, 'imports' => $imports]);
    }

    public function documents(Request $request): JsonResponse
    {
        $request->validate([
            'client_id' => 'required|integer',
        ]);

        $homeId = $this->homeId();
        $clientId = (int) $request->input('client_id');

        $files = DB::table('su_file_manager')
            ->leftJoin('file_category', 'su_file_manager.category_id', '=', 'file_category.id')
            ->where('su_file_manager.home_id', $homeId)
            ->where('su_file_manager.service_user_id', $clientId)
            ->where('su_file_manager.is_deleted', 0)
            ->select(
                'su_file_manager.id',
                'su_file_manager.file',
                'su_file_manager.created_at',
                'file_category.name as category_name'
            )
            ->orderByDesc('su_file_manager.created_at')
            ->get();

        $imports = AIDocumentImport::forHome($homeId)
            ->forClient($clientId)
            ->notDeleted()
            ->where('import_status', 'completed')
            ->get()
            ->keyBy('stored_path');

        $documents = $files->map(function ($file) use ($imports) {
            $importData = $imports->get($file->file);
            return [
                'id' => $file->id,
                'filename' => basename($file->file),
                'category' => $file->category_name ?? 'Other',
                'created_at' => $file->created_at ? Carbon::parse($file->created_at)->format('d M Y') : null,
                'ai_import' => $importData ? [
                    'id' => $importData->id,
                    'summary' => $importData->import_summary,
                    'categories' => $importData->imported_categories,
                ] : null,
            ];
        });

        return response()->json(['status' => true, 'documents' => $documents]);
    }

    public function delete(Request $request): JsonResponse
    {
        $request->validate([
            'import_id' => 'required|integer',
        ]);

        $homeId = $this->homeId();

        $import = AIDocumentImport::where('id', (int) $request->input('import_id'))
            ->where('home_id', $homeId)
            ->where('is_deleted', 0)
            ->first();

        if (!$import) {
            return response()->json(['status' => false, 'error' => 'Import not found.'], 404);
        }

        $import->update(['is_deleted' => 1]);

        return response()->json(['status' => true, 'message' => 'Import deleted.']);
    }

    public function download(Request $request, int $id): mixed
    {
        $homeId = $this->homeId();

        $import = AIDocumentImport::where('id', $id)
            ->where('home_id', $homeId)
            ->where('is_deleted', 0)
            ->first();

        if (!$import) {
            abort(404, 'File not found.');
        }

        $fullPath = storage_path('app/private/' . $import->stored_path);

        if (!file_exists($fullPath)) {
            abort(404, 'File not found.');
        }

        return response()->download($fullPath, $import->original_filename, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $import->original_filename . '"',
        ]);
    }
}
