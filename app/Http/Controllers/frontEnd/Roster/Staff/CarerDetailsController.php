<?php

namespace App\Http\Controllers\frontEnd\Roster\Staff;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Staff\StaffService;
use App\Models\Staff\Documents;
use App\Models\Staff\UserNote;
use Illuminate\Support\Facades\Storage;

class CarerDetailsController extends Controller
{

    protected StaffService $staffService;

    public function __construct(StaffService $staffService)
    {
        $this->staffService = $staffService;
    }

    public function carer_details($carer_id)
    {
        if (!$carer_id) {
            abort(400, 'User ID is required.');
        }
        // dd($carer_id);
        $data['staffDetails'] = $this->staffService->getStaffDetails($carer_id);
        $data['selectedCourseIds'] = $data['staffDetails']->qualifications
                            ->pluck('course_id')
                            ->toArray();
        // dd($data['staffDetails']);
        $data['user_documents'] = Documents::where('user_id', $carer_id)->get()->toArray();
        $data['courses'] = $this->staffService->courses();
        $data['user_notes'] = UserNote::where('user_id', $carer_id)->orderBy('created_at', 'DESC')->get()->toArray();

        // dd($data['user_notes']);
        return view('frontEnd.roster.staff.carer_details', $data);
    }

      public function saveDouments(Request $request)
    {
        $request->validate([
            'user_id'        => 'required',
            'document_title' => 'required|string',
            'document_file'  => 'required|file|max:2048',
        ]);

        $filePath = $request->file('document_file')->store('staff-documents', 'public');

        Documents::create([
            'user_id'        => $request->user_id,
            'title'          => $request->document_title,
            'file_path'      => $filePath,
        ]);

        return response()->json(['success' => true]);
    }

      public function destroy_documents($id)
    {
        $document = Documents::findOrFail($id);

        // Delete physical file if exists
        if ($document->file_path && Storage::disk('public')->exists($document->file_path)) {
            Storage::disk('public')->delete($document->file_path);
        }

        // Soft delete DB record
        $document->delete();

        return response()->json([
            'success' => true,
            'message' => 'Document deleted successfully'
        ]);
    }

    // Notes Section Start
    public function saveNote(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:user,id',
            'note'    => 'required|string',
        ]);

        UserNote::create([
            'user_id' => $request->user_id,
            'note'    => $request->note,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Note added successfully'
        ]);
    }

    public function destroy_notes($id)
    {
        $note = UserNote::findOrFail($id);

        $note->delete(); // Soft delete

        return response()->json([
            'success' => true,
            'message' => 'Note deleted successfully'
        ]);
    }
    // Notes Section End
}
