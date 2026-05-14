<?php

namespace App\Http\Controllers\frontEnd\Roster;

use App\Http\Controllers\Controller;
use App\Models\FormTemplate;
use App\Models\FormSubmission;
use App\Services\AI\FormBuilderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class RosterFormBuilderController extends Controller
{
    private FormBuilderService $formService;

    public function __construct(FormBuilderService $formService)
    {
        $this->formService = $formService;
    }

    private function homeId(): int
    {
        return (int) explode(',', Auth::user()->home_id)[0];
    }

    public function index()
    {
        $homeId = $this->homeId();

        $templates = FormTemplate::forHome($homeId)->notDeleted()
            ->orderBy('created_at', 'desc')
            ->get();

        $clients = DB::table('service_user')
            ->where('home_id', $homeId)
            ->where('is_deleted', 0)
            ->orderBy('name')
            ->get(['id', 'name']);

        $templatesJson = $this->buildTemplatesJson($templates);

        return view('frontEnd.roster.form-builder.index', compact('templates', 'clients', 'templatesJson'));
    }

    public function uploadAndGenerate(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:pdf,docx,doc|max:10240',
        ]);

        $homeId = $this->homeId();
        $userId = Auth::user()->id;

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
        $storedPath = 'form-uploads/' . $homeId . '/' . $storedName;

        Storage::disk('local')->putFileAs('private/form-uploads/' . $homeId, $file, $storedName);

        try {
            $result = $this->formService->generateTemplateFromDocument($storedPath, $originalName, $homeId, $userId);
        } catch (\Exception $e) {
            Storage::disk('local')->delete('private/' . $storedPath);
            $msg = $e instanceof RuntimeException ? $e->getMessage() : 'Failed to process document.';
            return response()->json(['status' => false, 'error' => $msg], 422);
        }

        Storage::disk('local')->delete('private/' . $storedPath);

        $template = FormTemplate::find($result['template_id']);

        return response()->json([
            'status' => true,
            'template' => [
                'id' => $template->id,
                'title' => $template->title,
                'description' => $template->description,
                'ai_generated' => $template->ai_generated,
                'form_json' => $result['form_json'],
                'created_at' => $template->created_at ? $template->created_at->format('d M Y') : date('d M Y'),
            ],
        ]);
    }

    public function storeTemplate(Request $request): JsonResponse
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'form_json' => 'required|string|max:500000',
        ]);

        $homeId = $this->homeId();

        $formJson = json_decode($request->input('form_json'), true);
        if (!$formJson || !$this->formService->validateFormJson($formJson)) {
            return response()->json(['status' => false, 'error' => 'Invalid form structure.'], 422);
        }

        $template = FormTemplate::create([
            'home_id' => $homeId,
            'title' => $request->input('title'),
            'description' => $request->input('description'),
            'form_json' => json_encode($formJson),
            'status' => 'published',
            'ai_generated' => 0,
            'created_by' => Auth::user()->id,
        ]);

        return response()->json([
            'status' => true,
            'template' => [
                'id' => $template->id,
                'title' => $template->title,
                'description' => $template->description,
                'ai_generated' => false,
                'created_at' => $template->created_at ? $template->created_at->format('d M Y') : date('d M Y'),
            ],
        ]);
    }

    public function updateTemplate(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'form_json' => 'required|string|max:500000',
        ]);

        $homeId = $this->homeId();

        $template = FormTemplate::where('id', $id)->where('home_id', $homeId)->where('is_deleted', 0)->first();
        if (!$template) {
            return response()->json(['status' => false, 'error' => 'Template not found.'], 404);
        }

        $formJson = json_decode($request->input('form_json'), true);
        if (!$formJson || !$this->formService->validateFormJson($formJson)) {
            return response()->json(['status' => false, 'error' => 'Invalid form structure.'], 422);
        }

        $template->update([
            'title' => $request->input('title'),
            'description' => $request->input('description'),
            'form_json' => json_encode($formJson),
        ]);

        return response()->json(['status' => true]);
    }

    public function deleteTemplate(Request $request, int $id): JsonResponse
    {
        $homeId = $this->homeId();

        $template = FormTemplate::where('id', $id)->where('home_id', $homeId)->where('is_deleted', 0)->first();
        if (!$template) {
            return response()->json(['status' => false, 'error' => 'Template not found.'], 404);
        }

        $template->update(['is_deleted' => 1]);

        return response()->json(['status' => true]);
    }

    public function getTemplate(int $id): JsonResponse
    {
        $homeId = $this->homeId();

        $template = FormTemplate::where('id', $id)->where('home_id', $homeId)->where('is_deleted', 0)->first();
        if (!$template) {
            return response()->json(['status' => false, 'error' => 'Template not found.'], 404);
        }

        return response()->json([
            'status' => true,
            'template' => [
                'id' => $template->id,
                'title' => $template->title,
                'description' => $template->description,
                'form_json' => json_decode($template->form_json, true),
                'ai_generated' => $template->ai_generated,
                'source_filename' => $template->source_filename,
                'created_at' => $template->created_at ? $template->created_at->format('d M Y') : '',
            ],
        ]);
    }

    public function fillForm(int $templateId)
    {
        $homeId = $this->homeId();

        $template = FormTemplate::where('id', $templateId)->where('home_id', $homeId)->where('is_deleted', 0)->first();
        if (!$template) {
            abort(404);
        }

        $clients = DB::table('service_user')
            ->where('home_id', $homeId)
            ->where('is_deleted', 0)
            ->orderBy('name')
            ->get(['id', 'name']);

        $templates = FormTemplate::forHome($homeId)->notDeleted()->orderBy('created_at', 'desc')->get();
        $templatesJson = $this->buildTemplatesJson($templates);

        return view('frontEnd.roster.form-builder.index', [
            'templates' => $templates,
            'clients' => $clients,
            'templatesJson' => $templatesJson,
            'fillTemplateId' => $templateId,
        ]);
    }

    private function buildTemplatesJson($templates)
    {
        return $templates->map(function ($t) {
            $fj = json_decode($t->form_json, true);
            $sections = $fj['sections'] ?? [];
            $fieldCount = 0;
            foreach ($sections as $s) {
                $fieldCount += count($s['fields'] ?? []);
            }
            return [
                'id' => $t->id,
                'title' => $t->title,
                'description' => $t->description,
                'ai_generated' => $t->ai_generated,
                'section_count' => count($sections),
                'field_count' => $fieldCount,
                'source_filename' => $t->source_filename,
                'created_at' => $t->created_at ? $t->created_at->format('d M Y') : '',
            ];
        });
    }

    public function aiFill(Request $request): JsonResponse
    {
        $request->validate([
            'template_id' => 'required|integer',
            'client_id' => 'required|integer',
        ]);

        $homeId = $this->homeId();
        $userId = Auth::user()->id;
        $templateId = (int) $request->input('template_id');
        $clientId = (int) $request->input('client_id');

        $client = DB::table('service_user')
            ->where('id', $clientId)
            ->where('home_id', $homeId)
            ->where('is_deleted', 0)
            ->first();
        if (!$client) {
            return response()->json(['status' => false, 'error' => 'Client not found.'], 404);
        }

        try {
            $result = $this->formService->aiFillForm($templateId, $clientId, $homeId, $userId);
        } catch (RuntimeException $e) {
            return response()->json(['status' => false, 'error' => $e->getMessage()], 422);
        }

        return response()->json([
            'status' => true,
            'values' => $result['values'],
            'filled_count' => $result['filled_count'],
            'total_fields' => $result['total_fields'],
        ]);
    }

    public function saveSubmission(Request $request): JsonResponse
    {
        $request->validate([
            'template_id' => 'required|integer',
            'client_id' => 'nullable|integer',
            'values_json' => 'required|string|max:1000000',
        ]);

        $homeId = $this->homeId();
        $user = Auth::user();

        $template = FormTemplate::where('id', $request->input('template_id'))
            ->where('home_id', $homeId)->where('is_deleted', 0)->first();
        if (!$template) {
            return response()->json(['status' => false, 'error' => 'Template not found.'], 404);
        }

        $clientId = $request->input('client_id') ? (int) $request->input('client_id') : null;
        if ($clientId) {
            $client = DB::table('service_user')
                ->where('id', $clientId)->where('home_id', $homeId)->where('is_deleted', 0)->first();
            if (!$client) {
                return response()->json(['status' => false, 'error' => 'Client not found.'], 404);
            }
        }

        $valuesJson = json_decode($request->input('values_json'), true);
        if (!is_array($valuesJson)) {
            return response()->json(['status' => false, 'error' => 'Invalid form values.'], 422);
        }

        $submission = FormSubmission::create([
            'home_id' => $homeId,
            'form_template_id' => $template->id,
            'client_id' => $clientId,
            'form_title' => $template->title,
            'values_json' => json_encode($valuesJson),
            'submitted_by' => $user->id,
            'submitted_by_name' => $user->name ?? 'Unknown',
            'ai_filled' => $request->input('ai_filled') ? 1 : 0,
        ]);

        return response()->json([
            'status' => true,
            'submission_id' => $submission->id,
        ]);
    }

    public function updateSubmission(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'values_json' => 'required|string|max:1000000',
        ]);

        $homeId = $this->homeId();

        $submission = FormSubmission::where('id', $id)->where('home_id', $homeId)->where('is_deleted', 0)->first();
        if (!$submission) {
            return response()->json(['status' => false, 'error' => 'Submission not found.'], 404);
        }

        $valuesJson = json_decode($request->input('values_json'), true);
        if (!is_array($valuesJson)) {
            return response()->json(['status' => false, 'error' => 'Invalid form values.'], 422);
        }

        $submission->update([
            'values_json' => json_encode($valuesJson),
        ]);

        return response()->json(['status' => true]);
    }

    public function deleteSubmission(Request $request, int $id): JsonResponse
    {
        $homeId = $this->homeId();

        $submission = FormSubmission::where('id', $id)->where('home_id', $homeId)->where('is_deleted', 0)->first();
        if (!$submission) {
            return response()->json(['status' => false, 'error' => 'Submission not found.'], 404);
        }

        $submission->update(['is_deleted' => 1]);

        return response()->json(['status' => true]);
    }

    public function getSubmission(int $id): JsonResponse
    {
        $homeId = $this->homeId();

        $submission = FormSubmission::where('id', $id)->where('home_id', $homeId)->where('is_deleted', 0)->first();
        if (!$submission) {
            return response()->json(['status' => false, 'error' => 'Submission not found.'], 404);
        }

        $template = FormTemplate::find($submission->form_template_id);

        return response()->json([
            'status' => true,
            'submission' => [
                'id' => $submission->id,
                'form_template_id' => $submission->form_template_id,
                'client_id' => $submission->client_id,
                'form_title' => $submission->form_title,
                'values_json' => json_decode($submission->values_json, true),
                'submitted_by_name' => $submission->submitted_by_name,
                'ai_filled' => $submission->ai_filled,
                'created_at' => $submission->created_at ? $submission->created_at->format('d M Y H:i') : '',
            ],
            'template' => $template ? [
                'id' => $template->id,
                'title' => $template->title,
                'form_json' => json_decode($template->form_json, true),
            ] : null,
        ]);
    }

    public function listSubmissions(): JsonResponse
    {
        $homeId = $this->homeId();

        $submissions = FormSubmission::where('home_id', $homeId)
            ->where('is_deleted', 0)
            ->orderBy('created_at', 'desc')
            ->get(['id', 'form_template_id', 'client_id', 'form_title', 'submitted_by_name', 'ai_filled', 'created_at']);

        return response()->json([
            'status' => true,
            'submissions' => $submissions->map(function ($s) {
                return [
                    'id' => $s->id,
                    'form_template_id' => $s->form_template_id,
                    'client_id' => $s->client_id,
                    'form_title' => $s->form_title,
                    'submitted_by_name' => $s->submitted_by_name,
                    'ai_filled' => $s->ai_filled,
                    'created_at' => $s->created_at ? $s->created_at->format('d M Y H:i') : '',
                ];
            }),
        ]);
    }

    public function clientSubmissions(int $clientId): JsonResponse
    {
        $homeId = $this->homeId();

        $client = DB::table('service_user')
            ->where('id', $clientId)->where('home_id', $homeId)->where('is_deleted', 0)->first();
        if (!$client) {
            return response()->json(['status' => false, 'error' => 'Client not found.'], 404);
        }

        $submissions = FormSubmission::where('home_id', $homeId)
            ->where('client_id', $clientId)
            ->where('is_deleted', 0)
            ->orderBy('created_at', 'desc')
            ->get(['id', 'form_template_id', 'form_title', 'submitted_by_name', 'ai_filled', 'created_at']);

        return response()->json([
            'status' => true,
            'submissions' => $submissions->map(function ($s) {
                return [
                    'id' => $s->id,
                    'form_template_id' => $s->form_template_id,
                    'form_title' => $s->form_title,
                    'submitted_by_name' => $s->submitted_by_name,
                    'ai_filled' => $s->ai_filled,
                    'created_at' => $s->created_at ? $s->created_at->format('d M Y H:i') : '',
                ];
            }),
        ]);
    }
}
