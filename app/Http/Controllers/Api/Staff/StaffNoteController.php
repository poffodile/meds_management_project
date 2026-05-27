<?php

namespace App\Http\Controllers\Api\Staff;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Staff\UserNote;
use App\User;

class StaffNoteController extends Controller
{
    public function staff_notes($staff_id)
    {
        try {
            // Validate staff existence (optional but recommended)
            if (!User::where('id', $staff_id)->exists()) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Staff not found'
                ], 404);
            }

            $notes = UserNote::where('user_id', $staff_id)
                ->orderBy('created_at', 'DESC')->where('deleted_at', null)
                ->get();

            // No data found
            if ($notes->isEmpty()) {
                return response()->json([
                    'status'  => true,
                    'message' => 'No notes found',
                    'data'    => []
                ], 200);
            }

            // Data found
            return response()->json([
                'status'  => true,
                'message' => 'Notes fetched successfully',
                'data'    => $notes,
            ], 200);
        } catch (\Exception $e) {
            // Server error
            return response()->json([
                'status'  => false,
                'message' => 'Something went wrong',
                'error'   => $e->getMessage(), // hide this in production
            ], 500);
        }
    }

    public function add_staff_note(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:user,id',
            'note'    => 'required|string',
        ]);

        // Validation failed
        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => 'Validation error',
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {
            $note = UserNote::create([
                'user_id' => $request->user_id,
                'note'    => $request->note,
            ]);

            return response()->json([
                'status'  => true,
                'message' => 'Note added successfully',
                'data'    => $note,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Something went wrong',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
    public function deleteNote(Request $request, $note_id)
    {
        try {
            $note = UserNote::find($note_id);

            // Note not found
            if (!$note) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Note not found',
                ], 404);
            }

            $note->delete();

            return response()->json([
                'status'  => true,
                'message' => 'Note deleted successfully',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Something went wrong',
                'error'   => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }
}
