<?php

namespace App\Http\Controllers\frontEnd\Roster\Client;

use App\Http\Controllers\Controller;
use App\Services\Client\ProgressReportService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ClientProgressReportController extends Controller
{
    protected ProgressReportService $progressReportService;

    public function __construct(ProgressReportService $progressReportService)
    {
        $this->progressReportService = $progressReportService;
    }
    public function index(Request $req)
    {
        $homeIds = explode(',', auth()->user()->home_id);
        $homeId  = $homeIds[0] ?? null;
        $reqData = $req->all();
        $reqData['home_id'] = $homeId;
        $type = $req->type;
        $subQuery = $this->progressReportService->list($reqData);

        $data =  $subQuery->latest()->get()->map(function ($item) {
            $report_date = Carbon::parse($item->report_date)->format('M d, Y');
            return [
                'id' => $item->id,
                'carer_name' => $item->carer->name ?? "",
                'report_type' => $item->report_type,
                'report_date' => $report_date,
                'overall_rating' => $item->overall_rating,
                'overall_progress' => $item->overall_progress,

            ];
        });

        return response()->json([
            'status' => true,
            'data' => $data,
        ]);
    }

    public function stats(Request $req)
    {
        $homeIds = explode(',', auth()->user()->home_id);
        $homeId  = $homeIds[0] ?? null;
        $reqData = $req->all();
        $reqData['home_id'] = $homeId;
        $type = $req->type;
        $data =  $this->progressReportService->stats($reqData);
        if (!$data) {
            return response()->json([
                'status' => false,
                'message' => 'No progress report found'
            ], 404);
        }
        return response()->json([
            'status' => true,
            'data' => $data,
        ]);
    }
    public function create(Request $req)
    {
        try {
            //  return $req;
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

            // if ($validator->fails()) {
            //     return response()->json(['status' => false, 'message' => $validator->errors()], 422);
            // }


            $homeIds = explode(',', auth()->user()->home_id);
            $homeId  = $homeIds[0] ?? null;
            $reqData = $req->all();
            $reqData['home_id'] = $homeId;
            $reqData['user_id'] = auth()->id();
            // return $reqData;
            $data = $this->progressReportService->store($reqData);
            if (!$data) {
                return response()->json(['status' => false, 'message' => 'Progress Report not saved'], 422);
            }
            return response()->json(['status' => true, 'message' => 'Progress Report created successfully']);
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
            $data = $this->progressReportService->delete($req->id);
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
