<?php

namespace App\Http\Controllers\frontEnd\PersonalManagement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PersonalManagement\TimeSheet;
use App\Http\Requests\PersonalManagement\TimeSheetRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\User;
use Exception;

class TimesheetController extends Controller
{

    public function index($manager_id)
    {
        $data['manager_id'] = $manager_id;

        $data['users'] = User::getHomeUsers(Auth::user()->home_id);
        // $data['time_sheets'] = TimeSheet::with('user')->where('deleted_at', null)->orderBy('created_at', 'desc')->get();
        return view('frontEnd.personalManagement.elements.time_sheet.index', $data);
    }
    public function save(TimeSheetRequest $request)
    {
        try {
            $validated = $request->validated();
            // dd($validated);
            $validated['home_id'] = Auth::user()->home_id;
            $validated['date'] = \Carbon\Carbon::parse($validated['date'])->format('Y-m-d');
            TimeSheet::updateOrCreate(['id' => $validated['time_sheet_id']], $validated);

            if (empty($validated['time_sheet_id'])) {
                return response()->json([
                    'message' => 'Time sheet saved successfully.',
                    'data' => $validated
                ], 201);
            } else {
                return response()->json([
                    'message' => 'Time sheet updated successfully.',
                    'data' => $validated
                ], 200);
            }
            // return response()->json([
            //     'message' => 'Time sheet saved successfully.',
            //     'data' => $validated
            // ], 201);
        } catch (\Exception $e) {
            // Optional: Log the error for debugging
            Log::error('TimeSheet Store Error: ' . $e->getMessage());

            return response()->json([
                'message' => 'Failed to save time sheet.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            TimeSheet::where('id', $id)->update(['deleted_at' => now()]);
            return response()->json(['status' => 'success', 'message' => 'Record deleted successfully!']);
        } catch (Exception $e) {
            Log::error('Record deletion failed: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Something went wrong while deleting. Please try again.']);
        }
    }

    public function getData(Request $request)
    {
        $query = TimeSheet::with('user')->where('deleted_at', null)->orderBy('created_at', 'desc');

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        return response()->json($query->get());
    }
}
