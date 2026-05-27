<?php

namespace App\Http\Controllers\backEnd\systemManage;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ClientCarePlan;
use Illuminate\Support\Facades\Validator;
use Session, DB;

class ClientCarePlanController extends Controller
{
    public function index(Request $request)
    {
        $home_id = Session::get('scitsAdminSession')->home_id;
        
        $query = ClientCarePlan::where('home_id', $home_id);

        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where('name', 'like', '%' . $search . '%');
        }

        $limit = $request->get('limit', 10);
        $care_plans = $query->orderBy('id', 'desc')->paginate($limit);

        $page = 'client_care_plan';
        return view('backEnd.systemManage.client_care_plan.index', compact('care_plans', 'page', 'limit'));
    }

    public function save(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|max:255',
            'status' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()->first()]);
        }

        try {
            $home_id = Session::get('scitsAdminSession')->home_id;
            
            if ($request->id) {
                $care_plan = ClientCarePlan::find($request->id);
                $message = "Client Care Plan updated successfully";
            } else {
                $care_plan = new ClientCarePlan();
                $care_plan->home_id = $home_id;
                $message = "Client Care Plan added successfully";
            }

            $care_plan->name = $request->name;
            $care_plan->status = $request->status;
            $care_plan->save();

            return response()->json(['success' => true, 'message' => $message]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'errors' => $e->getMessage()]);
        }
    }

    public function delete($id)
    {
        try {
            $care_plan = ClientCarePlan::find($id);
            if ($care_plan) {
                $care_plan->delete();
                return redirect()->back()->with('success', 'Client Care Plan deleted successfully');
            }
            return redirect()->back()->with('error', 'Client Care Plan not found');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function status_change(Request $request)
    {
        try {
            $care_plan = ClientCarePlan::find($request->id);
            if ($care_plan) {
                $care_plan->status = $request->status;
                $care_plan->save();
                return response()->json(['success' => true, 'message' => 'Status changed successfully']);
            }
            return response()->json(['success' => false, 'message' => 'Client Care Plan not found']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
