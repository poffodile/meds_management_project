<?php

namespace App\Http\Controllers\Rota;


use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\Log;
use Exception;

use App\Http\Requests\Rota\StaffWorkerRequest;
// use Illuminate\Http\Request;
use App\Services\Rota\StaffWorkerService;
use Illuminate\Support\Facades\Auth;



class StaffController extends Controller
{

    protected $staffWorkerService;
    public function __construct(StaffWorkerService $staffWorkerService)
    {
        $this->staffWorkerService = $staffWorkerService;
    }
    public function index(){
        $homeId = Auth::user()->home_id;
        $data['staffWorkers'] = $this->staffWorkerService->getStaffWorkerData($homeId);

        return view('rotaStaff.staff.index', $data);
    }

    // StaffWorkerRequest
    public function store(StaffWorkerRequest $request){
      
        $validated = $request->validated();

        // dd($validated);
        try {
            $this->staffWorkerService->saveStaffWorkerData($validated, Auth::user()->home_id);
            if($validated['staff_id'] === null){
                return response()->json(['status' => 'success', 'message' => 'Form submitted successfully!']);
            }else{
                return response()->json(['status' => 'success', 'message' => 'Form updated successfully!']);
            }
        } catch (Exception $e) {
            Log::error('Form submission failed: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Something went wrong while saving. Please try again.']);
        }

    }

    public function destroy($id){
        try {
            $this->staffWorkerService->deleteStaffWorkerData($id);
            return response()->json(['status' => 'success', 'message' => 'Staff deleted successfully!']);
        } catch (Exception $e) {
            Log::error('Staff deletion failed: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Something went wrong while deleting. Please try again.']);
        }
    }
}
