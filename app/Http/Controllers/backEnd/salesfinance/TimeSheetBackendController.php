<?php

namespace App\Http\Controllers\backEnd\salesfinance;

use App\Http\Requests\PersonalManagement\TimeSheetRequest;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Session;
use Illuminate\Http\Request;
use App\User;
use App\Models\PersonalManagement\TimeSheet;
use Illuminate\Support\Facades\Log;
use Exception;

class TimeSheetBackendController extends Controller
{


    public function index()
    {
        $data['page'] = "time_sheet";
        $admin   = Session::get('scitsAdminSession');
        $home_id = $admin->home_id;
        $data['users'] = User::getHomeUsers($home_id);
        return view('backEnd.salesFinance.time_sheet.time_sheet', $data);
    }

    public function create()
    {
        $data['page'] = "time_sheet";
        $admin   = Session::get('scitsAdminSession');
        $home_id = $admin->home_id;
        $data['users'] = User::getHomeUsers($home_id);
        return view('backEnd.salesFinance.time_sheet.time_sheet_form', $data);
    }
    public function store(TimeSheetRequest $request)
    {
        try {
            $validated = $request->validated();

            $admin = Session::get('scitsAdminSession');
            $validated['home_id'] = $admin->home_id;
            $validated['date'] = \Carbon\Carbon::parse($validated['date'])->format('Y-m-d');

            TimeSheet::updateOrCreate(['id' => $validated['time_sheet_id']], $validated);

            if (empty($validated['time_sheet_id'])) {
                return redirect()->route('backEnd.salesFinance.time_sheet') // or the route name for your list page
                    ->with('success', 'Time sheet saved successfully.');
            } else {
                return redirect()->route('backEnd.salesFinance.time_sheet')
                    ->with('success', 'Time sheet updated successfully.');
            }
        } catch (\Exception $e) {
            Log::error('TimeSheet Store Error: ' . $e->getMessage());

            return redirect()->back()
                ->withInput() // repopulate form
                ->withErrors(['error' => 'Failed to save time sheet: ' . $e->getMessage()]);
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

    public function edit($id){
        $data['page'] = "time_sheet";
        $admin   = Session::get('scitsAdminSession');
        $home_id = $admin->home_id;
        $data['users'] = User::getHomeUsers($home_id);
        $data['timeSheet'] = TimeSheet::findOrFail($id);
        return view('backEnd.salesFinance.time_sheet.time_sheet_form', $data);

    }

}
