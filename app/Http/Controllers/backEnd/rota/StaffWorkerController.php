<?php

namespace App\Http\Controllers\backEnd\rota;

use App\Http\Controllers\Controller;
// use Illuminate\Http\Request;
use App\Http\Requests\Rota\StaffWorkerRequest;
use App\Services\Rota\StaffWorkerService;
use Illuminate\Support\Facades\Log;
use Exception;
use Illuminate\Support\Facades\Session;

class StaffWorkerController extends Controller
{
    protected $staffWorkerService;
    public function __construct(StaffWorkerService $staffWorkerService)
    {
        $this->staffWorkerService = $staffWorkerService;
    }

    public function index()
    {
        $data['page'] = "staff_worker";
        
        $homeId = Session::get('scitsAdminSession')->home_id;
        $data['staffWorkers'] = $this->staffWorkerService->getStaffWorkerData($homeId);
        return view('backEnd/rota/staff_worker', $data);
    }

    public function create()
    {
        $data['page'] = "staff_worker";
        return view('backEnd/rota/staff_worker_form', $data);
    }

    public function store(StaffWorkerRequest $request)
    {

        $validated = $request->validated();

        // print_r($validated);
        // dd($validated);

        try {
            $home_id = Session::get('scitsAdminSession')->home_id;
            $data = $this->staffWorkerService->saveStaffWorkerData($validated, $home_id);
            if (empty($validated['staff_id'])) {
                if ($data) {
                    return redirect()->route('backend.staff_worker')->with('success', 'Staff workers added successfully!');
                } else {
                    return redirect()->route('backend.staff_worker.add')->with('error', 'Failed to save Staff Worker');
                }
            } else {
                if ($data) {
                    return redirect()->route('backend.staff_worker')->with('success', 'Staff Worker edited successfully!');
                } else {
                    return redirect()->route('backend.staff_worker.add')->with('error', 'Failed to edit Staff Worker');
                }
            }
        } catch (Exception $e) {
            Log::error('Form submission failed: ' . $e->getMessage());
            return redirect()->route('backend.staff_worker.add')->with('error', 'Something went wrong while saving. Please try again.');
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

    public function edit($id){
        $data['page'] = "staff_worker";
        $data['staffWorker'] = $this->staffWorkerService->getData($id);
        // dd($data['staffWorker']);
        return view('backEnd/rota/staff_worker_form', $data);        
    }
}
