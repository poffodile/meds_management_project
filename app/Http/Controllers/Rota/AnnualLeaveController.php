<?php

namespace App\Http\Controllers\Rota;

use App\Http\Controllers\Controller;
use App\Models\CompanyDepartment;
use Illuminate\Http\Request;
use App\User;
use Illuminate\Support\Facades\Auth;

class AnnualLeaveController extends Controller
{
    public function index()
    {

        $data['users'] = User::getHomeUsers(Auth::user()->home_id);
        $data['departments'] = CompanyDepartment::getActiveCompanyDepartment();
        // dd($data);
        return view('rotaStaff.annualLeave.annual_leave_tracker', $data);
    }

    // public function getUserData(Request $request)
    // {
    //     $request->validate([
    //         'id' => 'required|exists:user,id',
    //     ]);

    //     $data = User::getData($request->id);
    //     // dd($data);
    //     return response()->json(['data' => $data]);
    // }

    public function getUserData(){
        $currentYear = date('Y');

        $users = User::where('is_deleted', 0)->where('status', 1)->get();

        foreach($users as $user){
            
        }
    }
}
