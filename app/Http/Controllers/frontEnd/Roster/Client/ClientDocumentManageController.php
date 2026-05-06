<?php

namespace App\Http\Controllers\frontEnd\Roster\Client;

use App\Http\Controllers\Controller;
use App\Services\Client\DocumentManageService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ClientDocumentManageController extends Controller
{
    protected DocumentManageService $documentManageService;

    public function __construct(DocumentManageService $documentManageService)
    {
        $this->documentManageService = $documentManageService;
    }
    public function index(Request $req)
    {
        $homeIds = explode(',', auth()->user()->home_id);
        $homeId  = $homeIds[0] ?? null;
        $reqData = $req->all();
        $reqData['home_id'] = $homeId;
        $type = $req->type;
        $subQuery = $this->documentManageService->list($reqData)->with(['accesslevel:id,name', 'carer:id,name']);

        $data =  $subQuery->latest()->paginate(15)->through(function ($item) {

            $created_at = Carbon::parse($item->created_at)->format('M d, Y');
            $expiry_date = $item->expiry_date ? Carbon::parse($item->expiry_date)->format('M d, Y') : "";
            $tagsArr = [];
            if ($item->tags) {
                $subjectsArray = explode(',', $item->tags);
                foreach ($subjectsArray as $subject) {
                    if (trim($subject) !== '') {
                        $tagsArr[] = trim($subject);
                    }
                }
            }
            return [
                'id' => $item->id,
                'doc_name' => ucfirst($item->doc_name),
                'document_type' => $item->document_type,
                'is_confidential' => $item->is_confidential,
                'access_level_id' => $item->access_level_id,
                'expiry_date' => $expiry_date,
                'is_expired' => $item->expiry_date && Carbon::parse($item->expiry_date)->isPast(),
                'file' => url('public/uploads/client/documents/' . $item->file),
                'file_size' => ($item->file_size ?? 0) . " KB",
                'created_at' => $created_at,
                'notes' => $item->note,
                'tags' => $tagsArr,
                'accesslevel' => $item->accesslevel->name ?? "",
                'carer_name' => $item->carer->name ?? "",
            ];
            return $item;
        });
        $pagination =    [
            'total' => $data->total(),
            'per_page' => $data->perPage(),
            'current_page' => $data->currentPage(),
            'total_pages' => $data->lastPage(),
        ];
        $nexPage = $data->nextPageUrl();
        $arrr = $data->items();

        return response()->json([
            'status' => true,
            'data' => $arrr,
            'next_page' => $nexPage ?? null,
            'pagination' => $pagination ?? null,
        ]);
    }
    public function create(Request $req)
    {
        try {
            // return $req;
            $validator = Validator::make($req->all(), [
                'client_id' => 'required|exists:service_user,id',
                'doc_manage_id' => 'nullable',
                'document_type' => 'required|max:255',
                'doc_name' => 'required|max:255',
                'doc_files' => 'required|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120',
                'doc_expiry_date' => 'nullable|date',
                'doc_access_level_id' => 'required',
                'doc_tags' => 'nullable|max:255',
                'is_confidential' => 'nullable',
                'doc_notes' => 'nullable',
            ],  [

                'client_id.required' => 'Client is required.',
                'client_id.exists' => 'Selected client does not exist.',

                'document_type.required' => 'Document type is required.',
                'document_type.max' => 'Document type must not exceed 255 characters.',

                'doc_name.required' => 'Document name is required.',
                'doc_name.max' => 'Document name must not exceed 255 characters.',

                'doc_files.required' => 'Please upload a document file.',
                'doc_files.file' => 'Uploaded file must be a valid file.',
                'doc_files.mimes' => 'Only PDF, DOC, DOCX, JPG, and PNG files are allowed.',
                'doc_files.max' => 'File size must not exceed 5MB.',

                'doc_expiry_date.required' => 'Expiry date is required.',
                'doc_expiry_date.date' => 'Please enter a valid expiry date.',

                'doc_access_level_id.required' => 'Access level is required.',

                'doc_tags.required' => 'Please enter at least one tag.',
                'doc_tags.max' => 'Tags must not exceed 255 characters.',

            ]);

            if ($validator->fails()) {
                return response()->json(['status' => false, 'message' => $validator->errors()], 422);
            }


            $homeIds = explode(',', auth()->user()->home_id);
            $homeId  = $homeIds[0] ?? null;
            $reqData = $req->all();
            $reqData['home_id'] = $homeId;
            $reqData['user_id'] = auth()->id();
            // return $reqData;
            $data = $this->documentManageService->store($reqData);
            if (!$data) {
                return response()->json(['status' => false, 'message' => 'Contact not saved'], 422);
            }
            return response()->json(['status' => true, 'message' => 'Document added successfully']);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function delete(Request $req)
    {
        try {
            $data = $this->documentManageService->delete($req->id);
            if (!$data) {
                return response()->json([
                    'status' => false,
                    'message' => 'Document not deleted'
                ], 422);
            }
            return response()->json([
                'status' => true,
                'message' => 'Document deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
